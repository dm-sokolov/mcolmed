<?php
/**
 * Leads.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'lead');

// Код формы
$iAdmin_Form_Id = 272;
$sAdminFormAction = '/admin/lead/note/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iLeadId = intval(Core_Array::getGet('lead_id', 0));
$oLead = Core_Entity::factory('Lead', $iLeadId);

// var_dump(Core_Array::getGet('form'));

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Lead_Note.lead_notes_title'))
	->pageTitle(Core::_('Lead_Note.lead_notes_title'))
	->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	)
	->addView('note', 'Lead_Controller_Note')
	->view('note');

$oAdmin_Form_Controller->addExternalReplace('{lead_id}', $oLead->id);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oLead_Note_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Lead_Note_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLead_Note_Controller_Edit);
}

// Добавление заметки
$oAdmin_Form_Action_Add_Lead_Note = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('addLeadNote');

if ($oAdmin_Form_Action_Add_Lead_Note && $oAdmin_Form_Controller->getAction() == 'addLeadNote')
{
	$oLead_Note_Controller_Add = Admin_Form_Action_Controller::factory(
		'Lead_Note_Controller_Add', $oAdmin_Form_Action_Add_Lead_Note
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLead_Note_Controller_Add);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Lead_Note')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('where' => array('lead_notes.lead_id', '=', $oLead->id))
	)->addCondition(
		array('orderBy' => array('lead_notes.id', 'DESC'))
	);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();