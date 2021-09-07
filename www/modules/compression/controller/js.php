<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Compression.
 *
 * @package HostCMS
 * @subpackage Compression
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Compression_Controller_Js extends Compression_Controller
{
	protected $_aJs = array();

	public function addJs($path)
	{
		$this->_aJs[] = $path;
		return $this;
	}

	/**
	 * Get path of the cache dir for the JS with CMS_FOLDER
	 * @return string
	 */
	public function getJsDirPath()
	{
		return CMS_FOLDER . Core::$mainConfig['compressionJsDirectory'];
	}

	/**
	 * Get href of the cache dir for the JS
	 * @return string
	 */
	public function getJsDirHref()
	{
		return '/' . Core::$mainConfig['compressionJsDirectory'];
	}

	public function getPath()
	{
		$sFileName = md5(implode(',', $this->_aJs)) . '.js';

		$sJsDir = $this->getJsDirPath();

		clearstatcache();

		if (!is_file($sJsDir . $sFileName))
		{
			$sContent = '';
			foreach ($this->_aJs as $js)
			{
				$sPath = Core_File::pathCorrection(CMS_FOLDER . ltrim($js, '/\\'));

				if (is_file($sPath))
				{
					$sContent .= $this->compress(
						Core_File::read($sPath), $js
					)
					. ';' . PHP_EOL;
				}
			}

			clearstatcache();

			if (!is_dir($sJsDir))
			{
				Core_File::mkdir($sJsDir);
			}

			Core_File::write($sJsDir . $sFileName, $sContent);
		}

		return $this->getJsDirHref() . $sFileName;
	}

	/**
	 * Compress data
	 * @return string
	 */
	public function compress($str, $fileName)
	{
		// Remove BOM
		if (substr($str, 0, 3) === "\xEF\xBB\xBF")
		{
			$str = substr($str, 3);
		}

		// Remove sourceMappingURL
		$str = preg_replace('~^//[#@]\s(source(?:Mapping)?URL)=\s*(\S+)~m', '', $str);

		return strpos($fileName, '.min.') === FALSE
			? Compression_Controller_JSMin::minify($str)
			: $str;
	}

	/**
	 * Clear controller
	 * @return self
	 */
	public function clear()
	{
		$this->_aJs = array();
		return $this;
	}

	/**
	 * Delete all cached files
	 * @return self
	 */
	public function deleteAllJs()
	{
		Core_File::deleteDir(
			$this->getJsDirPath()
		);

		clearstatcache();

		return TRUE;
	}
}