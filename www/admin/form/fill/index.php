<?php
/**
 * Forms.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'form');

// Form fill's file download
if (Core_Array::getGet('downloadFile'))
{
	$oForm_Fill_Field = Core_Entity::factory('Form_Fill_Field')->find(intval(Core_Array::getGet('downloadFile')));
	if (!is_null($oForm_Fill_Field->id) && $oForm_Fill_Field->Form_Field->Form->Site->id == CURRENT_SITE)
	{
		$filePath = $oForm_Fill_Field->getPath();
		Core_File::download($filePath, $oForm_Fill_Field->value, array('content_disposition' => 'attachment'));
	}
	else
	{
		throw new Core_Exception('Access denied');
	}
	exit();
}

// Код формы
$iAdmin_Form_Id = 29;
$sAdminFormAction = '/admin/form/fill/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$form_id = intval(Core_Array::getGet('form_id', 0));
$oForm = Core_Entity::factory('Form', $form_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Form_Fill.title', $oForm->name))
	->pageTitle(Core::_('Form_Fill.title', $oForm->name));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Form_Fill.export'))
		->icon('fa fa-upload')
		->img('/admin/images/export.gif')
		->target('_blank')
		->onclick('')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportForm', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$sFormPath = '/admin/form/index.php';

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Form.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sFormPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sFormPath, NULL, NULL, '')
		)
);

$additionalParams = 'form_id=' . $form_id;

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Form_Fill.form_fills'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);


// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oForm_Fill_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Form_Fill_Controller_Edit', $oAdmin_Form_Action
	);

	$oForm_Fill_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oForm_Fill_Controller_Edit);
}

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

// Действие экспорта
$oAdminFormActionExport = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('exportForm');

if ($oAdminFormActionExport && $oAdmin_Form_Controller->getAction() == 'exportForm')
{
	$Form_Fill_Export_Controller = new Form_Fill_Export_Controller($oForm);
	$Form_Fill_Export_Controller->execute();
}

// Действие "Изменить статус"
$oAdminFormActionChangeStatus = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('changeStatus');

if ($oAdminFormActionChangeStatus && $oAdmin_Form_Controller->getAction() == 'changeStatus')
{
	$oFormFillControllerStatus = Admin_Form_Action_Controller::factory(
		'Form_Fill_Controller_Status', $oAdminFormActionChangeStatus
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oFormFillControllerStatus);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Form_Fill')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('form_id', '=', $form_id)
	)
);

// $oAdmin_Form_Dataset->changeField('datetime', 'type', 10);

// Список значений для фильтра и поля
$aForm_Statuses = Core_Entity::factory('Form_Status')->findAll();
$aList = array('0' => Core::_('Admin.none'));
foreach ($aForm_Statuses as $oForm_Status)
{
	$aList[$oForm_Status->id] = $oForm_Status->name;
}

$oAdmin_Form_Dataset
	->changeField('form_status_id', 'type', 8)
	->changeField('form_status_id', 'list', $aList);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();