<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cloud Controller
 *
 * @package HostCMS
 * @subpackage Cloud
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Cloud_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'dirId',
		'dir',
		'percent',
		'chunkSize',
	);

	protected $_oCloud = NULL;
	protected $_token = NULL;
	protected $_config = array();
	protected $_timeout = array();
	abstract public function getOauthCodeUrl();
	abstract public function getAccessToken();
	abstract public function listDir();
	abstract public function download($sFileName, $sTargetDirectory, $aParams = array());
	abstract public function upload($sSourcePath, $sDestinationFileName = NULL, $aParams = array());
	abstract public function delete($oObjectData);
	abstract public function getBreadCrumbs();

	public function __construct(Cloud_Model $oCloud)
	{
		parent::__construct();

		$this->_oCloud = $oCloud;

		$this->dir = $oCloud->root_folder;
		$this->percent = $this->chunkSize = 0;

		$this->_timeout = (!defined('DENY_INI_SET') || !DENY_INI_SET)
			? ini_get('max_execution_time') - 5
			: 25;

		$this->_timeout <= 0 && $this->_timeout > 360
			&& $this->_timeout = 25;

		return $this;
	}

	static protected $_instance = array();

	static public function factory($iCloudId = 0)
	{
		if ($iCloudId == 0)
		{
			throw new Core_Exception("Can't create cloud provider class with empty client's id");
		}

		$iCloudId = intval($iCloudId);

		if (!array_key_exists($iCloudId, self::$_instance))
		{
			self::$_instance[$iCloudId] = NULL;

			$oCloud = Core_Entity::factory('Cloud')->find($iCloudId);

			if (is_null($oCloud))
			{
				throw new Core_Exception("Can't find cloud provider class with id = %id", array('%id' => $iCloudId));
			}

			$sCloudName = ucfirst($oCloud->type);

			if ($sCloudName != '')
			{
				$sProviderClassName = "Cloud_Handler_{$sCloudName}_Controller";

				class_exists($sProviderClassName)
					&& self::$_instance[$iCloudId] = new $sProviderClassName($oCloud);
			}
		}

		return self::$_instance[$iCloudId];
	}

	static public function getClouds()
	{
		$aConfig = Core_Config::instance()->get('cloud_config', array()) + array('drivers' => array());

		$aClouds = array();

		foreach ($aConfig['drivers'] as $cloudName => $cloudParams)
		{
			$aClouds[$cloudName] = Core_Array::get($cloudParams, 'name');
		}

		return $aClouds;
	}
}