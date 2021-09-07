<?php
/**
 * Hostcms Bitrix24
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'hostcms_bitrix24');

$sAdminFormAction = '/admin/hostcms/bitrix24/index.php';
$sPageTitle = Core::_('Hostcms_Bitrix24.title');

$oSite = Core_Entity::factory('Site', CURRENT_SITE);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sPageTitle);

$aTypicalConfig = array(
	'siteuser' => 1,
	'form' => 1,
	'shop' => 1,
	'http' => '',
	'secret' => '',
	'bitrix_user' => 1,
	'new_order' => 'NEW',
	'paid_order' => 'WON',
	'canceled_order' => 'LOSE'
);

$aConfig = Core_Config::instance()->get('hostcms_bitrix24_config');

if ($oAdmin_Form_Controller->getAction() == 'exec')
{
	foreach ($aTypicalConfig as $name => $value)
	{
		switch ($name)
		{
			case 'siteuser':
			case 'form':
			case 'shop':
			case 'bitrix_user':
				$aConfig[$oSite->id][$name] = intval(Core_Array::getPost($name));
			break;
			case 'http':
				$aConfig[$oSite->id][$name] = rtrim(strval(Core_Array::getPost($name)), '/');
			break;
			case 'secret':
			case 'new_order':
			case 'paid_order':
			case 'canceled_order':
				$aConfig[$oSite->id][$name] = strval(Core_Array::getPost($name));
			break;
		}
	}

	Core_Config::instance()->set('hostcms_bitrix24_config', $aConfig);
}

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle($sPageTitle);

$formSettings = Core_Array::getPost('hostcms', array())
	+ array(
		'action' => NULL,
		'window' => 'id_content',
	);

$windowId = Core_Str::escapeJavascriptVariable($formSettings['window']);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sPageTitle)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

$oMainTab
	->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
	;

$oMainRow1->add(
	Admin_Form_Entity::factory('Div')
		->class('form-group col-xs-12 bold')
		->value(Core::_('Hostcms_Bitrix24.exchange'))
);

!isset($aConfig[$oSite->id]) && $aConfig[$oSite->id] = array();
$aConfig[$oSite->id] = $aConfig[$oSite->id] + $aTypicalConfig;

$access = 0;

foreach ($aConfig[$oSite->id] as $entity => $value)
{
	$oModule = Core_Entity::factory('Module')->getByPath($entity);

	if (!is_null($oModule) && $oModule->active)
	{
		switch ($entity)
		{
			case 'siteuser':
			case 'form':
				$oMainRow1->add(
					Admin_Form_Entity::factory('Checkbox')
						->caption(Core::_('Hostcms_Bitrix24.' . $entity))
						->value($value)
						->name($entity)
						->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
				);
			break;
			case 'shop':
				$oMainRow2
					->add(
						Admin_Form_Entity::factory('Checkbox')
							->caption(Core::_('Hostcms_Bitrix24.' . $entity))
							->value($value)
							->name($entity)
							->divAttr(array('class' => 'form-group col-xs-12 col-md-3 margin-top-21'))
					)
					->add(
						Admin_Form_Entity::factory('Input')
							->caption(Core::_('Hostcms_Bitrix24.new_order'))
							->value(Core_Array::get($aConfig[$oSite->id], 'new_order', ''))
							->name('new_order')
							->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
					)
					->add(
						Admin_Form_Entity::factory('Input')
							->caption(Core::_('Hostcms_Bitrix24.paid_order'))
							->value(Core_Array::get($aConfig[$oSite->id], 'paid_order', ''))
							->name('paid_order')
							->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
					)
					->add(
						Admin_Form_Entity::factory('Input')
							->caption(Core::_('Hostcms_Bitrix24.canceled_order'))
							->value(Core_Array::get($aConfig[$oSite->id], 'canceled_order', ''))
							->name('canceled_order')
							->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
					);
				break;
		}

		$access++;
	}
	elseif ($entity == 'http' || $entity == 'secret' || $entity == 'bitrix_user')
	{
		$oMainRow4->add(
			Admin_Form_Entity::factory('Input')
				->caption(Core::_('Hostcms_Bitrix24.' . $entity))
				->value($value)
				->name($entity)
				->divAttr(array('class' => 'form-group col-xs-12 col-md-4'))
		);
	}
}

if ($access)
{
	$oMainRow3->add(
		Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 bold')
			->value(Core::_('Hostcms_Bitrix24.settings'))
	);

	$oButtonDiv = Admin_Form_Entity::factory('Div')
		->class('form-group col-xs-12')
		->add(
			Admin_Form_Entity::factory('Button')
			->name('apply')
			->type('submit')
			->value(Core::_('Hostcms_Bitrix24.save'))
			->class('applyButton btn btn-blue')
			->onclick($oAdmin_Form_Controller->getAdminSendForm('exec'))
		);

	$oMainRow5->add($oButtonDiv);
}
else
{
	Core_Message::show(Core::_('Hostcms_Bitrix24.not_access'), 'error');
}

Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($sAdminFormAction)
	->add($oMainTab)
	->execute();

$content = ob_get_clean();

ob_start();

$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title($sPageTitle)
	->module($sModule)
	->execute();