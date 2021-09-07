<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * File cache driver
 *
 * @package HostCMS
 * @subpackage Cache
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cache_File extends Core_Cache
{
	/**
	 * Cache path
	 * @var string
	 */
	protected $_path = NULL;

	/**
	 * Constructor.
	 * @param array $config Driver's configuration
	 */
	public function __construct($config)
	{
		parent::__construct($config);

		if (!isset($this->_config['caches']) || !is_array($this->_config['caches']))
		{
			throw new Core_Exception('File caches configuration section does not exist', array(), 0, FALSE);
		}

		// Sets default value
		foreach ($this->_config['caches'] as $key => $cache)
		{
			$this->_config['caches'][$key] += self::$aCaches;
		}

		$this->_path = CMS_FOLDER . 'hostcmsfiles'
			. DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

		clearstatcache();
	}

	/**
	 * check cache available
	 * @return boolean
	 */
	public function available()
	{
		return TRUE;
	}

	/**
	 * Get unique cache name key
	 * @param string $cacheName cache name
	 * @param string $key key name
	 * @return mixed
	 */
	protected function _getActualKey($cacheName, $key = NULL)
	{
		$return = basename($cacheName);

		if (!is_null($key))
		{
			$md5 = md5($key);
			$return .= DIRECTORY_SEPARATOR . $md5[0] . DIRECTORY_SEPARATOR . $md5 . '.ch';
		}

		return $return;
	}

	/**
	 * Check if data exists
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @return NULL|TRUE|FALSE
	 */
	public function check($key, $cacheName = 'default')
	{
		if ($this->_issetCacheConfig($cacheName) && $this->_config['caches'][$cacheName]['active'])
		{
			$cacheFileName = $this->_path . $this->_getActualKey($cacheName, $key);

			if (is_file($cacheFileName))
			{
				$filemtime = filemtime($cacheFileName);

				// Если файл еще не истек
				if (time() <= $filemtime + $this->_config['caches'][$cacheName]['expire'])
				{
					return TRUE;
				}
				else
				{
					$this->delete($key, $cacheName);
				}
			}
		}

		return FALSE;
	}

	/**
	 * Get a count of keys in cache $cacheName
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @param string $defaultValue default value if index does not exist
	 * @return integer
	 * @hostcms-event Core_Cache.onBeforeGet
	 * @hostcms-event Core_Cache.onAfterGet
	 */
	public function get($key, $cacheName = 'default', $defaultValue = NULL)
	{
		Core_Event::notify('Core_Cache.onBeforeGet', $this);

		$return = $defaultValue;

		if ($this->_issetCacheConfig($cacheName) && $this->_config['caches'][$cacheName]['active'])
		{
			$cacheFileName = $this->_path . $this->_getActualKey($cacheName, $key);

			if (is_file($cacheFileName))
			{
				$filemtime = filemtime($cacheFileName);

				// Если файл еще не истек
				if (time() <= $filemtime + $this->_config['caches'][$cacheName]['expire'])
				{
					try {
						//$oldErrorReporting = error_reporting(E_ERROR);
						$return = $this->_unPack(Core_File::read($cacheFileName));
						//error_reporting($oldErrorReporting);
					} catch (Exception $e) {}
				}
				else
				{
					$this->delete($key, $cacheName);
				}
			}
		}

		Core_Event::notify('Core_Cache.onAfterGet', $this);

		return $return;
	}

	/**
	 * Set data in cache
	 * @param string $key key name
	 * @param mixed $value value
	 * @param string $cacheName cache name
	 * @return self
	 * @hostcms-event Core_Cache.onBeforeSet
	 * @hostcms-event Core_Cache.onAfterSet
	 */
	public function set($key, $value, $cacheName = 'default', array $tags = array())
	{
		Core_Event::notify('Core_Cache.onBeforeSet', $this, array($key, $value, $cacheName));

		if (!$this->_issetCacheConfig($cacheName))
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write(Core::_('Cache.parameters_does_not_exist', $cacheName));
		}
		elseif ($this->_config['caches'][$cacheName]['active'] && defined('CHMOD'))
		{
			$valueToWrite = $this->_pack($value);

			// Check size
			if (strlen($valueToWrite) <= $this->_config['caches'][$cacheName]['size'])
			{
				$actualKey = $this->_getActualKey($cacheName, $key);
				$cacheFileName = $this->_path . $actualKey;

				$dirName = dirname($cacheFileName);
				!is_dir($dirName) && Core_File::mkdir($dirName, CHMOD, TRUE);
				Core_File::write($cacheFileName, $valueToWrite);

				$expire = $this->_config['caches'][$cacheName]['expire'];

				$this->_saveTags($cacheName, $actualKey, $tags, time() + $expire);
			}
		}

		Core_Event::notify('Core_Cache.onAfterSet', $this, array($key, $value, $cacheName));

		return $this;
	}

	/**
	 * Delete cache items by $oCache_Tag
	 * @param Cache_Tag_Model $oCache_Tag
	 * @return self
	 */
	protected function _deleteByTag(Cache_Tag_Model $oCache_Tag)
	{
		$aExplode = explode(DIRECTORY_SEPARATOR, $oCache_Tag->hash);

		if (count($aExplode) == 3)
		{
			$md5 = $aExplode[2];

			$cacheFileName = $this->_path . basename($aExplode[0])
				. DIRECTORY_SEPARATOR . $md5[0]
				. DIRECTORY_SEPARATOR . $md5;

			$this->_delete($cacheFileName);
		}

		return $this;
	}

	/**
	 * Delete key from cache
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @return self
	 */
	public function delete($key, $cacheName = 'default')
	{
		$actualKey = $this->_getActualKey($cacheName, $key);
		$cacheFileName = $this->_path . $actualKey;

		$this->_delete($cacheFileName);

		$this->_config['caches'][$cacheName]['tags'] && $this->deleteTags($actualKey);

		return $this;
	}

	/**
	 * Delete key from cache
	 * @param string $hash cache index
	 * @return self
	 */
	protected function _delete($hash)
	{
		if (is_file($hash))
		{
			try {
				Core_File::delete($hash);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete all keys from cache
	 * @param string $cacheName cache name
	 * @return self
	 */
	public function deleteAll($cacheName = 'default')
	{
		Core_Event::notify('Core_Cache.onBeforeDeleteAll', $this, array($cacheName));

		if (strlen($cacheName))
		{
			Core_File::deleteDir($this->_path . basename($cacheName));

			// Clear all tags for $cacheName
			$this->clearTags($cacheName);
		}

		Core_Event::notify('Core_Cache.onAfterDeleteAll', $this, array($cacheName));

		return $this;
	}

	/**
	 * Get a count of keys in cache $cacheName
	 * @param string $cacheName cache name
	 * @return integer
	 */
	public function getCount($cacheName = 'default')
	{
		$dirName = $this->_path . basename($cacheName);

		$iCount = 0;

		clearstatcache();

		if (is_dir($dirName) && !is_link($dirName))
		{
			$aDirs = array();

			// получаем дескриптор основного каталога
			if ($dh = @opendir($dirName))
			{
				while (($file = readdir($dh)) !== FALSE)
				{
					if ($file != '.' && $file != '..' && @filetype($dirName . DIRECTORY_SEPARATOR . $file) == 'dir')
					{
						$aDirs[] = $file;
					}
				}

				closedir($dh);
			}

			if (count($aDirs) > 0)
			{
				foreach ($aDirs as $sDir)
				{
					$subdir = $dirName . DIRECTORY_SEPARATOR . $sDir;
					if ($dh = @opendir($subdir))
					{
						while (($file = readdir($dh)) !== FALSE)
						{
							if ($file != '.' && $file != '..' && @filetype($subdir . DIRECTORY_SEPARATOR . $file) != 'dir')
							{
								$iCount++;
							}
						}

						closedir($dh);
					}
				}
			}
		}

		return $iCount;
	}
}