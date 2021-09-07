<?php

defined('HOSTCMS') || exit('HostCMS: ac
	cess denied.');

/**
 * REPLACE.
 *
 * @package HostCMS 6\Replace
 * @version 6.x
 * @author art studio Morozov&Pimnev
 * @copyright © 2016 ООО Арт-студио "Морозов и Пимнев" (Morozov&Pimnev LLC), http://www.morozovpimnev.ru
 */
class Replace_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;
	static public $bReturn = array();

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function execute($sText, $path, $aGetExt, $rText = FALSE)
	{
		if($dh = opendir($path))
		{
			while (($file = readdir($dh)) !== FALSE)
			{
				$filePath = $path . DIRECTORY_SEPARATOR . $file;

				if ($file != '.' && $file != '..')
				{
					if (is_file($filePath) && !in_array(Core_File::getExtension($filePath), $aGetExt))
					{
						$content = file_get_contents($filePath);

						if (strpos($content, $sText) !== false)
						{
							if ($rText) 
							{
								$content = str_replace($sText, $rText, $content); 
								file_put_contents($filePath, $content);
							}

							self::$bReturn[] = $filePath;
						}
					}
					elseif (is_dir($filePath))
					{
						self::execute($sText, $filePath, $aGetExt, $rText);
					}
				}
			}

			closedir($dh);

			return self::$bReturn;
		}
	}
}