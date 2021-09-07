<?php

/**
 * Система управления сайтом HostCMS v. 5.xx
 *
 * Copyright © 2005-2011 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 *
 * Класс модуля "Резервное копирование", предназначен для создания резервной копии базы данных.
 *
 * Файл: /modules/admin_forms/Mysqldump.class.php
 *
 * @package HostCMS 5
 * @author Hostmake LLC
 * @version 5.x
 */
class mysqldump
{
	function mysqldump($host, $user, $password, $base)
	{
		throw new Core_Exception('Method mysqldump() does not allow');
	}

	function backup($filename, $table_number)
	{
		throw new Core_Exception('Method backup() does not allow');
	}
}

if (-1977579255 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
{
	die();
}