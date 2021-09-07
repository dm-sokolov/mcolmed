<?php

 defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Kad_Module_Key_Model.
 * @author KAD artem.kuts@gmail.com
 */
 
 class Kad_Module_Key_Model extends Core_Entity
 {

	protected $_marksDeleted = NULL;
	
	//связи
	
	/**
	 * Constructor.
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);
	}	
 }