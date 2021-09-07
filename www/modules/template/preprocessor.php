<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template Preprocessors
 *
 * @package HostCMS 6
 * @subpackage Template
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Template_Preprocessor
{
	protected $_preprocessor = NULL;
	
	public function getPreprocessor()
	{
		return $this->_preprocessor;
	}
	
	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return 'Template_Preprocessor_' . ucfirst($driver);
	}

	/**
	 * Create and return an object of preprocessor
	 * @param string $driveName
	 */
	static public function factory($driverName)
	{
		$driver = self::_getDriverName($driverName);
		return new $driver();
	}

	/**
	 * Compile
	 * @param string $content
	 * @return string
	 */
	abstract public function compile($content);
}