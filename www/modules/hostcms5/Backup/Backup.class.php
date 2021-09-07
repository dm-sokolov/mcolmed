<?php
/**
 * Система управления сайтом HostCMS v. 5.xx
 *
 * Copyright © 2005-2011 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 *
 * Класс модуля "Резервное копирование".
 *
 * Файл: /modules/Backup/Backup.class.php
 *
 * @package HostCMS 5
 * @author Hostmake LLC
 * @version 5.x
 */
class Backup
{
	/**
	 * Создание резевной копии базы данных
	 *
	 * @param string $backup_name полный путь к файлу для резервной копии
	 * @param string $userName имя пользователя БД
	 * @param string $password пароль пользователя БД
	 * @param string $host host БД
	 * @param string $db_name имя базы данных
	 */
	function CreateBackup($backup_name, $userName, $password, $host, $db_name, $table_number)
	{
		throw new Core_Exception('Method CreateBackup() does not allow');
	}
}

if (-1977579255 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
{
	die();
}