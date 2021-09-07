<?php

/**
 * Система управления сайтом HostCMS v. 5.xx
 *
 * Copyright © 2005-2011 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 *
 * Класс модуля "Формы".
 *
 * Файл: /modules/Forms/Forms.class.php
 *
 * @package HostCMS 5
 * @author Hostmake LLC
 * @version 5.x
 */
class Forms
{
	/**
	* Код ошибки
	*
	* @var int
	* @access private
	*/
	var $error;

	function getArrayForm($oForm)
	{
		return array(
			'forms_id' => $oForm->id,
			'forms_name' => $oForm->name,
			'forms_email' => $oForm->email,
			'forms_description' => $oForm->description,
			'forms_button_type' => 0,
			'forms_button_text_value' => 'Отправить',
			'forms_button_name' => $oForm->button_name,
			'forms_button_value' => $oForm->button_value,
			'forms_captcha_used' => $oForm->use_captcha,
			'users_id' => $oForm->user_id,
			'forms_mail_subject' => $oForm->email_subject,
			'site_id' => $oForm->site_id
		);
	}

	function getArrayFormFill($oFormFill)
	{
		return array(
			'forms_fill_id' => $oFormFill->id,
			'forms_id' => $oFormFill->form_id,
			'forms_fill_ip' => $oFormFill->ip,
			'forms_fill_date' => $oFormFill->datetime,
			'forms_fill_read' => $oFormFill->read
		);
	}

	/**
	* Вставка/обновление информации о форме
	*
	* @param int $type параметр, определяющий производится вставка или обновление информации о форме( 0 – вставка, 1 обновление)
	* @param int $forms_id идентификатор обновляемой формы (при вставке равен 0)
	* @param string $forms_name имя формы
	* @param string $forms_description описание формы
	* @param string $forms_email e-mail куратора формы
	* @param int $forms_button_type тип кнопки для формы (0 – простая кнопка, 1 - кнопка типа button)
	* @param string $forms_button_name название кнопки (английское)
	* @param string $forms_button_value значение, передаваемое при нажатии кнопки
	* @param string $forms_button_text_value текст на кнопке
	* @param array $param массив дополнительных параметров
	* <br> $param['forms_captcha_used'] параметр, определяющий использовать при отображении формы captcha (1 использовать, 0 не использовать)
	* @param string $forms_mail_subject тема письма администратору об отправке формы
	* @param int $users_id идентификатор пользователя, если false - берется текущий пользователь
	* @param int $site_id идентификатор сайта, если false - берется текущий сайт
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $type = 0;
	* $forms_id = 0;
	* $forms_name = 'Новая форма';
	* $forms_description = '';
	* $forms_email = '';
	* $forms_button_type = 1;
	* $forms_button_name = 'Btn';
	* $forms_button_value = '';
	* $forms_button_text_value = '';
	* $param = array();
	* $forms_mail_subject = '';
	*
	* $newid = $Form->InsertForms($type, $forms_id, $forms_name, $forms_description, $forms_email, $forms_button_type, $forms_button_name, $forms_button_value, $forms_button_text_value);
	*
	* // Распечатаем результат
	* echo $newid;
	* ?>
	* </code>
	* @return mixed идентификатор вставленной/обновленной формы в случае успешного выполнения, false в противном случае
	*/
	function InsertForms($type, $forms_id, $forms_name, $forms_description, $forms_email, $forms_button_type, $forms_button_name, $forms_button_value, $forms_button_text_value, $param = array(), $forms_mail_subject = '', $users_id = FALSE, $site_id = FALSE)
	{
		if (!$forms_id)
		{
			$forms_id = NULL;
		}

		$oForm = Core_Entity::factory('Form', $forms_id);

		$oForm->name = $forms_name;
		$oForm->description = $forms_description;
		$oForm->email = $forms_email;
		$oForm->button_name = $forms_button_name;
		$oForm->button_value = $forms_button_value;
		$oForm->email_subject = $forms_mail_subject;

		$oForm->site_id = $site_id === FALSE
			? CURRENT_SITE
			: intval($site_id);

		if (is_null($oForm->id) && $users_id)
		{
			$oForm->user_id = $users_id;
		}

		$oForm->use_captcha = isset($param['forms_captcha_used'])
			? $param['forms_captcha_used']
			: 1;

		$oForm->save();
		return $oForm->id;
	}

	/**
	* Вставка/обновление информации о поле формы
	*
	* @param int $type параметр, определяющий производится вставка или обновление информации о поле формы (0 – вставка, 1 обновление)
	* @param int $forms_fields_id идентификатор обновляемого поля формы (при вставке равен 0)
	* @param int $forms_id идентификатор формы, к которой относится обновляемое/добавляемое поле
	* @param int $lists_id идентификатор списка, с которым связано поле (для полей типа «радиогруппа», «выпадающий список»)
	* @param int $forms_fields_type – тип поля формы (0 – текстовое поле, 1 – поле пароля, 2 – поле загрузки файла,	3 – радиокнопка, 4 – checkbox, 5 – большое текстовое поле, 6 – список, 7 – скрытое поле, 9 - список из чекбоксов)
	* @param string $forms_fields_name английское название поля (тег “name”)
	* @param string $forms_fields_text_name поясняющий текст для поля формы
	* @param string $forms_fields_default_value значение по умолчанию
	* @param int $forms_fields_order порядоковый номер поля
	* @param int $forms_fields_size ширина поля формы (для текстового поля и поля пароля)
	* @param int $forms_fields_rows высота большого текстового поля
	* @param int $forms_fields_cols ширина большого текстового поля
	* @param int $forms_fields_checked параметр, определяющий отображать поле типа checkbox выбранным или нет (0 – не выбрано, 1 выбрано)
	* @param string $forms_fields_comment поясняющий комментарий к полю
	* @param int $forms_fields_obligatory параметр, определяющий является поле обязательным для заполнения (0 – необязательное (по умолчанию), 1 – обязательное)
	* @param int $users_id идентификатор пользователя, если false - берется текущий пользователь.
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $type = 0;
	* $forms_fields_id = 0;
	* $forms_id = 6;
	* $lists_id = 1;
	* $forms_fields_type = 0;
	* $forms_fields_name = 'Field';
	* $forms_fields_text_name = 'Поле';
	* $forms_fields_default_value = '';
	* $forms_fields_order = 10;
	* $forms_fields_size = '';
	* $forms_fields_rows = '';
	* $forms_fields_cols = '';
	* $forms_fields_checked = 0;
	* $forms_fields_comment = '';
	* $forms_fields_obligatory = 0;
	*
	* newid = $Form->InsertFormsFields($type, $forms_fields_id, $forms_id, $lists_id, $forms_fields_type, $forms_fields_name, $forms_fields_text_name, $forms_fields_default_value, $forms_fields_order, $forms_fields_size, $forms_fields_rows, $forms_fields_cols, $forms_fields_checked, $forms_fields_comment);
	*
	* // Распечатаем результат
	* echo $newid;
	*
	* ?>
	* </code>
	* @return mixed идентификатор вставленной/обновленной записи с информацией о поле формы в случае успешного выполнения, false в противном случае
	*/
	function InsertFormsFields($type, $forms_fields_id, $forms_id, $lists_id, $forms_fields_type, $forms_fields_name,
	$forms_fields_text_name, $forms_fields_default_value, $forms_fields_order, $forms_fields_size,
	$forms_fields_rows, $forms_fields_cols, $forms_fields_checked, $forms_fields_comment,
	$forms_fields_obligatory = 0, $users_id = FALSE)
	{
		if (!$forms_fields_id)
		{
			$forms_fields_id = NULL;
		}

		$oForm_Field = Core_Entity::factory('Form_Field', $forms_fields_id);

		$oForm_Field->form_id = intval($forms_id);
		$oForm_Field->list_id = intval($lists_id);
		$oForm_Field->type = intval($forms_fields_type);
		$oForm_Field->name = $forms_fields_name;
		$oForm_Field->caption = $forms_fields_text_name;
		$oForm_Field->default_value = $forms_fields_default_value;
		$oForm_Field->sorting = intval($forms_fields_order);
		$oForm_Field->size = intval($forms_fields_size);
		$oForm_Field->rows = intval($forms_fields_rows);
		$oForm_Field->cols = intval($forms_fields_cols);
		$oForm_Field->checked = intval($forms_fields_checked);
		$oForm_Field->description = $forms_fields_comment;
		$oForm_Field->obligatory = intval($forms_fields_obligatory);

		if (is_null($oForm_Field->id) && $users_id)
		{
			$oForm_Field->user_id = $param['users_id'];
		}

		$oForm_Field->save();
		return $oForm_Field->id;
	}

	/**
	* Вставка/обновление информации о заполнении формы
	*
	* @param int $type параметр, определяющий производится вставка или обновление информации о заполнении формы
	* @param int $forms_fill_id идентификатор редактируемой записи с информацией о заполненной форме (при вставке равен 0)
	* @param int $forms_id идентификатор формы
	* @param int $forms_fill_ip ip-адрес компьютера пользователя, отправившего данные формы
	* @param string $forms_fill_date дата заполнения формы
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $type = 0;
	* $forms_fill_id = 0;
	* $forms_id = 6;
	* $forms_fill_ip = '';
	* $forms_fill_date = date('Y-m-d H:i:s');
	*
	* $newid = $Form->InsertFormsFill($type, $forms_fill_id, $forms_id, $forms_fill_ip, $forms_fill_date);
	*
	* // Распечатаем результат
	* echo $newid;
	* ?>
	* </code>
	* @return mixed идентификатор вставленной/обновленной записи с информацией о заполнении поле формы в случае успешного выполнения, false в противном случае
	*/
	function InsertFormsFill($type, $forms_fill_id, $forms_id, $forms_fill_ip, $forms_fill_date)
	{
		if (!$forms_fill_id)
		{
			$forms_fill_id = NULL;
		}

  		$oForm_Fill = Core_Entity::factory('Form_Fill', $forms_fill_id);
		$oForm_Fill->form_id = intval($forms_id);
		$oForm_Fill->ip = $forms_fill_ip;
		$oForm_Fill->datetime = $forms_fill_date;

		$oForm_Fill->save();

		return $oForm_Fill->id;
	}

	/**
	* Вставка/обновление значений заполненной формы
	*
	* @param int $type параметр, определяющий производится вставка или обновление значений заполненной формы (0 - вставка, 1- обновление)
	* @param int $forms_fill_values_id идентификатор записи, хранящей данные одного из элементов формы
	* @param int $forms_fill_id идентификатор записи, хранящей данные о заполненной форме
	* @param int $forms_fields_id идентификатор элемента заполняемой формы
	* @param string $forms_fill_values_value значение элемента формы
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $type = 0;
	* $forms_fill_values_id = '';
	* $forms_fill_id = 6;
	* $forms_fields_id = 0;
	* $forms_fill_values_value = '';
	*
	* $newid = $Form->InsertFormsFillValues($type, $forms_fill_values_id, $forms_fill_id, $forms_fields_id, $forms_fill_values_value);
	*
	* // Распечатаем результат
	* echo $newid;
	* ?>
	* </code>
	* @return mixed идентификатор добавленной/отредактированной записи, хранящей данные о значении, переданном из формы в случае успешного выполнения, false в противном случае
	*/
	function InsertFormsFillValues($type, $forms_fill_values_id, $forms_fill_id, $forms_fields_id, $forms_fill_values_value)
	{
		if (!$forms_fill_values_id)
		{
			$forms_fill_values_id = NULL;
		}

		$oForm_Fill_Field = Core_Entity::factory('Form_Fill_Field', $forms_fill_values_id);
		$oForm_Fill_Field->form_fill_id = intval($forms_fill_id);
		$oForm_Fill_Field->form_field_id = intval($forms_fields_id);
		$oForm_Fill_Field->value = $forms_fill_values_value;
		$oForm_Fill_Field->save();
		return $oForm_Fill_Field->id;
	}

	/**
	* Получение данных о формах
	*
	* @param int $forms_id идентификатор формы, данные о которой необходимо получить, если $forms_id = -1, то получаем данные о всех формах
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_id = 6;
	*
	* $resource = $Form->SelectForms($forms_id);
	*
	* // Распечатаем результат
	* while($row = mysql_fetch_assoc($resource))
	* {
	* 	print_r($row);
	* }
	* ?>
	* </code>
	* @return mixed resource в случае успешного выполнения, false в потивном случае
	*/
	function SelectForms($forms_id)
	{
		$forms_id = intval($forms_id);

		$queryBuilder = Core_QueryBuilder::select(
				array('id', 'forms_id'),
				array('name', 'forms_name'),
				array('email', 'forms_email'),
				array('description', 'forms_description'),
				array('button_name', 'forms_button_name'),
				array('button_value', 'forms_button_value'),
				array('use_captcha', 'forms_captcha_used'),
				array('user_id', 'users_id'),
				array('email_subject', 'forms_mail_subject'),
				'site_id'
			)
			->from('forms')
			->where('deleted', '=', 0);

		$forms_id != -1 && $queryBuilder->where('id', '=', $forms_id);

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение данных о поле формы
	*
	* @param int $field_id идентификатор поля формы, данные о котором необходимо получить
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $field_id = 6;
	*
	* $resource = $Form->SelectFormsFields($field_id);
	*
	* // Распечатаем результат
	* $row = mysql_fetch_assoc($resource);
	*
	* print_r($row);
	*
	* ?>
	* </code>
	* @return mixed resource в случае успешного выполнения, false в противном случае
	*/
	function SelectFormsFields($field_id)
	{
		$field_id = intval($field_id);

		$queryBuilder = Core_QueryBuilder::select(
				array('id', 'forms_fields_id'),
				array('form_id', 'forms_id'),
				array('list_id', 'lists_id'),
				array('type', 'forms_fields_type'),
				array('size', 'forms_fields_size'),
				array('rows', 'forms_fields_rows'),
				array('cols', 'forms_fields_cols'),
				array('checked', 'forms_fields_checked'),
				array('name', 'forms_fields_name'),
				array('caption', 'forms_fields_text_name'),
				array('default_value', 'forms_fields_default_value'),
				array('sorting', 'forms_fields_order'),
				array('description', 'forms_fields_comment'),
				array('obligatory', 'forms_fields_obligatory'),
				array('user_id', 'users_id')
			)
			->from('form_fields')
			->where('deleted', '=', 0);

		if ($field_id != -1 || $field_id == FALSE)
		{
			$queryBuilder->where('id', '=', $field_id);
		}
		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение данных о полях формы
	*
	* @param int $form_id идентификатор формы
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $form_id = 6;
	*
	* $resource = $Form->GetFormFields($form_id);
	*
	* // Распечатаем результат
	* while($row = mysql_fetch_assoc($resource))
	* {
	* 	print_r($row);
	* }
	* ?>
	* </code>
	* @return mixed resorce в случае успешного выполнения, false в противном случае
	*/
	function GetFormFields($form_id)
	{
		$form_id = intval($form_id);

		$queryBuilder = Core_QueryBuilder::select(
				array('id', 'forms_fields_id'),
				array('form_id', 'forms_id'),
				array('list_id', 'lists_id'),
				array('type', 'forms_fields_type'),
				array('size', 'forms_fields_size'),
				array('rows', 'forms_fields_rows'),
				array('cols', 'forms_fields_cols'),
				array('checked', 'forms_fields_checked'),
				array('name', 'forms_fields_name'),
				array('caption', 'forms_fields_text_name'),
				array('default_value', 'forms_fields_default_value'),
				array('sorting', 'forms_fields_order'),
				array('description', 'forms_fields_comment'),
				array('obligatory', 'forms_fields_obligatory'),
				array('user_id', 'users_id')
			)
			->from('form_fields')
			->where('form_id', '=', $form_id)
			->where('deleted', '=', 0)
			->orderBy('sorting');

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Удаление бланка заполненной формы
	*
	* @param int $forms_fill_id идентификатор удаляемого бланка
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_fill_id = 2;
	*
	* $result = $Form->DelFillForms($forms_fill_id);
	*
	* if ($result)
	* {
	* 	echo "Удаление выполнено успешно";
	* }
	* else
	* {
	* 	echo "Ошибка удаления";
	* }
	* ?>
	* </code>
	* @return boolean true в случае успешного удаления, false в противном случае
	*/
	function DelFillForms($forms_fill_id)
	{
		$forms_fill_id = intval($forms_fill_id);
		Core_Entity::factory('Form_Fill_Field', $forms_fill_id)->markDeleted();
		return TRUE;
	}

	/**
	* Удаление данных о форме
	*
	* @param int $forms_id идентификатор формы, информацию о которой необходимо удалить
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_id = 4;
	*
	* $result = $Form->DelForms($forms_id);
	*
	* if ($result)
	* {
	* 	echo "Удаление выполнено успешно";
	* }
	* else
	* {
	* 	echo "Ошибка удаления";
	* }
	* ?>
	* </code>
	* @return boolean true в случае успешного удаления формы, false в противном случае
	*/
	function DelForms($forms_id)
	{
		$forms_id = intval($forms_id);
		Core_Entity::factory('Form', $forms_id)->markDeleted();
		return TRUE;
	}

	/**
	* Удаление поля формы
	*
	* @param int $forms_fields_id идентификатор удаляемого поля формы
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_fields_id = 8;
	*
	* $result = $Form->DelFormsFields($forms_fields_id);
	*
	* if ($result)
	* {
	* 	echo "Удаление выполнено успешно";
	* }
	* else
	* {
	* 	echo "Ошибка удаления";
	* }
	* ?>
	* </code>
	* @return boolean true в случае успешного удаления поля формы, false в противном случае
	*/
	function DelFormsFields($forms_fields_id)
	{
		$forms_fields_id = intval($forms_fields_id);
		Core_Entity::factory('Form_Field', $forms_id)->markDeleted();
		return TRUE;
	}

	/**
	* Удаление значения одного из полей отправленной формы
	*
	* @param int $forms_fill_values_id идентификатор записи, содержащей значение поля отправленной формы
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_fill_values_id = 4;
	*
	* $result = $Form->DelFillValues($forms_fill_values_id);
	*
	* if ($result)
	* {
	* 	echo "Удаление выполнено успешно";
	* }
	* else
	* {
	* 	echo "Ошибка удаления";
	* }
	* ?>
	* </code>
	* @return boolean true в случае успешного удаления значения поля отправленной формы, false в противном случае
	*/
	function DelFillValues($forms_fill_values_id)
	{
		$forms_fill_values_id = intval($forms_fill_values_id);
		Core_Entity::factory('Form_Fill_Field', $forms_fill_values_id)->delete();
		return TRUE;
	}

	/**
	* Отображение формы
	*
	* @param int $form_id идентификатор формы
	* @param string $xsl_name название XSL-шаблона
	* @param array $external_propertys массив дополнительных свойств для включения в исходный XML код
	* @param array $fill_value массив заполненых значений для полей. Если не передан, используются данные, переданные методом POST
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $form_id = 6;
	* $xsl_name = 'ОтобразитьФорму';
	*
	* $result = $Form->ShowForm($form_id, $xsl_name);
	*
	* if ($result)
	* {
	* 	echo "Удаление выполнено успешно";
	* }
	* else
	* {
	* 	echo "Ошибка удаления";
	* }
	* ?>
	* </code>
	* @return boolean true в случае успешного выполнения, false в противном случае
	*/
	function ShowForm($form_id, $xsl_name, $external_propertys = array(), $fill_value = array())
	{
		$form_id = intval($form_id);

		$Captcha = new Captcha();

		/* Если не были переданы данные о заполненных полях - возьмем их из POST*/
		$fill_value = count($fill_value) == 0?
			$_POST
			: Core_Type_Conversion::toArray($fill_value);

		$form_row = $this->GetFormInfo($form_id);

		if (!$form_row)
		{
			show_error_message('Ошибка! Формы с таким идентификатором не существует!');
			return FALSE;
		}

		$kernel = & singleton('kernel');

		if ($kernel->AllowShowPanel())
		{
			$param_panel = array();

			// Добавить Форму
			$param_panel[0]['image_path'] = "/hostcmsfiles/images/application_form_add.gif";
			$sPath = '/admin/form/index.php';
			$sAdditional = "hostcms[action]=edit&hostcms[checked][0][0]=1";
			$param_panel[0]['onclick'] = "$.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false";
			$param_panel[0]['href'] = "{$sPath}?{$sAdditional}";
			$param_panel[0]['alt'] = "Добавить форму";

			// Добавить поле формы
			$param_panel[1]['image_path'] = "/hostcmsfiles/images/textfield_add.gif";
			$sPath = '/admin/form/field/index.php';
			$sAdditional = "?hostcms[action]=edit&form_id=" . $form_id . "&hostcms[checked][0][0]=1";
			$param_panel[1]['onclick'] = "$.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false";
			$param_panel[1]['href'] = "{$sPath}?{$sAdditional}";
			$param_panel[1]['alt'] = "Добавить поле формы";

			// Редактировать Форму
			$param_panel[2]['image_path'] = "/hostcmsfiles/images/edit.gif";
			$sPath = '/admin/form/index.php';
			$sAdditional = "hostcms[action]=edit&hostcms[checked][0][" . $form_id . "]=1";
			$param_panel[2]['onclick'] = "$.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false";
			$param_panel[2]['href'] = "{$sPath}?{$sAdditional}";
			$param_panel[2]['alt'] = "Редактировать форму";

			// Копировать Форму
			$param_panel[3]['image_path'] = "/hostcmsfiles/images/copy.gif";
			$sPath = '/admin/form/index.php';
			$sAdditional = "hostcms[action]=copy&hostcms[checked][0][" . $form_id . "]=1";
			$param_panel[3]['onclick'] = "$.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false";
			$param_panel[3]['href'] = "{$sPath}?{$sAdditional}";
			$param_panel[3]['alt'] = "Копировать форму";

			// Выводим панель
			echo $kernel->ShowFlyPanel($param_panel);
		}

		$xmlData = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xmlData .= '<document>'."\n";

		// Вносим в XML дополнительные теги из массива дополнительных параметров
		$ExternalXml = new ExternalXml;
		$xmlData .= $ExternalXml->GenXml($external_propertys);

		$xmlData .= '<forms_id>'.$form_row['forms_id'].'</forms_id>'."\n";
		$xmlData .= '<forms_name>'.str_for_xml($form_row['forms_name']).'</forms_name>'."\n";
		$xmlData .= '<forms_email>'.str_for_xml($form_row['forms_email']).'</forms_email>'."\n";
		$xmlData .= '<forms_description>'.str_for_xml($form_row['forms_description']).'</forms_description>'."\n";
		$xmlData .= '<forms_button_type>'.$form_row['forms_button_type'].'</forms_button_type>'."\n";
		$xmlData .= '<forms_button_name>'.str_for_xml($form_row['forms_button_name']).'</forms_button_name>'."\n";
		$xmlData .= '<forms_button_text_value>'.str_for_xml($form_row['forms_button_text_value']).'</forms_button_text_value>'."\n";
		$xmlData .= '<forms_button_value>'.str_for_xml($form_row['forms_button_value']).'</forms_button_value>'."\n";
		$xmlData .= '<forms_captcha_used>'.Core_Type_Conversion::toInt($form_row['forms_captcha_used']).'</forms_captcha_used>'."\n";
		$xmlData .= '<forms_captcha_key>'.((Core_Type_Conversion::toInt($form_row['forms_captcha_used']) == 1)
		? $Captcha->GetCaptchaID()
		: 0).'</forms_captcha_key>'."\n";
		$xmlData .= '<fields>'."\n";

		// выбираем поля формы
		$result = $this->GetFormFields($form_id);

		while ($row = mysql_fetch_assoc($result))
		{
			$xmlData .= '<field>' . "\n";
			$xmlData .= '<name>' . str_for_xml($row['forms_fields_name']) . '</name>' . "\n";
			$xmlData .= '<field_text_name>' . str_for_xml($row['forms_fields_text_name']) . '</field_text_name>' . "\n";
			$xmlData .= '<field_default_value>' . str_for_xml($row['forms_fields_default_value']) . '</field_default_value>' . "\n";
			$xmlData .= '<type>' . Core_Type_Conversion::toInt($row['forms_fields_type']) . '</type>' . "\n";
			$xmlData .= '<size>' . Core_Type_Conversion::toInt($row['forms_fields_size']) . '</size>' . "\n";
			$xmlData .= '<cols>' . Core_Type_Conversion::toInt($row['forms_fields_cols']) . '</cols>' . "\n";
			$xmlData .= '<rows>' . Core_Type_Conversion::toInt($row['forms_fields_rows']) . '</rows>' . "\n";
			$xmlData .= '<checked>' . Core_Type_Conversion::toInt($row['forms_fields_checked']) . '</checked>' . "\n";
			$xmlData .= '<order>' . Core_Type_Conversion::toInt($row['forms_fields_order']) . '</order>' . "\n";
			$xmlData .= '<comment>' . str_for_xml($row['forms_fields_comment']) . '</comment>' . "\n";
			$xmlData .= '<obligatory>' . str_for_xml($row['forms_fields_obligatory']) . '</obligatory>' . "\n";
			$xmlData .= '<value>' . str_for_xml(
			isset($fill_value[$row['forms_fields_name']])
			? $fill_value[$row['forms_fields_name']]
			: (($row['forms_fields_type'] == 4)
				? 0
				: $row['forms_fields_default_value'])) . '</value>' . "\n";

			// тип поля - список или радиокнопки
			if (($row['forms_fields_type'] == 6
			|| $row['forms_fields_type'] == 3
			|| $row['forms_fields_type'] == 9)
			&& class_exists('lists'))
			{
				// формируем данные о списке
				$lists = & singleton('lists');

				//$xmlData .= $lists->GenXml4ListItems($row['lists_id']);
				// нельзя, т.к. формат другой !!!!!
				$list_row = $lists->SelectList($row['lists_id']);

				if ($list_row)
				{
					$xmlData .= '<list id="' . $list_row['lists_id'] . '" name="' . str_for_xml($list_row['lists_name']) . '">' . "\n";

					// формируем список
					$queryBuilder = Core_QueryBuilder::select(
							array('list_items.id', 'list_items_id'),
							array('lists.name', 'lists_name'),
							array('lists.description', 'lists_description'),
							array('list_items.value', 'lists_items_value'),
							array('list_items.description', 'lists_items_description'),
							array('list_items.sorting', 'lists_items_order')
						)
						->from('lists')
						->join('list_items', 'list_items.list_id', '=', 'lists.id')
						->where('lists.id', '=', $row['lists_id'])
						->where('list_items.active', '=', 1)
						->where('lists.deleted', '=', 0)
						->where('list_items.deleted', '=', 0)
						->orderBy('list_items.sorting')
						->orderBy('list_items.value');

					$aResult = $queryBuilder->execute()->asAssoc()->result();

					foreach($aResult as $row1)
					{
						$xmlData .= '<list_item>' . "\n";
						$xmlData .= '<list_item_id>' . $row1['list_items_id'] . '</list_item_id>' ."\n";
						$xmlData .= '<lists_name>' . str_for_xml($row1['lists_name']) . '</lists_name>' . "\n";
						$xmlData .= '<lists_description>' . str_for_xml($row1['lists_description']) . '</lists_description>' . "\n";
						$xmlData .= '<list_item_value>' . str_for_xml($row1['lists_items_value']) . '</list_item_value>' . "\n";
						$xmlData .= '<list_item_description>' . str_for_xml($row1['lists_items_description']) . '</list_item_description>' . "\n";
						$xmlData .= '<lists_items_order>' . str_for_xml($row1['lists_items_order']) . '</lists_items_order>' . "\n";
						$xmlData .= '</list_item>' . "\n";
					}

					$xmlData .= '</list>';
				}
			}

			$xmlData .= '</field>' . "\n";
		}

		$xmlData .= '</fields>' . "\n";
		$xmlData .= '</document>' . "\n";

		$xsl = & singleton('xsl');
		echo $xsl->build($xmlData,$xsl_name);

		return TRUE;
	}

	/**
	* Обработка содержимого отправленной формы
	*
	* @param int $form_id идентификатор отправленной формы
	* @param string $xsl_forms XSL-шаблон, формирующий сообщение пользователю при отправке формы
	* @param string $xsl_email XSL-шаблон, формирующий текст письма куратору отправленной формы с данными, переданными в форме
	* @param array $param массив дополнительных параметров, имеет следующую структуру
	* - $param['e-mail'] - поле формы, содержащее email адрес отправителя;
	* - $param['e-mail-to'] - строка со списком электронных адресов получателей.
	* При явном указании списка электронных адресов, адреса указанные в
	* атрибутах формы игнорируются;
	* - $param['subject'] - тема письма куратору формы;
	* - $param['type'] - тип письма (0 - text/html, 1 - text/plain);
	* - $param['bound'] - граница прикрепляемого файла. Если не передан, создается
	* автоматически.
	* @param array $external_propertys массив дополнительных свойств для включения в исходный XML код
	* @return boolean true в случае успешного отправления формы, false в противном случае
	*/
	function GetForm($form_id, $xsl_forms, $xsl_email, $param = array(), $external_propertys = array())
	{
		$form_id = intval($form_id);

		$Captcha = new Captcha();

		// определяем название главной кнопки формы
		$row = $this->GetFormInfo($form_id);

		$main_button_name = str_for_xml($row['forms_button_name']);
		$forms_captcha_used = Core_Type_Conversion::toInt($row['forms_captcha_used']);
		// проверяем нажали главную кнопку формы или нет

		// нажали кнопку - получаем данные из формы и сохраняем их
		if (isset($_POST[$main_button_name]) && ($forms_captcha_used == 0
		|| $forms_captcha_used == 1 && isset($_POST['captcha_key']) && isset($_POST['captcha_keystring'])
		&& $Captcha->ValidCaptcha($_POST['captcha_key'], $_POST['captcha_keystring'])))
		{
			// Массив содержащий пути прикрепленных файлов и их имена
			$mas_file_path_name = array();
			// Тема письма куратору формы
			$subject = $row['forms_mail_subject'];

			//Проверяем на соответствие заполнения обязательным полям
			// получаем данные о полях формы
			$result = $this->GetFormFields($form_id);
			// Цикл по всем полям формы

			$count_forms_fields = mysql_num_rows($result);

			for($i = 0; $i < $count_forms_fields; $i++)
			{
				$row_fields = mysql_fetch_assoc($result);
				// если поле является обязательным для заполнения, но его не заполнили, то
				// прерываем выполнение метода и возвращаем -1

				if ($row_fields['forms_fields_obligatory'] == 1
				// И тип не чекбокс
				&& $row_fields['forms_fields_type'] != 9
				// И тип не файл
				&& $row_fields['forms_fields_type'] != 2
				&& (!isset($_POST[$row_fields['forms_fields_name']])
				|| (isset($_POST[$row_fields['forms_fields_name']])
				&& trim($_POST[$row_fields['forms_fields_name']]) == '')))
				{
					return -1;
				}
			}

			$xmlData='<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$xmlData .= '<document>' . "\n";

			// Вносим в XML дополнительные теги из массива дополнительных параметров
			$ExternalXml = new ExternalXml;
			$xmlData.= $ExternalXml->GenXml($external_propertys);

			$xmlData .= '<forms_id>' . $row['forms_id'] . '</forms_id>' . "\n";
			$xmlData .= '<forms_path>' . str_for_xml(Core_Type_Conversion::toStr($_SERVER['HTTP_REFERER'])) .'></forms_path>' . "\n";
			$xmlData .= '<forms_name>' . str_for_xml($row['forms_name']) . '</forms_name>' . "\n";
			$xmlData .= '<forms_description>' . str_for_xml($row['forms_description']) . '</forms_description>' . "\n";
			$xmlData .= '<forms_email>' . str_for_xml($row['forms_email']) . '</forms_email>' . "\n";
			$xmlData .= '<forms_button_type>' . str_for_xml($row['forms_button_type']) . '</forms_button_type>' . "\n";
			$xmlData .= '<forms_button_name>' . str_for_xml($row['forms_button_name']) . '</forms_button_name>' . "\n";
			$xmlData .= '<forms_button_text_value>' . str_for_xml($row['forms_button_text_value']) . '</forms_button_text_value>' . "\n";
			$xmlData .= '<forms_button_value>' . str_for_xml($row['forms_button_value']) . '</forms_button_value>' . "\n";
			$xmlData .= '<forms_captcha_used>' . $forms_captcha_used . '</forms_captcha_used>' . "\n";

			$forms_fill_ip = Core_Type_Conversion::toStr($_SERVER['REMOTE_ADDR']);
			$forms_fill_date = date('Y-m-d H:i:s');
			$confirm_get_form = 0;

			// идентификатор заполненной формы
			$fill_form_id = 0;

			// проверяем может ли пользователь заполнять форму - достаточно прошло времени
			if ($this->ConfirmGetForm($form_id, $forms_fill_date, $forms_fill_ip) == 1)
			{
				$confirm_get_form = 1;

				// заполняем таблицу forms_fill_table - информация о заполнении формы
				$forms_fill_id = $this->InsertFormsFill(0, 0, $form_id, $forms_fill_ip, $forms_fill_date);

				// Идентификатор заполненной формы
				$xmlData .= '<form_fill_id>' . $forms_fill_id . '</form_fill_id>' . "\n";

				$result = $this->GetFormFields($form_id);

				// число полей формы
				$count_forms_fields = mysql_num_rows($result);

				if (class_exists("Lists"))
				{
					$Lists = new Lists();
				}

				for($i = 0; $i < $count_forms_fields; $i++)
				{
					$row = mysql_fetch_assoc($result);

					//если это список чекбоксов
					if($row['forms_fields_type'] == 9)
					{
						$mas_name_field_id_list = array();
						if (class_exists("Lists"))
						{
							$mas = $Lists->SelectListsItems($row['lists_id']);
							while ($res = mysql_fetch_assoc($mas))
							{
								$mas_name_field_id_list[] = $row['forms_fields_name'] . "_" . $res['lists_items_id'];
							}
						}

						foreach($mas_name_field_id_list as $value)
						{
							if(isset($_POST[$value]))
							{
								$val = $_POST[$value];
								$fill_form_id = $this->InsertFormsFillValues(0, 0, $forms_fill_id, $row['forms_fields_id'], $val);
							}
						}
					}
					else
					{
						// заполняем таблицу forms_fill_values_table
						if (isset($_POST[$row['forms_fields_name']]) || Core_Type_Conversion::toInt($_FILES[$row['forms_fields_name']]['size']) > 0)
						{
							if ($row['forms_fields_type'] != 2)
							{
								$value = $_POST[$row['forms_fields_name']];

								// Checkbox
								$row['forms_fields_type'] == 4 && $value = 1;
							}

							elseif (isset($_FILES[$row['forms_fields_name']]['tmp_name']))
							{
								$value = $_FILES[$row['forms_fields_name']]['name'];
							}

							$fill_form_id = $this->InsertFormsFillValues(0, 0, $forms_fill_id, $row['forms_fields_id'], $value);

							if ($row['forms_fields_type'] == 2 && isset($_FILES[$row['forms_fields_name']]['tmp_name']) && intval($_FILES[$row['forms_fields_name']]['size']) > 0)
							{
								$uploaddir = CMS_FOLDER . UPLOADDIR . 'private/' ;

								!is_dir($uploaddir) && Core_File::mkdir($uploaddir);

								copy($_FILES[$row['forms_fields_name']]['tmp_name'], $uploaddir . $fill_form_id);
							}
						}
					}
				}
			}
			$xmlData .= '<confirm_get_form>' . $confirm_get_form . '</confirm_get_form>' . "\n";
			$xmlData .= '</document>' . "\n";

			$xsl = & singleton('xsl');
			echo $xsl->build($xmlData, $xsl_forms);

			// прошло не достаточно времени, чтобы пользователь мог отправлять содержимое формы
			if ($confirm_get_form != 1)
			{
				return FALSE;
			}

			// Формирование xml с данными формы для письма куратору формы
			$queryBuilder = Core_QueryBuilder::select(
					array('email', 'forms_email'),
					array('forms.name', 'forms_name'),
					array('form_fields.type', 'forms_fields_type'),
					array('form_fields.list_id', 'lists_id'),
					array('form_fields.name', 'forms_fields_name'),
					array('caption', 'forms_fields_text_name'),
					array('form_fields.sorting', 'forms_fields_order'),
					array('form_fields.description', 'forms_fields_comment')
				)
				->from('forms')
				->join('form_fields', 'form_fields.form_id', '=', 'forms.id')
				->where('forms.id', '=', $form_id)
				->where('forms.deleted', '=', 0)
				->where('form_fields.deleted', '=', 0)
				->orderBy('form_fields.sorting');

			$result = $queryBuilder->execute()->getResult();

			$count_forms_fields = mysql_num_rows($result);

			// массив адресов получателей
			$mas_email = array();

			// текст письма
			$text = '';
			$row = mysql_fetch_assoc($result);

			// Если список адресов передан явно - используем его, иначе список адресов из атрибутов формы
			$email = !empty($param['e-mail-to'])
				? Core_Type_Conversion::toStr($param['e-mail-to'])
				: $row['forms_email'];

			/* Заменяем точки запятые на запятые*/
			$email = str_replace(';', ',', $email);

			// получаем список адресов ( если их несколько)
			$mas_email= explode(',', $email);

			// Тип письма не задан - отправляем вручную
			if (!isset($param['type']))
			{
				$text .= 'Уважаемый куратор формы, посетителем сайта заполнена форма "' . $row['forms_name'] . '"' . "\n\n";

				$text .= '--------------------------------------------------------------------------------------------------------------------------' . "\n";

				for ($i = 0; $i < $count_forms_fields; $i++)
				{
					// элемент - список checkbox
					if($row['forms_fields_type'] == 9)
					{
						$mas_name_field_id_list = array();

						if (class_exists("Lists"))
						{
							$mas = $Lists->SelectListsItems($row['lists_id']);
							while ($res = mysql_fetch_assoc($mas))
							{
								$mas_name_field_id_list[] = $row['forms_fields_name'] . "_" . $res['lists_items_id'];
							}
						}

						foreach($mas_name_field_id_list as $value)
						{
							if(isset($_POST[$value]))
							{
								$val = $_POST[$value];
								$text .= $row['forms_fields_text_name'] . ': ' . $val . "\n";
							}
						}
					}

					if (isset($_POST[$row['forms_fields_name']])
					/* или есть файл с ненулевым размером*/
					|| (isset($_FILES[$row['forms_fields_name']]['name'])
					&& Core_Type_Conversion::toInt($_FILES[$row['forms_fields_name']]['size'])!=0))
					{
						if ($row['forms_fields_type'] != 2) // тип поля - не файл
						{
							// элемент - checkbox
							if ($row['forms_fields_type'] == 4 && isset($_POST[$row['forms_fields_name']]))
							{
								$text .= $row['forms_fields_text_name'].': Да' . "\n";
							}

							else
							if ($row['forms_fields_type']==5)
							{
								$text .= $row['forms_fields_text_name'] . ':' . "\n";
								$text .= $_POST[$row['forms_fields_name']] . "\n";
							}

							else
							{
								if ($row['forms_fields_type']!=7)
								{
									$text.= $row['forms_fields_text_name'] . ': ' . $_POST[$row['forms_fields_name']] . "\n";
								}
							}
						}
						else //тип поля - файл
						{
							$count_mas_file_path_name = count($mas_file_path_name);
							$mas_file_path_name[$count_mas_file_path_name]['filepath'] = $_FILES[$row['forms_fields_name']]['tmp_name'];
							$mas_file_path_name[$count_mas_file_path_name]['filename'] = $_FILES[$row['forms_fields_name']]['name'];
						}
					}

					$row = mysql_fetch_assoc($result);
				}
				$text .= 'IP - адрес отправителя: ' . $forms_fill_ip . "\n";

				$text .= 'Дата отправки: ' . Core_Date::sql2datetime($forms_fill_date);
				$text .=  "\n--------------------------------------------------------------------------------------------------------------------------\n";
				$text.= "\n\nСистема управления сайтом HostCMS";
			}
			// Отправляем с помощью внешнего шаблона
			else
			{
				$xmlData='<?xml version="1.0" encoding="UTF-8"?>'."\n";
				$xmlData .= '<document>'."\n";

				// Вносим в XML дополнительные теги из массива дополнительных параметров
				$ExternalXml = new ExternalXml;
				$xmlData.= $ExternalXml->GenXml($external_propertys);

				$rez_form_fill = $this->GetFillForms($form_id);
				$row_form_fill = mysql_fetch_assoc($rez_form_fill);

				if ($row_form_fill)
				{
					$xmlData .= '<form_id>'.$row_form_fill['forms_fill_id'].'</form_id>'."\n";
				}
				else
				{
					$xmlData .= '<form_id>'.$fill_form_id.'</form_id>'."\n";
				}
				$xmlData .= '<forms_path>'.str_for_xml(Core_Type_Conversion::toStr($_SERVER['HTTP_REFERER'])).'</forms_path>'."\n";
				$xmlData .= '<form_name>' . str_for_xml($row['forms_name']) . '</form_name>' . "\n";
				$xmlData .= '<form_email>' . str_for_xml($row['forms_email']) . '</form_email>' . "\n";
				$xmlData .= '<form_ip>' . str_for_xml($forms_fill_ip) . '</form_ip>' . "\n";
				$xmlData .= '<form_date>' . str_for_xml(Core_Date::sql2date($forms_fill_date)) . '</form_date>' . "\n";
				$xmlData .= '<form_datetime>' . str_for_xml(Core_Date::sql2datetime($forms_fill_date)) . '</form_datetime>' . "\n";
				$xmlData .= '<form_fields>' . "\n";

				// Нужен для работы с файлами
				$site = & singleton ('site');

				for ($i = 0; $i < $count_forms_fields; $i++)
				{
					if($row['forms_fields_type'] == 9)
					{
						$xmlData .= '<form_field>' . "\n";
						$xmlData .= '<form_field_order>' . Core_Type_Conversion::toInt($row['forms_fields_order']) . '</form_field_order>' . "\n";
						$xmlData .= '<form_field_name>' . str_for_xml($row['forms_fields_name']) . '</form_field_name>' . "\n";
						$xmlData .= '<form_field_text_name>' . str_for_xml($row['forms_fields_text_name']) . '</form_field_text_name>' . "\n";
						$xmlData .= '<comment>' . str_for_xml($row['forms_fields_comment']) . '</comment>' . "\n";

						// элемент - список checkbox
						$mas_name_field_id_list = array();
						if (class_exists("Lists"))
						{
							$mas = $Lists->SelectListsItems($row['lists_id']);
							while ($res = mysql_fetch_assoc($mas))
							{
								$mas_name_field_id_list[] = $row['forms_fields_name'] . "_" . $res['lists_items_id'];
							}
						}

						foreach($mas_name_field_id_list as $value)
						{
							if (isset($_POST[$value]))
							{
								$xmlData .= '<form_field_value>'.str_for_xml($_POST[$value]).'</form_field_value>'."\n";
							}
						}

						$xmlData .= '</form_field>'."\n";
					}

					if ($row['forms_fields_type'] != 9
					&& (isset($_POST[$row['forms_fields_name']])
					/* или есть файл с ненулевым размером*/
					|| (isset($_FILES[$row['forms_fields_name']]['name']) && Core_Type_Conversion::toInt($_FILES[$row['forms_fields_name']]['size']) != 0)))
					{

						$xmlData .= '<form_field>'."\n";
						$xmlData .= '<form_field_order>'.Core_Type_Conversion::toInt($row['forms_fields_order']).'</form_field_order>'."\n";
						$xmlData .= '<form_field_name>'.str_for_xml($row['forms_fields_name']).'</form_field_name>'."\n";
						$xmlData .= '<form_field_text_name>'.str_for_xml($row['forms_fields_text_name']).'</form_field_text_name>'."\n";
						$xmlData .= '<comment>'.str_for_xml($row['forms_fields_comment']).'</comment>'."\n";

						// тип поля - не файл
						if ($row['forms_fields_type'] != 2 && $row['forms_fields_type'] != 9)
						{
							if (isset($_POST[$row['forms_fields_name']]))
							{
								// тип письма - plain/text
								if (isset($param['type']) && $param['type'] == 1)
								{
									$value = Core_Type_Conversion::toStr($_POST[$row['forms_fields_name']]);
								}
								else // тип письма - html/text
								{
									$value = nl2br(Core_Type_Conversion::toStr($_POST[$row['forms_fields_name']]));
								}
							}

							$xmlData .= '<form_field_value>'.str_for_xml($value).'</form_field_value>'."\n";
						}
						else // тип поля - файл
						{
							if (isset($_FILES[$row['forms_fields_name']]))
							{
								$count_mas_file_path_name = count($mas_file_path_name);
								$mas_file_path_name[$count_mas_file_path_name]['filepath'] = $_FILES[$row['forms_fields_name']]['tmp_name'];
								$mas_file_path_name[$count_mas_file_path_name]['filename'] = $_FILES[$row['forms_fields_name']]['name'];
								$value = $_FILES[$row['forms_fields_name']]['name'];
								$xmlData .= '<form_field_value>'.str_for_xml($value).'</form_field_value>'."\n";
							}

							/* определяем название алиаса сайта*/
							/*$site_alias=$site->GetCurrentAlias(CURRENT_SITE);

							$value='<a href= "http://'.$site_alias.'/admin/Forms/Forms.php?get_upload_file='.$download_file_id[$row['forms_fields_id']].'">'.$_FILES[$row['forms_fields_name']]['name'].'</a>';
							*/
						}

						$xmlData .= '</form_field>'."\n";
					}

					$row = mysql_fetch_assoc($result);
				}

				$xmlData .= '</form_fields>' . "\n";
				$xmlData .= '</document>' . "\n";

				$text = $xsl->build($xmlData, $xsl_email);
			}

			if (isset($param['subject']))
			{
				$subject = $param['subject'];
			}

			$rez_form_fill = $this->GetFillForms($form_id);
			$row_form_fill = mysql_fetch_assoc($rez_form_fill);

			if ($row_form_fill)
			{
				$subject = str_replace('{forms_fill_id}', $row_form_fill['forms_fill_id'], $subject);

				$subject = str_replace('{forms_fill_date}', strftime(DATE_FORMAT, Core_Date::sql2timestamp($row_form_fill['forms_fill_date'])), $subject);
				$subject = str_replace('{forms_fill_datetime}', strftime(DATE_TIME_FORMAT, Core_Date::sql2timestamp($row_form_fill['forms_fill_date'])), $subject);
			}

			$emailfrom = isset($param['e-mail']) && valid_email($param['e-mail'])
				? $param['e-mail']
				: EMAIL_TO;

			// тип письма - text/plain
			if (isset($param['type']) && $param['type'] == 1)
			{
				$type_text = ' text/plain';

				// При текстовой отправке нужно преобразовать HTML-сущности в символы
				$text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
			}
			else // тип письма - text/html
			{
				$type_text = ' text/html';
			}

			// в цикле отправляем письма всем кураторам формы
			$count_mas_email = count($mas_email);

			$kernel = & singleton('kernel');

			for($i = 0; $i < $count_mas_email; $i++)
			{
				/* Отправляем письма только в непустые адреса*/
				if (trim($mas_email[$i]) != '')
				{
					if (!isset($param['header']))
					{
						$param['header'] = array('X-HostCMS-Reason' => 'Form');
					}

					$kernel->SendMailWithFile(trim($mas_email[$i]), $emailfrom, $subject,
					$text, $mas_file_path_name, $type_text, $param);

					sleep(1);
				}
			}

			return Core_Type_Conversion::toInt($forms_fill_id);
		}
		else
		{
			// Если код подтверждения не верен
			return 0;
		}
	}

	/**
	* Определение права пользователя отправлять форму
	*
	* @param int $forms_id идентификатор отправляемой формы
	* @param string $date дата и время (в формате MySQL) отправки формы
	* @param string $forms_fill_ip ip-адрес компьютера отправителя формы
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $form_id = 6;
	* $date = date('Y-m-d H:i:s');
	* $forms_fill_ip = '';
	*
	* $result = $Form->ConfirmGetForm($forms_id, $date, $forms_fill_ip);
	*
	* // Распечатаем результат
	* if ($result)
	* {
	* 	echo 'Пользователь имеет право отправлять данные';
	* }
	* else
	* {
	* 	echo 'Пользователь не имеет право отправлять данные';
	* }
	* ?>
	* </code>
	* @return int 1 – пользователь может отправлять данные формы, 0 – пользователь не может отправлять данные формы
	*/
	function ConfirmGetForm($forms_id, $date, $forms_fill_ip)
	{
		$forms_id = intval($forms_id);

		$queryBuilder = Core_QueryBuilder::select()
			->from('form_fills')
			->join('forms', 'forms.id', '=', 'form_fills.form_id')
			->where('form_id', '=', $forms_id)
			->where('ip', '=', $forms_fill_ip)
			->where('form_fills.deleted', '=', 0)
			->where('forms.deleted', '=', 0)
			->orderBy('datetime', 'DESC')
			->limit(1);

		$aResult = $queryBuilder->execute()->asAssoc()->current();

		// Заполнял форму
		if ($aResult)
		{
			//Дата и время последнего заполнения формы пользователем
			$date_last_fill_form = $aResult['datetime'];

			//Определяем дату следующего возможного заполнения формы
			$date_next_fill_form = Core_Date::sql2timestamp($date_last_fill_form);

			// Метка времени даты следующего добавления сообщения
			$date_next_fill_form = $date_next_fill_form + ADD_COMMENT_DELAY;
			$date = Core_Date::sql2timestamp($date);

			// Время заполнения формы меньше допустимого для следующего заполнения
			if ($date < $date_next_fill_form)
			{
				// Пользователь не может заполнять форму
				return 0;
			}
		}

		// Пользователь не заполнял форму - имеет право для заполнения
		return 1;
	}

	/**
	* Копирование поля формы
	*
	* @param int $forms_field_id идентификатор поля формы
	* @param array $param ассоциативный массив параметров
	* - $param['show_text_copy'] параметр, определяющий будет добавляться текcт "Копия [датавремя создания копии]" к названию копии поля формы (true - добавлять текст, false - не добавлять), по умолчанию $param['show_text_copy'] = true
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_field_id = 6;
	*
	* $newid = $Form->CopyFormsField($forms_field_id);
	*
	* // Распечатаем результат
	* echo $newid;
	* ?>
	* </code>
	* @return mixed идентификатор нового (скопированного) поля формы, false в противном случае
	*/
	function CopyFormsField($forms_field_id, $param = array())
	{
		$forms_field_id = intval($forms_field_id);
		$oNewForm_Field = Core_Entity::factory('Form_Field', $forms_field_id)->copy();
		return $oNewForm_Field->id;
	}

	/**
	* Копирование структуры формы и ее полей
	*
	* @param int $forms_id идентификатор формы, структуру которой необходимо скопировать
	* @param int $site_id идентификатор сайта, куда следует скопировать форму, если не передан, используется текущий сайт
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_id = 6;
	*
	* $newid = $Form->CopyForms($forms_id);
	*
	* // Распечатаем результат
	* echo $newid;
	* ?>
	* </code>
	* @return mixed идентификатор новой формы в случае успешного выполнения, false - в противном случае
	*/
	function CopyForms($forms_id, $site_id = FALSE)
	{
		$forms_id = intval($forms_id);
		$oForm = Core_Entity::factory('Form', $forms_id)->copy();
		if ($site_id !== FALSE)
		{
			$oForm->site = $site_id;
		}
		$oForm->save();
		return $oForm->id;
	}

	/**
	* Получение информации о форме
	*
	* @param int $form_id идентификатор формы
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $form_id = 6;
	*
	* $row = $Form->GetFormInfo($form_id);
	*
	* // Распечатаем результат
	* print_r ($row);
	* ?>
	* </code>
	* @return mixed ассоциативный массив с данными о форме в случае успешного выполнения, false - в противном случае
	*
	*/
	function GetFormInfo($form_id)
	{
		$form_id = intval($form_id);
		$oForm = Core_Entity::factory('Form')->find($form_id);
		if (!is_null($oForm->id))
		{
			return $this->getArrayForm($oForm);
		}
		return FALSE;
	}

	/**
	* Получение информации о всех формах
	*
	* @param mixed $site_id идентификатор сайта, для которого необходимо получить список форм
	* - (по умолчанию получаем список форм текущего сайта, false - информация о формах всех сайтов)
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $site_id = 1;
	*
	* $resource = $Form->GetAllForms($site_id);
	*
	* // Распечатаем результат
	* while($row = mysql_fetch_assoc($resource))
	* {
	* 	print_r($row);
	* }
	* ?>
	* </code>
	* @return resource или false
	*/
	function GetAllForms($site_id = CURRENT_SITE)
	{
		$queryBuilder = Core_QueryBuilder::select(
				array('id', 'forms_id'),
				array('name', 'forms_name'),
				array('email', 'forms_email'),
				array('description', 'forms_description'),
				array('button_name', 'forms_button_name'),
				array('button_value', 'forms_button_value'),
				array('use_captcha', 'forms_captcha_used'),
				array('user_id', 'users_id'),
				array('email_subject', 'forms_mail_subject'),
				'site_id'
			)
			->from('forms')
			->where('deleted', '=', 0);

		$site_id && $queryBuilder->where('site_id', '=', $site_id);

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение списка заполненных форм для формы $forms_id.
	*
	* @param int $forms_id - идентификатор формы, если false - все заполненные формы всех форм
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_id = false;
	*
	* $resource = $Form->GetFillForms($forms_id);
	*
	* // Распечатаем результат
	* while($row = mysql_fetch_assoc($resource))
	* {
	* 	print_r($row);
	* }
	* ?>
	* </code>
	* @return resource
	*/
	function GetFillForms($forms_id = FALSE)
	{
		$queryBuilder = Core_QueryBuilder::select(
				array('id', 'forms_fill_id'),
				array('form_id', 'forms_id'),
				array('ip', 'forms_fill_ip'),
				array('datetime', 'forms_fill_date'),
				array('read', 'forms_fill_read')
			)
			->from('form_fills')
			->where('deleted', '=', 0)
			->orderBy('forms_fill_id', 'DESC');

		$forms_id && $queryBuilder->where('form_id', '=', intval($forms_id));

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение списка и значения полей для заполненной формы.
	*
	* @param int $fill_forms_id
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $fill_forms_id = 1;
	*
	* $resource = $Form->GetFillFormsFields($fill_forms_id);
	*
	* // Распечатаем результат
	* while($row = mysql_fetch_assoc($resource))
	* {
	* 	print_r($row);
	* }
	* ?>
	* </code>
	* @return resource
	*/
	function GetFillFormsFields($fill_forms_id)
	{
		$fill_forms_id = intval($fill_forms_id);

		$queryBuilder = Core_QueryBuilder::select(
				array('form_fields.id', 'forms_fields_id'),
				array('form_id', 'forms_id'),
				array('list_id', 'lists_id'),
				array('type', 'forms_fields_type'),
				array('size', 'forms_fields_size'),
				array('rows', 'forms_fields_rows'),
				array('cols', 'forms_fields_cols'),
				array('checked', 'forms_fields_checked'),
				array('name', 'forms_fields_name'),
				array('caption', 'forms_fields_text_name'),
				array('default_value', 'forms_fields_default_value'),
				array('sorting', 'forms_fields_order'),
				array('description', 'forms_fields_comment'),
				array('obligatory', 'forms_fields_obligatory'),
				array('user_id', 'users_id'),
				array('form_fill_fields.id', 'forms_fill_values_id'),
				array('form_fill_id', 'forms_fill_id'),
				array('form_field_id', 'forms_fields_id'),
				array('value', 'forms_fill_values_value')
			)
			->from('form_fields')
			->join('form_fill_fields', 'form_fill_fields.form_field_id', '=', 'form_fields.id')
			->where('form_fields.deleted', '=', 0)
			->orderBy('form_fields.sorting');

		$fill_forms_id && $queryBuilder->where('form_fill_fields.form_fill_id', '=', $fill_forms_id);

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение списка и значения полей для заполненной формы.
	*
	* @param int $fill_forms_id
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $fill_forms_id = 1;
	*
	* $row = $Form->GetFillForm($fill_forms_id);
	*
	* // Распечатаем результат
	* print_r ($row);
	* ?>
	* </code>
	* @return array массив с информацией о заполненной форме
	*/
	function GetFillForm($fill_forms_id)
	{
		$fill_forms_id = intval($fill_forms_id);
		$oForm_Fill = Core_Entity::factory('Form_Fill')->find($fill_forms_id);

		if (!is_null($oForm_Fill->id))
		{
			return $this->getArrayFormFill($oForm_Fill);
		}

		return FALSE;
	}

	/**
	* Получение количества заполнений формы.
	*
	* @param int $forms_id
	* <code>
	* <?php
	* $Form = new Forms();
	*
	* $forms_id = 0;
	*
	* $resource = $Form->GetCountFormsFill($forms_id);
	*
	* // Распечатаем результат
	* print_r ($resource);
	* ?>
	* </code>
	* @return resource
	*/
	function GetCountFormsFill($forms_id = 0)
	{
		$forms_id = intval($forms_id);
		$oForm_Fill = Core_Entity::factory('Form_Fill');

		$forms_id > 0 && $oForm_Fill->queryBuilder()->where('form_id', '=', $forms_id);

		$aForm_Fills = $oForm_Fill->findAll();
		return count($aForm_Fills);
	}
}

if (-1977579255 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
{
	die();
}