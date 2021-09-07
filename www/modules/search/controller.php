<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Search.
 *
 * @package HostCMS
 * @subpackage Search
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Search_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name = 'default')
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$aConfig = Core::$config->get('search_config') + array(
				'default' => array(
					'driver' => 'hostcms'
				)
			);

			if (!isset($aConfig[$name]))
			{
				throw new Core_Exception("Search configuration '%driverName' doesn't defined.", array('%driverName' => $name));
			}

			$aConfigDriver = defined('CURRENT_SITE') && isset($aConfig[$name][CURRENT_SITE])
				? $aConfig[$name][CURRENT_SITE]
				: $aConfig[$name];

			if (!isset($aConfigDriver['driver']))
			{
				throw new Core_Exception("Driver configuration '%driverName' doesn't defined.", array('%driverName' => $name));
			}

			$driver = self::_getDriverName($aConfigDriver['driver']);

			self::$instance[$name] = new $driver(
				Core_Array::get($aConfig, $name, array())
			);
		}

		return self::$instance[$name];
	}

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

	/**
	 * Get hash from $text
	 * @param string $text source text
	 * @param array $param list of hash params
	 * @return array
	 */
	static public function getHashes($text, $param = array())
	{
		if (!isset($param['hash_function']))
		{
			$param['hash_function'] = 'md5';
		}

		// Max string length for explode
		$iMaxLen = 5120;

		$iTextLen = mb_strlen($text);

		$result = array();

		if ($iTextLen)
		{
			do {
				if ($iTextLen < $iMaxLen)
				{
					$iMaxLen = $iTextLen;
				}

				$iStrCut = mb_strpos($text, ' ', $iMaxLen);

				if ($iStrCut === FALSE)
				{
					$iStrCut = $iTextLen;
				}

				$subStr = mb_substr($text, 0, $iStrCut);
				$text = mb_substr($text, $iStrCut);

				$aText = Core_Str::getHashes($subStr, array('hash_function' => ''));

				$bUkrainian = preg_match('/[ґєї]/u', $subStr);

				// Нормализация и хеширование слов
				foreach ($aText as $key => $res)
				{
					$word = preg_match('/[а-яё]/u', $res)
						? ($bUkrainian
							? Search_Stemmer::instance('ua')->stem($res)
							: Search_Stemmer::instance('ru')->stem($res)
						)
						: Search_Stemmer::instance('en')->stem($res);

					switch ($param['hash_function'])
					{
						case '':
							$result[] = $word;
						break;
						default:
						case 'md5':
							$result[] = md5($word);
						break;
						case 'crc32':
							$result[] = Core::crc32($word);
						break;
					}
				}
			} while ($iTextLen = mb_strlen($text));
		}

		return $result;
	}

	/**
	 * Indexing search pages
	 * @param array $aSearchPages list of search pages
	 * @return boolean
	 */
	static public function indexingSearchPages(array $aSearchPages)
	{
		return self::instance()->indexingSearchPages($aSearchPages);
	}

	/**
	 * Delete search page
	 *
	 * @param int $module module's number, 0-15
	 * @param int $module_value_type value type, 0-15
	 * @param int $module_value_id entity id, 0-16777216
	 * @return self
	 */
	static public function deleteSearchPage($module, $module_value_type, $module_value_id)
	{
		return self::instance()->deleteSearchPage($module, $module_value_type, $module_value_id);
	}
}