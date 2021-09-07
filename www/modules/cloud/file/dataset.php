<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cloud File Dataset.
 *
 * @package HostCMS
 * @subpackage Cloud
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cloud_File_Dataset extends Admin_Form_Dataset
{
	private $_cloudId = NULL;
	private $_count = NULL;

	/**
	 * Constructor.
	 * @param int $type type
	 */
	public function __construct($iCloudID, $sDirId)
	{
		$this->_cloudId = $iCloudID;
		$this->_dirId = $sDirId;
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		if (is_null($this->_count))
		{
			$this->_fillObjectsArray();

			$this->_count = count($this->_objects);
		}

		return $this->_count;
	}

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		!is_array($this->_objects) && $this->_fillObjectsArray();
		
		return array_slice($this->_objects, $this->_offset, $this->_limit);
	}

	/**
	 * Get typical entity
	 * @return object
	 */
	public function getEntity()
	{
		$oCloud_File = new Cloud_File();
		$oCloud_File->hash = base64_encode('EMPTY HASH');
		$oCloud_File->name = base64_encode('EMPTY NAME');
		return $oCloud_File;
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		!is_array($this->_objects) && $this->_fillObjectsArray();

		if (isset($this->_objects[$primaryKey]))
		{
			return $this->_objects[$primaryKey];
		}
		elseif ($primaryKey == 0)
		{
			return $this->getEntity();
		}

		return NULL;
	}

	/**
	 * Get new object
	 * @return object
	 */
	protected function _newObject(){}

	protected function _fillObjectsArray()
	{
		$this->_objects = array();

		// Default sorting field
		$sortField = 'name';
		$sortDirection = 'ASC';

		foreach ($this->_conditions as $condition)
		{
			foreach ($condition as $operator => $args)
			{
				if ($operator == 'orderBy')
				{
					$sortField = $args[0];
					$sortDirection = strtoupper($args[1]);
				}
			}
		}

		$oCloud_Controller = Cloud_Controller::factory($this->_cloudId);

		if ($oCloud_Controller)
		{
			!is_null($this->_dirId) && $oCloud_Controller->dirId($this->_dirId);

			$aResponse = $oCloud_Controller->listDir();

			foreach ($aResponse as $oResponse)
			{
				if (!$oResponse->is_dir)
				{
					$oCloud_File = new Cloud_File();
					$oCloud_File->setSortField($sortField);
					$oCloud_File->id = $oResponse->id;
					$oCloud_File->hash = rawurlencode(base64_encode($oResponse->path));
					$oCloud_File->name = $oResponse->name;
					$oCloud_File->size = number_format($oResponse->bytes, 0, '.', ' ');
					$oCloud_File->datetime = $oResponse->datetime;
					$oCloud_File->cloudController($oCloud_Controller);

					$this->_objects[$oCloud_File->hash] = $oCloud_File;
				}
			}

			uasort($this->_objects, array($this, $sortDirection == 'ASC' ? '_sortAsc' : '_sortDesc'));
		}
	}
}