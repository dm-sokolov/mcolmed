<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Counter.
 *
 * @package HostCMS
 * @subpackage Counter
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */

class Counter_Entity extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'counter';
	
	//public $adminCode = NULL;
	/**
	 * Backend property
	 * @var mixed
	 */
	public $id = NULL;
	
	/**
	 * Backend property
	 * @var mixed
	 */
	public $param = NULL;
	
	/**
	 * Backend property
	 * @var mixed
	 */
	public $today = NULL;
	
	/**
	 * Backend property
	 * @var mixed
	 */
	public $yesterday = NULL;
	
	/**
	 * Backend property
	 * @var mixed
	 */
	public $seven_day = NULL;
	
	/**
	 * Backend property
	 * @var mixed
	 */
	public $thirty_day = NULL;
	
	/**
	 * Backend property
	 * @var mixed
	 */
	public $all_days = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $date = NULL;

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
	 * Load columns list
	 * @return self
	 */
	protected function _loadColumns()
	{
		return $this;
	}
	
	/**
	 * Get name of the primary key
	 * @return string
	 */
	public function getPrimaryKeyName()
	{
		return 'id';
	}
}