<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cache.
 *
 * @package HostCMS
 * @subpackage Cache
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cache_Entity extends stdClass
{
	/**
	 * Table columns
	 * @var array
	 */
	protected $_tableColums = array();
	
	/**
	 * Set table columns
	 * @param array $tableColums columns
	 * @return self
	 */
	public function setTableColums($tableColums)
	{
		$this->_tableColums = $tableColums;
		return $this;
	}
	
	/**
	 * Get table columns
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->_tableColums;
	}
	
	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (strtolower(Core::$mainConfig['defaultCache']) == strtolower($this->name)
			|| Core_Entity::factory('Site', CURRENT_SITE)->html_cache_use && strtolower($this->name) == 'static'
		)
		{
			Core::factory('Core_Html_Entity_Span')
				->class('badge badge-palegreen badge-ico white')
				->add(Core::factory('Core_Html_Entity_I')->class('fa fa-check'))
				->execute();
		}
	}
}