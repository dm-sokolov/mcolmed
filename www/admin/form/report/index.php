<?php

/**
* Online shop.
*
* @package HostCMS
* @version 6.x
* @author Hostmake LLC
* @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
*/
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'form');

$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Контроллер формы
$oAdmin_Form_Controller->module(Core_Module::factory($sModule))->setUp()->path('/admin/form/report/index.php');
$oSite = Core_Entity::factory('Site', CURRENT_SITE);
ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	;

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs
	->add(Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Form.title'))
	->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/form/index.php'))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/form/index.php')))
;

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
	->name("Отчеты")
	->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, ""))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "")));

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($oAdmin_Form_Controller->getPath())
	->target('_blank');
//$oAdmin_Form_Entity_Form->add($oAdmin_Form_Entity_Breadcrumbs);
$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

// Обработка данных формы
if(!is_null(Core_Array::getPost('do_show_report')))
{
	$sDateFrom = Core_Date::datetime2sql(Core_Array::getPost('sales_order_begin_date') . ' 00:00:00');
	$sDateTo = Core_Date::datetime2sql(Core_Array::getPost('sales_order_end_date') . ' 23:59:59');

	$oQueryBuilderSelect = Core_QueryBuilder::select(array(Core_QueryBuilder::expression('COUNT(id)'), 'count_orders'));

	switch(Core_Array::getPost('sales_order_grouping'))
	{
		case 1: // группировка по неделям
			$sFormatDateTitle = '%u';
			$sFormatDateSelect = ", year_title";
			$sFormatDateSelectForm = ", DATE_FORMAT(form_fills.datetime, '%Y') AS year_title";
			$sFormatDateSelectShop = ", DATE_FORMAT(shop_orders.datetime, '%Y') AS year_title";
			break;
		case 2: // группировка по дням
			$sFormatDateTitle = '%d.%m.%Y';
			$sFormatDateSelect = $sFormatDateSelectForm = $sFormatDateSelectShop = "";
			break;
		default: // группировка по месяцам
			$sFormatDateTitle = '%m %Y';
			$sFormatDateSelect = $sFormatDateSelectForm = $sFormatDateSelectShop = "";
		break;
	}

	$oQueryBuilderSelectIForm = $oQueryBuilderSelectIShop = "";

	if(!is_null($iForm = Core_Array::getPost('form_id')) && $iForm > 0)
	{
		$oQueryBuilderSelectIForm = " AND forms.id = {$iForm}";
		$oQueryBuilderSelectIShop = " AND shops.id = 0";
	}

	if(!is_null($iShop = Core_Array::getPost('shop_id')) && $iShop > 0)
	{
		$oQueryBuilderSelectIShop = " AND shops.id = {$iShop}";
		$oQueryBuilderSelectIForm = " AND forms.id = 0";
	}

	$selectForm = "SELECT @tableName := 'Form_Fill' AS tableName, @price := 0 AS total_sum, form_fills.id, form_fills.datetime, DATE_FORMAT(form_fills.datetime, '{$sFormatDateTitle}') AS date_title, DATE_FORMAT(form_fills.datetime, '%Y') AS year, DATE_FORMAT(form_fills.datetime, '%Y%m%d') AS order_title{$sFormatDateSelectForm} FROM form_fills LEFT JOIN forms ON form_fills.form_id = forms.id AND forms.deleted = 0 WHERE form_fills.deleted = 0 AND form_fills.datetime >= '{$sDateFrom}' AND form_fills.datetime <= '{$sDateTo}' AND forms.site_id = {$oSite->id}{$oQueryBuilderSelectIForm}";

	$selectShop = "SELECT @tableName := 'Shop_Order' AS tableName, @sum := (SELECT SUM(shop_order_items.price * quantity) AS shop_order_items_price FROM shop_order_items WHERE shop_order_items.shop_order_id = shop_orders.id AND shop_order_items.deleted = 0) AS total_sum, shop_orders.id, shop_orders.datetime, DATE_FORMAT(shop_orders.datetime, '{$sFormatDateTitle}') AS date_title, DATE_FORMAT(shop_orders.datetime, '%Y') AS year, DATE_FORMAT(shop_orders.datetime, '%Y%m%d') AS order_title{$sFormatDateSelectShop} FROM shop_orders LEFT JOIN shops ON shop_orders.shop_id = shops.id AND shops.deleted = 0 WHERE shop_orders.deleted = 0 AND shop_orders.canceled = 0 AND shop_orders.datetime >= '{$sDateFrom}' AND shop_orders.datetime <= '{$sDateTo}' AND shops.site_id = {$oSite->id}{$oQueryBuilderSelectIShop}";

	$oQueryBuilderSelect
		->select(Core_QueryBuilder::expression("date_title, year, order_title, SUM(total_sum) AS total_sum{$sFormatDateSelect}"))
		->from(array(Core_QueryBuilder::expression("({$selectForm} UNION ALL {$selectShop})"), 'utb'))
		->groupBy('date_title')
		->groupBy('year')
		->orderBy('order_title')
	;

	$aOrdersResult = $oQueryBuilderSelect->execute()->asAssoc()->result();

	// Создаем второй запрос, отличается от первого выборкой полей в SELECT'е, и дополнительным GROUP BY
	$oQueryBuilderSelect
		->clearSelect()
		->select(Core_QueryBuilder::expression("id, datetime, date_title, year, tableName"))
		->clearOrderBy()
		->groupBy('id')
		->orderBy('datetime');

	$aOrdersResultPeriod = $oQueryBuilderSelect->execute()->asAssoc()->result();

	$aOrdersResultPeriodParsed = array();

	foreach($aOrdersResultPeriod as $aTmpArray)
	{
		$aOrdersResultPeriodParsed[
			$aTmpArray['date_title'].' ' . $aTmpArray['year']
		][] = array('id' => $aTmpArray['id'], 'model' => $aTmpArray['tableName']);
	}

	$sDateFrom = Core_Date::sql2date($sDateFrom);
	$sDateTo = Core_Date::sql2date($sDateTo);

	// Заголовок
	$oAdmin_View
		->pageTitle(sprintf("Отчет о заявках сайта за период %s - %s", $sDateFrom, $sDateTo, ""));

	if(count($aOrdersResult) > 0)
	{
		?>
		<div class="shop counter">
			<div class="shop-body">
				<div class="row">
					<div class="col-xs-12">
						<div id="shopOrdersReportDiagram" class="chart chart-lg"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="shop counter">
			<div class="shop-body">
				<table class="admin-table table table-bordered table-hover table-striped">
					<thead>
						<tr>
							<th></th>
							<th width="200">Дата/Время</th>
							<th width="140">IP</th>
							<th width="60">Кол-во <br />заявок</th>
							<th width="120">Сумма <br />заявок</th>
						</tr>
					</thead>
		<?php

		$aMonths = array(
			'01' => Core::_('Shop_Item.form_sales_order_month_january'),
			'02' => Core::_('Shop_Item.form_sales_order_month_february'),
			'03' => Core::_('Shop_Item.form_sales_order_month_march'),
			'04' => Core::_('Shop_Item.form_sales_order_month_april'),
			'05' => Core::_('Shop_Item.form_sales_order_month_may'),
			'06' => Core::_('Shop_Item.form_sales_order_month_june'),
			'07' => Core::_('Shop_Item.form_sales_order_month_july'),
			'08' => Core::_('Shop_Item.form_sales_order_month_august'),
			'09' => Core::_('Shop_Item.form_sales_order_month_september'),
			'10' => Core::_('Shop_Item.form_sales_order_month_october'),
			'11' => Core::_('Shop_Item.form_sales_order_month_november'),
			'12' => Core::_('Shop_Item.form_sales_order_month_december')
		);

		$iShopOrderItemsSum = 0;

		// Максимальное количесво подписей в легенде
		$iTotalNames = 4;
		$iSkipItems = intval(count($aOrdersResult) / $iTotalNames);
		$iSkipItems < 1 && $iSkipItems = 1;

		$i = 0;
		?>
		<script type="text/javascript">
			var shopOrdersReportDiagramData = [], sumOrdersValues = [], countOrdersValues = [], timePeriodTitles = [];
		</script>
		<?php

		foreach ($aOrdersResult as $key => $rowOrdersResult)
		{
			switch (Core_Array::getPost('sales_order_grouping'))
			{
				case 0: // группировка по месяцам
					// Разделяем месяц и год
					$mas_date = explode(' ',$rowOrdersResult['date_title']);

					$period_title = Core_Array::get($aMonths, $mas_date[0]) . ' ' . $mas_date[1];
				break;
				case 1: // группировка по неделям
					$DayLen = 24 * 60 * 60;

					$WeekLen = 7 * $DayLen;

					$year = $rowOrdersResult['year_title'];//1993;
					$week = $rowOrdersResult['date_title'];//1;

					$StJ = gmmktime(0,0,0,1,1,$year); // 1 января, 00:00:00

					// Определим начало недели, к которой относится 1 января
					$DayStJ = gmdate("w",$StJ);
					$DayStJ = ($DayStJ == 0 ? 7 : $DayStJ);
					$StWeekJ = $StJ - ($DayStJ-1) * $DayLen;

					// Если 1 января относится к 1й неделе, то в $week получается одна "лишняя" неделя
					if( gmdate("W",$StJ) == "01" )$week--;

					// прибавили к началу "январской" недели номер нашей недели
					$start = $StWeekJ + $week * $WeekLen;

					// К началу прибавляем недели (получаем след. понедельник, 00:00) и отняли одну секунду - т.е. воскресенье, 23:59
					$end = $start + $WeekLen - 5*60*60;

					$period_title = $rowOrdersResult['date_title'] . Core::_('Shop_Item.form_sales_order_week') . date('d.m.Y', $start) . '&mdash;' . date('d.m.Y', $end);
				break;
				default: // группировка по дням
					$period_title = $rowOrdersResult['date_title'];
				break;
			}

			?>
			<tbody>
			<tr>
				<td colspan="3"><strong><?php echo $period_title?></strong></td>
				<td><?php echo $rowOrdersResult['count_orders'] ?></td>
				<td><?php echo number_format($rowOrdersResult['total_sum'], 2, '.', ' ')?> руб.</td>
			</tr>

			<script type="text/javascript">
					sumOrdersValues.push([<?php echo $i . ', ' . sprintf("%.2f", $rowOrdersResult['total_sum'])?>]);
					countOrdersValues.push([<?php echo $i . ', ' .  $rowOrdersResult['count_orders']?>]);

					timePeriodTitles[<?php echo $i?>] = <?php echo '\'' . str_replace("&mdash;", "-", $period_title) . '\''?>
			</script>
			<?php
			++$i;

			if(count($aOrdersResultPeriodParsed[$rowOrdersResult['date_title'] . ' ' . $rowOrdersResult['year']]) > 0)
			{
				foreach ($aOrdersResultPeriodParsed[$rowOrdersResult['date_title'] . ' ' . $rowOrdersResult['year']] as $model)
				{
					$oModel = Core_Entity::factory($model['model'], $model['id']);

					$geoip = "";

					/*if($oModel->ip && $oModel->ip != '127.0.0.1')
        			{
        				$oGeoData = Core_Geoip::instance()->getGeoData($oModel->ip);

        				if(!is_null($oGeoData))
            			{
            				$geoip = sprintf('<br /><small><small>%s, %s, %s</small></small>', htmlspecialchars(Core_Entity::factory('Shop_Country', $oGeoData->countryId)->name), htmlspecialchars(Core_Entity::factory('Shop_Country_Location', $oGeoData->locationId)->name), htmlspecialchars(Core_Entity::factory('Shop_Country_Location_City', $oGeoData->cityId)->name));
            			}
        			}*/

					if($model['model'] == 'Form_Fill')
					{
						?>
						<tr class="row_table report_height" style="font-size: 120%">
							<td><?php echo sprintf('Форма №%s - <b>"%s"</b> - Заявка №<b>%s</b>', htmlspecialchars($oModel->Form->id), htmlspecialchars($oModel->Form->name), htmlspecialchars($oModel->id))?></td>
							<td><?php echo Core_Date::sql2datetime($oModel->datetime)?></td>
							<td><?php echo htmlspecialchars($oModel->ip)?><?php echo $geoip ?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<?php

						$aForm_Fill_Fields = $oModel->Form_Fill_Fields->findAll();

						foreach ($aForm_Fill_Fields as $oForm_Fill_Field)
						{
							switch ($oForm_Fill_Field->Form_Field->name)
							{
								case 'fio':
								case 'name':
								case 'phone':
								case 'email':
								case 'txt':
								case 'comment':
								case 'message':
								case 'question':
									if($oForm_Fill_Field->value)
									{
										?>
										<tr class="row_table report_height">
											<td colspan="3">— <?php echo htmlspecialchars($oForm_Fill_Field->value)?></td>
											<td></td>
											<td></td>
										</tr>
										<?php
									}
									break;
							}
						}
					}
					elseif($model['model'] == 'Shop_Order')
					{
						$iShopOrderItemsSum += $oModel->getAmount();
						?>
						<tr class="row_table report_height" style="font-size: 120%">
							<td><?php echo sprintf('Заказ <b>№"%s"</b> - в магазине №%s', htmlspecialchars($model['id']), htmlspecialchars($oModel->shop_id))?></td>
							<td><?php echo Core_Date::sql2datetime($oModel->datetime)?></td>
							<td><?php echo htmlspecialchars($oModel->ip)?><?php echo $geoip ?></td>
							<td>&nbsp;</td>
							<td><?php echo htmlspecialchars(number_format($oModel->getAmount(), 2, '.', ' '))?> руб.</td>
						</tr>
						<?php

						$aForm_Fill_Fields = array('phone', 'name', 'surname', 'email', 'description');

						foreach ($aForm_Fill_Fields as $field)
						{
							if($oModel->$field)
							{
								?>
								<tr class="row_table report_height">
									<td colspan="3">— <?php echo htmlspecialchars($oModel->$field)?></td>
									<td></td>
									<td></td>
								</tr>
								<?php
							}
						}
					}

					if ($oModel->source_id)
					{
						$oSource = $oModel->Source;
						?>
						<tr class="row_table report_height">
							<td colspan="3">— <span class="badge badge-ico badge-palegreen white"><i class="fa fa-tag"></i></span> <?php echo sprintf('Рекламный сервис: %s; Компания: %s; Ключевые слова: %s', $oSource->service, $oSource->campaign, $oSource->term)?></td>
							<td></td>
							<td></td>
						</tr>
						<?php
					}
				}
			}
		}
		?>
		<tr class="admin_table_filter row_table admin_table_sub_title">
			<td colspan="3"></td>
			<td>∑</td>
			<td><?php echo htmlspecialchars(number_format($iShopOrderItemsSum, 2, '.', ' '))?> руб.</td>
		</tr>
					</tbody>
				</table>
			</div>
		</div>
		<script type="text/javascript">
			$(function(){
				//var shopOrdersReportDiagramData = [];
				var gridbordercolor = "#eee",
				themeprimary = getThemeColorFromCss('themeprimary'),
				themesecondary = getThemeColorFromCss('themesecondary'),
				themethirdcolor = getThemeColorFromCss('themethirdcolor'),

				shopOrdersReportDiagramData = [
					{
						color: themeprimary, //'#30C4E8',
						label: 'Сумма заявок',
						data: sumOrdersValues,
						lines: {
							show: true,
							fill: false,
							fillColor: {
								colors: [{
									opacity: 0.3
								}, {
									opacity: 0
								}]
							}
						},
						points: {
							show: true
						},
						yaxis: 2
					},
					{
						color: themethirdcolor,
						label: 'Кол-во заявок',
						data: countOrdersValues,
						lines: {
							show: true,
							fill: false,
							fillColor: {
								colors: [{
									opacity: 0.3
								}, {
									opacity: 0
								}]
							}
						},
						points: {
							show: true
						},
						yaxis: 2
					},
				];

				var options = {
					legend: {
						show: false
					},
					xaxes: [ {
						show: false,
						tickDecimals: 0,
						color: gridbordercolor,
					} ],
					yaxes: [ {
								min: 0,
								color: gridbordercolor,
								tickDecimals: 0
							},
							{
								min: 0,
								color: gridbordercolor,
								tickDecimals: 0,
								position: "right"

					} ],
					grid: {
						hoverable: true,
						clickable: false,
						borderWidth: 0,
						aboveData: false,
						color: '#fbfbfb'
					},
					tooltip: true,
					tooltipOpts: {
						defaultTheme: false,
						//content: "<span>%lx</span>, <b>%s</b> : <span>%y</span>",
						content: function(label, xval, yval, flotItem){return '<span>' + timePeriodTitles[xval] + '</span>, <b>' + label + '</b> : <span>' + yval + '</span>'; }
					}
				};
				var placeholder = $("#shopOrdersReportDiagram");
				var plot = $.plot(placeholder, shopOrdersReportDiagramData, options);
			});
		</script>
		<?php
	}
	else
	{
		?><p><?php echo "За указанный период заявки отсутствуют." ?></p><?php
	}
}
else
{
	$oAdmin_View->pageTitle("Отчет по заявкам");

	$windowId = $oAdmin_Form_Controller->getWindowId();

	$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');
	$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Radiogroup')
			->radio(array(Core::_('Shop_Item.form_sales_order_grouping_monthly'),
				Core::_('Shop_Item.form_sales_order_grouping_weekly'),
				Core::_('Shop_Item.form_sales_order_grouping_daily')
			))
			->ico(array(
				'fa-calendar',
				'fa-calendar',
				'fa-calendar'
			))
			->caption(Core::_('Shop_Item.form_sales_order_select_grouping'))
			->name('sales_order_grouping')
			->divAttr(array('id' => 'sales_order_grouping', 'class' => 'form-group col-lg-12 col-md-12 col-sm-12'))
	))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Date')
			->caption(Core::_('Shop_Item.form_sales_order_begin_date'))
			->name('sales_order_begin_date')
			->value(date('01.m.Y'))
			->divAttr(array('class' => 'form-group col-lg-3 col-md-3 col-sm-3'))
	)->add(
		Admin_Form_Entity::factory('Date')
			->caption(Core::_('Shop_Item.form_sales_order_end_date'))
			->name('sales_order_end_date')
			->value(date('t.m.Y'))
			->divAttr(array('class' => 'form-group col-lg-3 col-md-3 col-sm-3'))
	));

	$aModels = array(' … ');
	$aForms = $oSite->Forms->findAll();

	foreach($aForms as $oForm)
	{
		$aModels[$oForm->id] = "[{$oForm->id}] {$oForm->name}";
	}

	$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Select')
			->options($aModels)
			->caption("Форма")
			->name('form_id')
			->divAttr(array('class' => 'form-group col-lg-6 col-md-6 col-sm-6'))
	));

	$aModels = array(' … ');
	$oShops = Core_Entity::factory('Shop');
	$oShops->queryBuilder()->where('site_id', '=', $oSite->id);
	$aShops = $oShops->findAll();

	foreach($aShops as $oShop)
	{
		$aModels[$oShop->id] = "[{$oShop->id}] {$oShop->name}";
	}

	$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Select')
			->options($aModels)
			->caption("Магазин")
			->name('shop_id')
			->divAttr(array('class' => 'form-group col-lg-6 col-md-6 col-sm-6'))
	));

	$oAdmin_Form_Entity_Form->add($oMainTab);

	$oAdmin_Form_Entity_Form->add(
		Admin_Form_Entity::factory('Button')
		->name('do_show_report')
		->type('submit')
		->class('applyButton btn btn-blue')
	);

	$oAdmin_Form_Entity_Form->execute();
}

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title("Отчет по заявкам")
	->execute();