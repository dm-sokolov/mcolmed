<?php

/**
 * Counter.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'counter');

// Код формы
$iAdmin_Form_Id = 123;
$sAdminFormAction = '/admin/counter/ip/index.php';

$sCounterPath = '/admin/counter/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$sTitle = Core::_('Counter.ip');

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
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Counter.title'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sCounterPath, NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sCounterPath, NULL, NULL, ''))
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
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

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Counter_Visit')
);

(!isset($oAdmin_Form_Controller->request['admin_form_filter_from_549'])
	|| $oAdmin_Form_Controller->request['admin_form_filter_from_549'] == '')
		&& $oAdmin_Form_Controller->request['admin_form_filter_from_549'] = Core_Date::timestamp2date(time()) . ' 00:00:00';

(!isset($oAdmin_Form_Controller->request['admin_form_filter_to_549'])
	|| $oAdmin_Form_Controller->request['admin_form_filter_to_549'] == '')
		&& $oAdmin_Form_Controller->request['admin_form_filter_to_549'] = Core_Date::timestamp2date(time()) . ' 23:59:59';

$oAdmin_Form_Dataset->addCondition(
	array('select' => array('*', array('COUNT(id)', 'dataCount')))
)
->addCondition(
	array('where' => array('site_id', '=', CURRENT_SITE))
)
->addCondition(
	array('groupBy' => array('ip'))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

function correctIp($sIp, $oAdmin_Form_Field)
{
	return Core_Str::ip2hex(trim($sIp));
}

$oAdmin_Form_Controller->addFilterCallback('ip', 'correctIp');

$aObjects = $oAdmin_Form_Controller->setDatasetConditions()->getDataset(0)->load();

$iCount = count($aObjects);

if ($iCount)
{
	$sWindowId = $oAdmin_Form_Controller->getWindowId();

	ob_start();

	$aColors = Core_Array::get(Core::$config->get('counter_color'), 'Column3D', array());
	$iCountColors = count($aColors);
	?>
	<div class="col-lg-12">
		<div class="widget counter">
			<div class="widget-body">
				<div class="row">
					<div class="col-xs-12">
						<div id="ipDiagram" class="chart chart-lg"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	$(function(){
		var aScripts = [
			'jquery.flot.js',
			'jquery.flot.time.min.js',
			'jquery.flot.categories.min.js',
			'jquery.flot.tooltip.min.js',
			'jquery.flot.crosshair.min.js',
			'jquery.flot.selection.min.js',
			'jquery.flot.pie.min.js',
			'jquery.flot.resize.js'
		];

		$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {
			var ipDiagramData = [];

			<?php
			$i = 0;

			foreach ($aObjects as $key => $oObject)
			{
				?>
				ipDiagramData.push(
					{
						label:'<?php echo Core_Str::escapeJavascriptVariable(htmlspecialchars($oObject->ipBackend()))?>',
						data: [[<?php echo $i . ', ' . $oObject->dataCount?>]],
						color: '#<?php echo $aColors[$i % $iCountColors]?>'
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
			var placeholder = $("#<?php echo $sWindowId?> #ipDiagram");
			var plot = $.plot(placeholder, ipDiagramData, options);
		});
	});
	</script>

	<?php

	$oAdmin_Form_Controller->addEntity(
		Admin_Form_Entity::factory('Code')->html(ob_get_clean())
	);
}

$oAdmin_Form_Controller
	->execute();