<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cloud.
 *
 * @package HostCMS
 * @subpackage Cloud
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cloud_Dir extends Core_Entity
{
	public $id = NULL;
	public $hash = NULL;
	public $name = NULL;
	public $type = NULL;
	public $datetime = NULL;
	public $size = NULL;
	public $mode = NULL;
	public $user_id = 0;
	private $_oCloud_Controller = NULL;
	protected $_modelName = 'cloud_dir';

	public function cloudController(Cloud_Controller $oCloud_Controller=NULL)
	{
		if (is_null($oCloud_Controller))
		{
			return $this->_oCloud_Controller;
		}
		else
		{
			$this->_oCloud_Controller = $oCloud_Controller;
			return $this;
		}
	}

	/**
	 * Get table columns
	 * @return array
	 */
	public function getTableColumns()
	{
		return array_flip(
			array('id', 'hash', 'name', 'type', 'datetime', 'size', 'mode', 'user_id')
		);
	}

	/**
	 * Get file image
	 */
	public function image()
	{
		Core::factory('Core_Html_Entity_I')->class('fa fa-folder-o')->execute();
	}

	/**
	 * Delete dir
	 */
	public function delete($primaryKey = NULL)
	{
		$this->_oCloud_Controller->delete($this);
		return $this;
	}
}