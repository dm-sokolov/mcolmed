<?php
/**
 * Support.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'support');

$sAdminFormAction = '/admin/support/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Support.title'));

$oAdmin_Form_Entity = Admin_Form_Entity::factory('Form')->id('formEditSupportForm');

$oAdmin_Form_Action_Controller_Type_Edit_Show = Admin_Form_Action_Controller_Type_Edit_Show::create(
	$oAdmin_Form_Entity
);

if ($oAdmin_Form_Controller->getAction() == 'send')
{
	switch (Core_Array::getPost('priority'))
	{
		default:
		case 0:
			$priority = Core::_('Support.low');
		break;
		case 1:
			$priority = Core::_('Support.middle');
		break;
		case 2:
			$priority = Core::_('Support.high');
		break;
		case 3:
			$priority = Core::_('Support.highest');
		break;
	}

	$message = Core::_('Support.mail_subject', Core_Array::getPost('subject')). "\n\n";
	$message .= Core_Array::getPost('text') . "\n";
	$message .= "_____________________________________\n";
	$message .= Core::_('Support.mail_page', Core_Array::getPost('page')) . "\n";
	$message .= "_____________________________________\n";
	$message .= Core::_('Support.mail_version', CURRENT_VERSION) . "\n";
	$message .= Core::_('Support.mail_update', HOSTCMS_UPDATE_NUMBER) . "\n";

	switch (Core_Array::get(Core::$config->get('core_hostcms'), 'integration'))
	{
		default:
		case 0:
			$redaction_name = 'HostCMS.Халява';
		break;
		case 1:
			$redaction_name = 'HostCMS.Мой сайт';
		break;
		case 3:
			$redaction_name = 'HostCMS.Малый бизнес';
		break;
		case 5:
			$redaction_name = 'HostCMS.Бизнес';
		break;
		case 7:
			$redaction_name = 'HostCMS.Корпорация';
		break;
	}

	$message .= Core::_('Support.mail_redaction', $redaction_name) . "\n";
	$message .= "_____________________________________\n";
	$message .= Core::_('Support.mail_contact_information') . "\n";
	$message .= Core::_('Support.mail_name', Core_Array::getPost('name')) ."\n";
	$message .= Core::_('Support.mail_mail', Core_Array::getPost('email')) ."\n";
	$message .= Core::_('Support.mail_phone', Core_Array::getPost('phone')) . "\n";
	$message .= Core::_('Support.mail_contract', Core_Array::getPost('contract')) . "\n";
	$message .= Core::_('Support.mail_pin', Core_Array::getPost('pin')) . "\n";
	$message .= Core::_('Support.mail_priority', $priority) . "\n";

	$memoryLimit = ini_get('memory_limit')
		? ini_get('memory_limit')
		: 'undefined';

	$aDbDrivers = array();
	class_exists('PDO') && $aDbDrivers[] = 'PDO';
	function_exists('mysql_connect') && $aDbDrivers[] = 'mysql';

	$sDbDrivers = implode(', ', $aDbDrivers);

	$message .= "_____________________________________\n";
	$message .= Core::_('Support.mail_system_information') . "\n";
	$message .= Core::_('Support.mail_php_version', phpversion()) ."\n";
	$message .= Core::_('Support.mail_mysql_version', Core_DataBase::instance()->getVersion()) ."\n";
	$message .= Core::_('Support.mail_mysql_drivers', $sDbDrivers) ."\n";
	$message .= Core::_('Support.mail_gd_version', Core_Image::instance('gd')->getVersion()) ."\n";
	$message .= Core::_('Support.mail_pcre_version', Core::getPcreVersion()) ."\n";
	$message .= Core::_('Support.mail_max_execution_time', intval(ini_get('max_execution_time'))) ."\n";
	$message .= Core::_('Support.mail_memory_limit', $memoryLimit) ."\n";

	PHP_VERSION_ID < 80000
		&& $message .= Core::_('Support.mail_func_overload', function_exists('mb_get_info') ? mb_get_info('func_overload') : 'undefined') ."\n";

	$message .= "_____________________________________\n";

	$aSite_Aliases = Core_Entity::factory('Site', CURRENT_SITE)->Site_Aliases->findAll();
	$site_alias = '';
	foreach ($aSite_Aliases as $oSite_Alias)
	{
		$site_alias .= $oSite_Alias->name . "\n";
	}

	$message .= Core::_('Support.mail_alias', $site_alias) . "\n";

	switch (Core_Array::getPost('department'))
	{
		default:
		case 0:
			$section = Core::_('Support.support');
		break;
		case 1:
			$section = Core::_('Support.main');
		break;
	}

	try {
		$oCore_Mail_Driver = Core_Mail::instance()
			->clear()
			->to('support@hostcms.ru')
			->from(trim(EMAIL_TO))
			->header('Reply-To', Core_Array::getPost('email'))
			->subject('HostCMS:' . $section . ':' . Core_Array::getPost('subject'))
			->message($message)
			->contentType('text/plain');

		$aFiles = Core_Array::getFiles('file', NULL);

		if (is_array($aFiles) && isset($aFiles['name']))
		{
			$iCount = count($aFiles['name']);

			for ($i = 0; $i < $iCount; $i++)
			{
				if (intval($aFiles['size'][$i]) > 0)
				{
					$oCore_Mail_Driver->attach(array(
						'filepath' => $aFiles['tmp_name'][$i],
						'filename' => $aFiles['name'][$i])
					);
				}
			}
		}

		Core_Event::notify('Support.oBeforeSend', $oCore_Mail_Driver);

		$oCore_Mail_Driver->send();
	}
	catch (Exception $e){
		Core_Message::show($e->getMessage(), 'error');
	}

	$oAdmin_Form_Action_Controller_Type_Edit_Show->message = $oAdmin_Form_Action_Controller_Type_Edit_Show->message . (
		$oCore_Mail_Driver->getStatus()
			? Core_Message::get(Core::_('Support.success'))
			: Core_Message::get(Core::_('Support.error'), 'error')
	);
}

$oAdmin_Form_Action_Controller_Type_Edit_Show->message = $oAdmin_Form_Action_Controller_Type_Edit_Show->message . Core_Message::get(Core::_('Support.warning'), 'info');

$oAdmin_Form_Action_Controller_Type_Edit_Show->Admin_Form_Controller($oAdmin_Form_Controller);

$windowId = $oAdmin_Form_Controller->getWindowId();

$oAdmin_Form_Entity
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Input')
					->name('subject')
					->caption(Core::_('Support.subject'))
					->class('form-control')
					->format(
						array(
							'minlen' => array('value' => 10),
							'maxlen' => array('value' => 255)
						)
					)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-8'))
			)
			->add(
				Admin_Form_Entity::factory('Select')
					->name('department')
					->caption(Core::_('Support.department'))
					->options(
						array(
							Core::_('Support.support'),
							Core::_('Support.main')
						)
					)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			)
	)->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Textarea')
					->name('text')
					->caption(Core::_('Support.text'))
					->rows(15)
			)
	)
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Input')
				->name('page')
				->caption(Core::_('Support.page'))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-8'))
			)
			->add(
				Admin_Form_Entity::factory('Select')
					->name('priority')
					->caption(Core::_('Support.priority'))
					->options(
						array(
							Core::_('Support.low'),
							Core::_('Support.middle'),
							Core::_('Support.high'),
							Core::_('Support.highest')
						)
					)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			)
	)
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Input')
					->name('name')
					->caption(Core::_('Support.name'))
					->format(
						array('minlen' => array('value' => 3)),
						array('maxlen' => array('value' => 255))
					)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name('email')
					->caption(Core::_('Support.email'))
					->format(
						array(
							'lib' => array('value' => 'email'),
							'minlen' => array('value' => 7),
							'maxlen' => array('value' => 255)
						)
					)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name('phone')
					->caption(Core::_('Support.phone'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			)
	)->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('Input')
					->name('contract')
					->caption(Core::_('Support.contract'))
					->value(defined('HOSTCMS_CONTRACT_NUMBER') ? HOSTCMS_CONTRACT_NUMBER : '')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->readonly('readonly')
			)
			->add(
				Admin_Form_Entity::factory('Input')
					->name('pin')
					->caption(Core::_('Support.pin'))
					->value(defined('HOSTCMS_PIN_CODE') ? HOSTCMS_PIN_CODE : '')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->readonly('readonly')
			)
	)->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add(
				Admin_Form_Entity::factory('File')
					->type('file')
					->name("file[]")
					->caption(Core::_('Support.file'))
					->largeImage(
						array(
							'show_params' => FALSE,
							'show_description' => FALSE
						)
					)
					->smallImage(
						array('show' => FALSE))
					->divAttr(array('id' => 'file', 'class' => 'col-xs-12 col-sm-6 add-deal-attachment'))
					->add(
						Admin_Form_Entity::factory('Code')
							->html('<div class="input-group-addon no-padding add-remove-property"><div class="no-padding-left col-lg-12"><div class="btn btn-palegreen" onclick="$.cloneFile(\'' . $windowId .'\'); event.stopPropagation();"><i class="fa fa-plus-circle close"></i></div>
							<div class="btn btn-darkorange" onclick="$(this).parents(\'#file\').remove(); event.stopPropagation();"><i class="fa fa-minus-circle close"></i></div>
							</div>
							</div>')
					)
			)
	)
	->add(
		Admin_Form_Entity::factory('Buttons')
		->add(
			Admin_Form_Entity::factory('Button')
				->name('button')
				->type('submit')
				->value(Core::_('Support.button'))
				->class('applyButton btn btn-blue')
				->onclick($oAdmin_Form_Controller->getAdminSendForm('send'))
		)
	);

$oAdmin_Form_Action_Controller_Type_Edit_Show
	->Admin_Form_Controller($oAdmin_Form_Controller)
	->title(Core::_('Support.title'))
	->buttons(NULL);

Core_Event::notify('Support.oBeforeShow', $oAdmin_Form_Action_Controller_Type_Edit_Show, array($oAdmin_Form_Entity));

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->children($oAdmin_Form_Action_Controller_Type_Edit_Show->children)
	->pageTitle($oAdmin_Form_Action_Controller_Type_Edit_Show->title)
	->module($oAdmin_Form_Controller->getModule())
	->content($oAdmin_Form_Action_Controller_Type_Edit_Show->showEditForm())
	->message($oAdmin_Form_Action_Controller_Type_Edit_Show->message)
	->show();

Core_Skin::instance()->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	//->content($oAdmin_Form_Action_Controller_Type_Edit_Show->showEditForm())
	->message($oAdmin_Form_Action_Controller_Type_Edit_Show->message)
	->title(Core::_('Support.title'))
	->module($sModule)
	->execute();
