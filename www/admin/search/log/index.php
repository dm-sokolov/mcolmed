<?php
/**
 * Search.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'search');

// Код формы
$iAdmin_Form_Id = 103;
$sAdminFormAction = '/admin/search/log/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Search_Log.title'))
	->pageTitle(Core::_('Search_Log.title'));

$sSearchPath = '/admin/search/index.php';

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Search.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sSearchPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sSearchPath, NULL, NULL, '')
	)
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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Search_Log')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('select' =>
		array('*', array('COUNT(*)', 'count'))
	)
)->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
)
->addCondition(
	array('groupBy' => array('query') )
)
->addExternalField('count');

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

if (!isset($oAdmin_Form_Controller->request['admin_form_filter_from_437']))
{
	$oAdmin_Form_Controller->request['admin_form_filter_from_437'] = Core_Date::timestamp2date(strtotime('-1 day'));
}
if (!isset($oAdmin_Form_Controller->request['admin_form_filter_to_437']))
{
	$oAdmin_Form_Controller->request['admin_form_filter_to_437'] = Core_Date::timestamp2date(time());
}

$aObjects = $oAdmin_Form_Controller->setDatasetConditions()->getDataset(0)->load();

if (count($aObjects))
{
	ob_start();

	$aColors = array(
		'AFD8F8',
		'F6BD0F',
		'8BBA00',
		'FF8E46',
		'008E8E',
		'D64646',
		'8E468E',
		'588526',
		'B3AA00',
		'008ED6',
		'9D080D',
		'A186BE'
	);

	$iCountColors = count($aColors);

	$oXml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>
		<graph decimalPrecision="0"
			shownames="0"
			showValues="0"
			canvasBgColor="fafafa"
			divlinecolor="cccccc"
			hoverCapBorder="A7BD34"
			hoverCapBgColor="A7BD34"
			baseFontColor="ffffff"
			outCnvBaseFontColor="000000"></graph>');

	foreach ($aObjects as $key => $oObject)
	{
		$oSet = $oXml->addChild('set');
		$oSet->addAttribute('name', $oObject->query);
		$oSet->addAttribute('value', $oObject->count);
		$oSet->addAttribute('color', $aColors[$key % $iCountColors]);
		// $oSet->addAttribute('showName', 0);
	}

	/*
	$sScript = "(function($){
	var chart = new FusionCharts('/admin/js/fusionchart/FCF_Column3D.swf ', 'ChartId', '600', '350');
	chart.setDataXML('" . Core_Str::escapeJavascriptVariable($oXml->asXml()) . "');
	chart.render('chartdiv');
	})(jQuery);";

	Core::factory('Core_Html_Entity_Div')
		->add(Core::factory('Core_Html_Entity_Div')->id('chartdiv'))
		->add(Core::factory('Core_Html_Entity_Script')->value($sScript))
		->execute();
		*/

	$oAdmin_Form_Controller->addEntity(
		Admin_Form_Entity::factory('Code')->html(ob_get_clean())
	);
}

// Показ формы
$oAdmin_Form_Controller->execute();
