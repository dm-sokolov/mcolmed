<?php

/**
 * Counter.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'counter');

// Код формы
$iAdmin_Form_Id = 97;
$sAdminFormAction = '/admin/counter/visitors/index.php';

$sCounterPath = '/admin/counter/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Counter.visitors_title'))
	->pageTitle(Core::_('Counter.visitors_title'));

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
		->name(Core::_('Counter.visitors_title'))
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

$aSetting = Core_Array::get(Core::$config->get('counter_setting'), 'setting', array());
$iFromTimestamp = strtotime("-{$aSetting['showDays']} day");

!isset($oAdmin_Form_Controller->request['admin_form_filter_from_408']) && $oAdmin_Form_Controller->request['admin_form_filter_from_408'] = Core_Date::timestamp2date($iFromTimestamp) . ' 00:00:00';
!isset($oAdmin_Form_Controller->request['admin_form_filter_to_408']) &&	$oAdmin_Form_Controller->request['admin_form_filter_to_408'] = Core_Date::timestamp2date(time()) . ' 23:59:59';

// Ограничение по сайту
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('counter_visits.*', 'counter_pages.page', 'counter_referrers.referrer'))
)
->addCondition(
	array('leftJoin' => array('counter_referrers', 'counter_visits.counter_referrer_id', '=', 'counter_referrers.id'))
)
->addCondition(
	array('leftJoin' => array('counter_pages', 'counter_visits.counter_page_id', '=', 'counter_pages.id'))
)
->addCondition(
	array('where' => array('counter_visits.site_id', '=', CURRENT_SITE))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

/**
 * Возвращает ID пользователя по логину, введенному в фильтре
 */
function correctSiteuserName($sSiteuserLogin, $oAdmin_Form_Field)
{
	$sSiteuserLogin = trim($sSiteuserLogin);
	if (Core::moduleIsActive('siteuser') && strlen($sSiteuserLogin))
	{
		$oSiteuser = Core_Entity::factory('Site', CURRENT_SITE)->Siteusers->getByLogin($sSiteuserLogin);

		if (!is_null($oSiteuser))
		{
			return $oSiteuser->id;
		}
	}

	return 0;
}

function correctIp($sIp, $oAdmin_Form_Field)
{
	return Core_Str::ip2hex($sIp);
}

$oAdmin_Form_Controller
	->addFilterCallback('siteuser_id', 'correctSiteuserName')
	->addFilterCallback('ip', 'correctIp')
	->execute();