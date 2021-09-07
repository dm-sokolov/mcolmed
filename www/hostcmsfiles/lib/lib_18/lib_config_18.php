<?php

if (Core_Array::getRequest('ajaxForm'))
{
	$formId = Core_Array::getRequest('ajaxForm');

	$oForm = Core_Entity::factory('Form', $formId);

	$Form_Controller_Show = new Form_Controller_Show($oForm);

	if (!is_null(Core_Array::getRequest($oForm->button_name)) && Core_Array::getRequest('pole') == '')
	{

		$Form_Controller_Show
			->values($_POST + $_FILES)
			->mailType(0)
			->mailXsl(
				Core_Entity::factory('Xsl')->getByName('ПисьмоКураторуФормыВФорматеHTML')
			)
			->mailFromFieldName('art.pimnev@gmail.com')
			->process();

		$Form_Controller_Show
			->xsl(
				Core_Entity::factory('Xsl')->getByName('ОтобразитьФормуЗвонок')
			)
			->show();

		header('Content-Type: text/html; charset=utf-8');
		exit();
	}
}