<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

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

	public function execute($sText, $iCaseSensitive, $iRegExp, $path, $aGetExt, $rText = FALSE)
	{
		$fSearchName = $iCaseSensitive ? 'strpos' : 'stripos';
		$fReplaceName = $iCaseSensitive ? 'str_replace' : 'str_ireplace';

		if($dh = opendir($path))
		{
			while (($file = readdir($dh)) !== FALSE)
			{
				$filePath = $path . DIRECTORY_SEPARATOR . $file;

				if ($file != '.' && $file != '..')
				{
					if (is_file($filePath) && in_array(Core_File::getExtension($filePath), $aGetExt))
					{
						$content = file_get_contents($filePath);

						if($iRegExp)
						{
							if (preg_match('/' . $sText . '/' . ($iCaseSensitive ? '':'i'), $content))
							{
								self::$bReturn[] = $filePath;
							}
						}
						else
						{
							if ($fSearchName($content, $sText) !== false)
							{
								if ($rText) 
								{
									$content = $fReplaceName($sText, $rText, $content); 
									file_put_contents($filePath, $content);
								}

								self::$bReturn[] = $filePath;
							}
						}
					}
					elseif (is_dir($filePath))
					{
						$this->execute($sText, $iCaseSensitive, $iRegExp, $filePath, $aGetExt, $rText);
					}
				}
			}

			closedir($dh);

			return self::$bReturn;
		}
	}

	static public function _sortAsc($m, $n)
	{
		$first = Core_Date::datetime2timestamp($m->datetime);
		$second = Core_Date::datetime2timestamp($n->datetime);
		
		if ($first == $second)
		{
			return 0;
		}

		return $first < $second ? -1 : 1;
	}

	static public function _sortDesc($m, $n)
	{
		$first = Core_Date::datetime2timestamp($m->datetime);
		$second = Core_Date::datetime2timestamp($n->datetime);

		if ($first == $second)
		{
			return 0;
		}

		return $first > $second ? -1 : 1;
	}
}