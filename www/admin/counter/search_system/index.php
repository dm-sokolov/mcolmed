<?php

/**
 * Counter.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'counter');

// Код формы
$iAdmin_Form_Id = 108;
$sAdminFormAction = '/admin/counter/search_system/index.php';

$sCounterPath = '/admin/counter/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$sTitle = Core::_('Counter_Page.search_system');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sTitle)
	->pageTitle($sTitle);

$sFormPath = $oAdmin_Form_Controller->getPath();

// подключение верхнего меню
include CMS_FOLDER . '/admin/counter/menu.php';

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Строка навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Counter.title'))
	->href($oAdmin_Form_Controller->getAdminLoadHref($sCounterPath, NULL, NULL, ''))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sCounterPath, NULL, NULL, ''))
)
->add(Admin_Form_Entity::factory('Breadcrumb')
	->name($sTitle)
	->href($oAdmin_Form_Controller->getAdminLoadHref($sFormPath, NULL, NULL, ''))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sFormPath, NULL, NULL, ''))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Counter_Page')
);

$aSetting = Core_Array::get(Core::$config->get('counter_setting'), 'setting', array());
$iFromTimestamp = strtotime("-{$aSetting['showDays']} day");

!isset($oAdmin_Form_Controller->request['admin_form_filter_from_446']) && $oAdmin_Form_Controller->request['admin_form_filter_from_446'] = Core_Date::timestamp2date($iFromTimestamp);
!isset($oAdmin_Form_Controller->request['admin_form_filter_to_446']) &&	$oAdmin_Form_Controller->request['admin_form_filter_to_446'] = Core_Date::timestamp2date(time());

// Ограничение по сайту
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('*',
		array('COUNT(id)', 'adminCount')
		)
	)
)
->addCondition(
	array('where' =>
			array('site_id', '=', CURRENT_SITE)
	)
)
->addCondition(
	array('where' =>
			array('search_system', '!=', '')
	)
)
->addCondition(
	array('groupBy' => array('search_system'))
)
->addCondition(
	array('groupBy' => array('search_query'))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$aObjects = $oAdmin_Form_Controller->setDatasetConditions()->getDataset(0)->load();

$iCount = count($aObjects);

if ($iCount)
{
	ob_start();

	$aColors = Core_Array::get(Core::$config->get('counter_color'), 'Column3D', array());
	$iCountColors = count($aColors);
	?>
	<div class="col-lg-12">
		<div class="widget counter">
			<div class="widget-body">
				<div class="row">
					<div class="col-xs-12">
						<div id="searchSystemDiagram" class="chart chart-lg"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	$(function(){
		var gridbordercolor = "#eee";
		var searchSystemDiagramData = [];

		<?php
		$i = 0;

		foreach ($aObjects as $key => $oObject)
		{
			?>
			searchSystemDiagramData.push(
				{
					label:'<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($oObject->search_system . '('. $oObject->search_query .')'))?>',
					data:[[<?php echo $i . ', ' .  $oObject->adminCount?>]],
					color: '#<?php echo $aColors[$i % count($aColors)]?>'
				}
			);
			<?php
			$i++;
		}
		?>
		var options = {

			series: {
				bars: {
					//order: 1,
					show: true,
					borderWidth: 0,
					barWidth: 0.4,
					lineWidth: .5,
					fillColor: {
						colors: [{
							opacity: 0.4
						}, {
							opacity: 1
						}]
					}
				}
			},
			legend: {
				show: false
			},
			xaxis: {
				tickDecimals: 0,
				color: '#f3f3f3',
				tickFormatter: function (val, axis) {
					return "";
				},
			},
			yaxis: {
				min: 0,
				color: '#f3f3f3',
				tickDecimals: 0
				/*
				tickFormatter: function (val, axis) {
					return "";
				},*/
			},
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
				content: "<b>%s</b> : <span>%y</span>",
			}
		};
		var placeholder = $("#searchSystemDiagram");
		var plot = $.plot(placeholder, searchSystemDiagramData, options);
	});
	</script>
	<?php
	$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')->html(ob_get_clean())
	);
}

$oAdmin_Form_Controller->execute();