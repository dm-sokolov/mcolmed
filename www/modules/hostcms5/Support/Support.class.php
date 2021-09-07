<?php

/**
 * Система управления сайтом HostCMS v. 5.xx
 *
 * Copyright © 2005-2011 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 *
 * Класс модуля "Техническая поддержка".
 *
 * Файл: /modules/Support/Support.class.php
 *
 * @package HostCMS 5
 * @author Hostmake LLC
 * @version 5.x
 */
class Support
{
	/**
	* Метод отправки письма с просьбой о технической поддержке
	*
	* @param string $name ФИО пользователя
	* @param string $mail e-mail пользователя
	* @param string $phone телефон пользователя
	* @param string $number номер договора
	* @param string $pin_code пин-код
	* @param string $text текст сообщения
	* @param string $sections отдел, в который необходимо направить письмо
	* @param string $priority приоритет сообщения
	* @param string $subject тема сообщения
	* @param string $page тема сообщения
	* @param string $filename путь к прикрепляемому файлу
	* <code>
	* <?php
	* $Support = new Support();
	*
	* $name = 'ФИО пользователя';
	* $mail = 'admin@site.ru';
	* $phone = '';
	* $number = '';
	* $pin_code = '';
	* $text = 'Текст сообщения';
	* $sections = 'Отдел технической поддержки';
	* $priority = 'Приоритет сообщения';
	* $subject = 'Тема сообщения';
	* $page = '';
	*
	* $result = $Support->SupportMail($name, $mail, $phone, $number, $pin_code, $text, $sections, $priority, $subject, $page);
	*
	* if ($result)
	* {
	* 	echo "Отправка сообщения выполнена успешно";
	* }
	* else
	* {
	*	echo "Ошибка отправки сообщения";
	* }
	* ?>
	* </code>
	* @return boolean истина при удачной отправке письма, ложь - в обратном случае
	*/
	function SupportMail($name, $mail, $phone, $number, $pin_code, $text, $sections, $priority, $subject, $page='', $file_path_name = array())
	{
		// Массив сообщений
		$DataBase = & singleton('DataBase');

		// Выбираем алиасы текущего сайта
		$site_id = intval(CURRENT_SITE);
		$DataBase->select("SELECT * FROM site_alias_table WHERE `site_id` = '$site_id'");
		$result = $DataBase->result;
		$site_alias = '';
		for ($i = 1; $i <= $DataBase->get_count_row(); $i++)
		{
			$row = mysql_fetch_assoc($result);
			$site_alias .= $row["alias_name"]."\n";
		}

		// Определяем приоритет
		switch ($priority)
		{
			case 1:
				$prior = $GLOBALS['MSG_support']['support_form_priority_small'];
			break;
			case 2:
				$prior = $GLOBALS['MSG_support']['support_form_priority_middle'];
			break;
			case 3:
				$prior = $GLOBALS['MSG_support']['support_form_priority_high'];
			break;
			case 4:
				$prior = $GLOBALS['MSG_support']['support_form_priority_highest'];
			break;
		}

		$message = $GLOBALS['MSG_support']['support_class_subject']."$subject\n\n";
		$message .= $text."\n_____________________________________";

		$message .= "\n".$GLOBALS['MSG_support']['support_class_page'].$page;

		$message .= "\n_____________________________________";

		$message.="\n".$GLOBALS['MSG_support']['support_class_version'].strip_tags(CURRENT_VERSION);
		$message.="\n".$GLOBALS['MSG_support']['support_class_update'].strip_tags(HOSTCMS_UPDATE_NUMBER);
		// Определяем редакцию системы
		$redaction_name = '';
		switch (INTEGRATION)
		{
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

		$message.="\n".$GLOBALS['MSG_support']['support_class_redaction'].$redaction_name;
		$message.="\n_____________________________________";
		$message.="\n".$GLOBALS['MSG_support']['support_class_contact_information']."\n".$GLOBALS['MSG_support']['support_class_name'].$name."\n".$GLOBALS['MSG_support']['support_class_mail'].$mail."\n".$GLOBALS['MSG_support']['support_class_phone'].$phone."\n".$GLOBALS['MSG_support']['support_class_number'].$number."\n".$GLOBALS['MSG_support']['support_class_pin'].$pin_code."\n".$GLOBALS['MSG_support']['support_class_priority']."$prior\n";
		$message.="_____________________________________\n".$GLOBALS['MSG_support']['support_class_alias'].$site_alias."\n";
		switch ($sections)
		{
			case 1:
				$section=$GLOBALS['MSG_support']['support_form_section_technical'];
			break;
			case 2:
				$section=$GLOBALS['MSG_support']['support_form_section_all_questions'];
			break;
		}

		$kernel = & singleton('kernel');
		return $kernel->SendMailWithFile('support@hostcms.ru', $mail, 'HostCMS:' . $section . ':' . $subject, $message, $file_path_name, 'text/plain');
	}
}

if ((~827242327) & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
{
	die();
}