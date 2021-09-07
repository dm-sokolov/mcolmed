<?php

if (Core_Array::getRequest('ajaxForm'))
{
	$formId = Core_Array::getRequest('ajaxForm');

	$oForm = Core_Entity::factory('Form', $formId);

	$Form_Controller_Show = new Form_Controller_Show($oForm);

	switch ($formId)
	{
		case 2:
			$xslName = 'ОтобразитьФормуКонсультация';
		break;
		case 3:
			$xslName = 'ОтобразитьФормуКонсультацияВопросВрачу';
		break;
		default:
			$xslName = 'ОтобразитьФормуЗвонок';
	}

	if (!is_null(Core_Array::getRequest($oForm->button_name)))
	{

		$Form_Controller_Show
			->values($_POST + $_FILES)
			->mailType(Core_Array::get(Core_Page::instance()->libParams, 'mailType'))
			->mailXsl(
				Core_Entity::factory('Xsl')->getByName(Core_Array::get(Core_Page::instance()->libParams, 'notificationMailXsl'))
			)
			->mailFromFieldName(Core_Array::get(Core_Page::instance()->libParams, 'emailFieldName'))
			->process();

		$Form_Controller_Show
			->xsl(
				Core_Entity::factory('Xsl')->getByName($xslName)
			)
			->show();

		header('Content-Type: text/html; charset=utf-8');
		exit();
	}
}