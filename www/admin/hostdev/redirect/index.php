<?php

/**
 * Redirects
 *
 * @version 1.35
 * @author Eugeny Panikarowsky - evgenii_panikaro@mail.ru
 * @copyright © 2018 Eugeny Panikarowsky
 *
*/

require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule='hostdev_redirect');


$sAdminFormAction = '/admin/hostdev/redirect/index.php';


$oAdmin_Form = Core_Entity::factory('Admin_Form')->getByguid('B67A8F0B-A90C-A1EF-89F2-F3EE1D62FF16');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('hostdev_redirect.listurl'))
	->pageTitle(Core::_('hostdev_redirect.listurl'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
		->name(Core::_('hostdev_redirect.menu'))
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('hostdev_redirect.redirect_add'))
				->img('/admin/images/folder_page_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);


// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Путь к контроллеру формы разделов информационных систем
$sInformationsystemDirPath = '/admin/hostdev/redirect/index.php';

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('hostdev_redirect.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sInformationsystemDirPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sInformationsystemDirPath, NULL, NULL, '')
	)
);

// Действие "Загрузка списка групп информационной системы"
$oAdminFormActionLoadGroupsList = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('loadGroupsList');

if ($oAdminFormActionLoadGroupsList && $oAdmin_Form_Controller->getAction() == 'loadGroupsList')
{
	$oInformationsystem_Item_Controller_Edit = new Informationsystem_Item_Controller_Edit($oAdminFormActionLoadGroupsList);
	$groups = $oInformationsystem_Item_Controller_Edit->fillInformationsystemGroup(Core_Array::getGet('informationsystem_id'), 0);
	echo json_encode($groups);
	exit();	
}

// Действие "Загрузка списка групп информационной системы"
$oAdminFormActionLoadShopGroupsList = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('loadShopGroupsList');

if ($oAdminFormActionLoadShopGroupsList && $oAdmin_Form_Controller->getAction() == 'loadShopGroupsList')
{
	$oInformationsystemLoadShopGroupsList = new Admin_Form_Action_Controller_Type_Load_Select_Options
	(
		$oAdminFormActionLoadShopGroupsList
	);
	$oInformationsystemLoadShopGroupsList
		->model(Core_Entity::factory('Shop_Group'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_id', '=', Core_Array::getGet('shop_id')))
		);

	$oAdmin_Form_Controller->addAction($oInformationsystemLoadShopGroupsList);
}
// Действие "Загрузка списка товаров"
$oAdminFormActionLoadShopGroupsList = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('loadShopItemList');

if ($oAdminFormActionLoadShopGroupsList && $oAdmin_Form_Controller->getAction() == 'loadShopItemList')
{
	$oShopLoadGroupsList = new Admin_Form_Action_Controller_Type_Load_Select_Options
	(
		$oAdminFormActionLoadShopGroupsList
	);
	$oShopLoadGroupsList
		->model(Core_Entity::factory('Shop_Item'))
		->defaultValue(' … ')
		->addCondition
		(
			array('where' => array('shop_id', '=', Core_Array::getGet('shop_id')))
		)
		->addCondition
		(
			array('where' => array('shop_group_id','=', Core_Array::getGet('shop_group_id'))
		)
	);

	$oAdmin_Form_Controller->addAction($oShopLoadGroupsList);
}
// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oRedirect_Controller_Edit = new Hostdev_Redirect_Controller_Edit(
		$oAdmin_Form_Action
	);

	$oRedirect_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oRedirect_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = new Admin_Form_Action_Controller_Type_Apply
	(
		$oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = new Admin_Form_Action_Controller_Type_Copy(
		$oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('hostdev_redirect')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
);
	
// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();