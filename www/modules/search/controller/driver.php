<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Search.
 *
 * @package HostCMS
 * @subpackage Search
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Search_Controller_Driver extends Core_Servant_Properties {
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'site',
		'offset',
		'page',
		'limit',
		'modules',
		'inner',
		'total',
		'orderField', // weight, datetime
		'orderDirection',
	);

	abstract public function truncate();

	abstract protected function getPageCount($site_id);

	abstract public function find($query);

	protected $_config = array();

	public function __construct($aConfig)
	{
		parent::__construct();

		$this->setConfig($aConfig);
		
		$this->orderField = 'weight';
		$this->orderDirection = 'DESC';
	}

	public function setConfig($aConfig)
	{
		$this->_config = $aConfig;
		return $this;
	}
	
	protected $_asObject = 'Search_Page_Model';

	public function asObject($objectName)
	{
		$this->_asObject = $objectName;
		return $this;
	}
}