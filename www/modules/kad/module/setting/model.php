<?php

 defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Kad_Module_Setting_Model.
 *
 * @package HostCMS 6
 * @version 6.x
 * @author KAD artem.kuts@gmail.com
 */
 
 class Kad_Module_Setting_Model extends Core_Entity
 {
 
	protected $_marksDeleted = NULL;
	
	/**
	 * Constructor.
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);
	}	
	
 }