<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Kad_Module
 * @author KAD artem.kuts@gmail.com
 */

class Kad_Module_Controller
{
	private $_module_id;

	public function __construct($module_id)
	{
		$this->_module_id = $module_id;
	}

	// Получить параметр
	public function get($name)
	{
		$name = $this->_name($name);
		$oSet = Core_Entity::factory('kad_module_setting')->getByName($name, false);
		if ($oSet)
		{
			return $oSet->value;
		} 
		
		return NULL;
	}	
	
	// Установить параметр
	public function set($name, $value)
	{
		$name = $this->_name($name);
		$oSet = Core_Entity::factory('kad_module_setting')->getByName($name);
		if (!$oSet)
		{
			$oSet = Core_Entity::factory('kad_module_setting');
			$oSet->name = $name;
		}
		
		$oSet->value = $value;
		$oSet->save();
		
		return $this;
	}	

	private function _name($name)
	{		
		if ($this->_module_id)
		{
			return $this->_module_id . "_" . $name;;
		} else
		{
			throw new Core_Exception('Module id required (method "public function module_id($module_id)")');
		}
	}
	
	public function module_id($module_id)
	{
		$this->_module_id = $module_id;	

		return $this;
	}
	
	static function install()
	{
		// Импорт таблиц модуля
		$query = "			
	CREATE TABLE IF NOT EXISTS `kad_module_settings` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `value` text NOT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

			
			CREATE TABLE IF NOT EXISTS `kad_module_keys` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `key` varchar(254) NOT NULL,
			  `site_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
		";
		
		// Выполняем запрос
		Sql_Controller::instance()->execute($query);
	}
	
	static function uninstall()
	{
		
	}
}
