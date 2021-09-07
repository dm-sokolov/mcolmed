<?php
/**
 * Система управления сайтом HostCMS v. 5.xx
 *
 * Copyright © 2005-2011 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 *
 * Модуль Support.
 *
 * Файл: /modules/Support/Support.php
 *
 * @package HostCMS 5
 * @author Hostmake LLC
 * @version 5.x
 */

if ((~827242327) & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
{
	die();
}

$module_path_name = 'Support';
$module_name = 'Техническая поддержка';

$kernel = & singleton('kernel');

/* Список файлов для загрузки */
$kernel->AddModuleFile($module_path_name, CMS_FOLDER . "modules/hostcms5/{$module_path_name}/{$module_path_name}.class.php");

// Добавляем версию модуля
$kernel->add_modules_version($module_path_name, '5.9', '28.04.2012');