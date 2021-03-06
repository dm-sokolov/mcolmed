<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Siteuser_Property_List_Model
 *
 * @package HostCMS
 * @subpackage Siteuser
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Siteuser_Property_List_Model extends Site_Model
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'sites';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'siteuser_property_list';

	/**
	 * Backend property
	 * @var boolean
	 */
	public $changeFilename = TRUE;

	/**
	 * Backend property
	 * @var string
	 */
	public $watermarkFilePath = '';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'property' => array(
			'through' => 'siteuser_property',
			'foreign_key' => 'site_id',
			'dependent_key' => 'property_id'
		),
		'property_dir' => array(
			'through' => 'siteuser_property_dir',
			'foreign_key' => 'site_id',
			'dependent_key' => 'property_dir_id'
		)
	);

	/**
	 * Get large image max width
	 * @return int
	 */
	public function getLargeImageMaxWidth()
	{
		return $this->max_size_load_image_big;
	}

	/**
	 * Get large image max height
	 * @return int
	 */
	public function getLargeImageMaxHeight()
	{
		return $this->max_size_load_image_big;
	}

	/**
	 * Get small image max width
	 * @return int
	 */
	public function getSmallImageMaxWidth()
	{
		return $this->max_size_load_image;
	}

	/**
	 * Get small image max height
	 * @return int
	 */
	public function getSmallImageMaxHeight()
	{
		return $this->max_size_load_image;
	}

	/**
	 * Get object directory href
	 * @param Core_Entity $object
	 * @return string
	 */
	public function getDirHref(Core_Entity $object)
	{
		return '/' . $object->getDirHref();
	}

	/**
	 * Get object directory path
	 * @param Core_Entity $object
	 * @return string
	 */
	public function getDirPath(Core_Entity $object)
	{
		return $object->getDirPath();
	}

	/**
	 * Create object directory
	 * @param Core_Entity $object
	 * @return self
	 */
	public function createPropertyDir(Core_Entity $object)
	{
		$path = $this->getDirPath($object);
		if (!is_dir($path))
		{
			try
			{
				Core_File::mkdir($path, CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Get property large image file name
	 * @param Core_Entity $object entity
	 * @param Property_Value_Model $oFileValue entity of property_value
	 * @param string $originalFileName original file name
	 * @return string
	 */
	public function getLargeFileName(Core_Entity $object, $oFileValue, $originalFileName)
	{
		return 'property_' . $oFileValue->id . '.' . Core_File::getExtension($originalFileName);
	}

	/**
	 * Get property small image file name
	 * @param Core_Entity $object entity
	 * @param Property_Value_Model $oFileValue entity of property_value
	 * @param string $originalFileName original file name
	 * @return string
	 */
	public function getSmallFileName(Core_Entity $object, $oFileValue, $originalFileName)
	{
		return 'small_property_' . $oFileValue->id . '.' . Core_File::getExtension($originalFileName);
	}
}