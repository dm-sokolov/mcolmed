<?php

if (Core::moduleIsActive('form'))
{
	$formId = Core_Array::getGet('ajaxForm', 1);

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

	if (!is_null(Core_Array::getPost($oForm->button_name)) && Core_Array::getRequest('pole') == '')
	{
		$Form_Controller_Show
			->values($_POST + $_FILES)
			// 0 - html, 1- plain text
			->mailType(Core_Array::get(Core_Page::instance()->libParams, 'mailType'))
			->mailXsl(
				Core_Entity::factory('Xsl')->getByName(Core_Array::get(Core_Page::instance()->libParams, 'notificationMailXsl'))
			)
			->mailFromFieldName(Core_Array::get(Core_Page::instance()->libParams, 'emailFieldName'))
			->process();
	}

	$Form_Controller_Show
		->xsl(
			Core_Entity::factory('Xsl')->getByName($xslName)
		)
		->show();
}
else
{
	?>
	<h1>Формы</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/forms/">Формы</a>&raquo; доступен в редакциях &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo;, &laquo;<a href="http://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo; и &laquo;<a href="http://www.hostcms.ru/hostcms/editions/small-business/">Малый бизнес</a>&raquo;.</p>
	<?php
}