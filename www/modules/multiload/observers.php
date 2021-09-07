<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

if (Core::moduleIsActive('multiload'))
{
	$aConfig = Multiload_Controller::instance()->getConfig();

	if ($aConfig['shop_item_tab'])
	{
		//Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onBeforeExecute', array('Multiload_Observers_ItemTab', 'onBeforeExecute'));		
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('Multiload_Observers_ItemTab', 'onAfterRedeclaredPrepareFormShop'));
	}

	if ($aConfig['informationsystem_item_tab'])
	{
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('Multiload_Observers_ItemTab', 'onAfterRedeclaredPrepareFormIs'));
	}
}