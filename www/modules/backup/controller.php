<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Backup.
 *
 * @package HostCMS
 * @subpackage Backup
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Backup_Controller
{
	/**
	 * Backup Files
	 * @param string $destinationDir
	 * @return FALSE|self FALSE if system('tar ...') call was used
	 */
	public function backupFiles($destinationDir)
	{
		$date = date("d_m_Y_G_i_s");

		/*if (substr(php_uname(), 0, 7) != 'Windows' && Core::isFunctionEnable('system'))
		{
			$fileName = "backup_{$date}.tar.gz";
			$command = "tar -czf {$destinationDir}{$fileName} --exclude={$destinationDir}* " . CMS_FOLDER . " > /dev/null &";
			system($command);
			Core_Message::show(Core::_('Backup.backgroud'));
			return FALSE;
		}*/

		if (Core_Zip::available())
		{
			$oCore_Zip = new Core_Zip();
			$oCore_Zip->excludeDir(BACKUP_DIR);
			$oCore_Zip->excludeDir(CMS_FOLDER . 'cache_html');
			$oCore_Zip->excludeDir(CMS_FOLDER . 'hostcmsfiles' . DIRECTORY_SEPARATOR . 'cache');
			$oCore_Zip->excludeDir(CMS_FOLDER . 'hostcmsfiles' . DIRECTORY_SEPARATOR . 'tmp');
			$oCore_Zip->excludeDir(CMS_FOLDER . 'hostcmsfiles' . DIRECTORY_SEPARATOR . 'logs');

			$aConfig = Core_Config::instance()->get('backup_config', array());

			if (is_array($aConfig) && isset($aConfig['excludeDir']))
			{
				foreach ($aConfig['excludeDir'] as $excludeDir)
				{
					$oCore_Zip->excludeDir($excludeDir);
				}
			}

			$oCore_Zip->zipDir(CMS_FOLDER, $destinationDir . "backup_{$date}.zip");
		}
		else
		{
			$Core_Tar = new Core_Tar($destinationDir . "backup_{$date}.tar.gz");
			$Core_Tar->create(array(CMS_FOLDER));
		}

		return $this;
	}

	/**
	 * Backup Database
	 * @param string $destinationDir
	 * @return self
	 */
	public function backupDatabase($destinationDir)
	{
		$oCore_Out_File = new Core_Out_File();
		$oCore_Out_File->filePath($destinationDir . "dump_" . date("d_m_Y_G_i_s") . ".sql");

		$aConfig = Core_DataBase::instance()->getConfig();

		$oCore_Out_File
			->open()
			->write(
				"-- HostCMS dump\r\n"
				. "-- http://www.hostcms.ru\r\n"
				. "-- Host: " . Core_DataBase::instance()->quote($aConfig['host']) . "\r\n"
				. "-- Database: " . Core_DataBase::instance()->quote($aConfig['database']) . "\r\n"
				. "-- Версия сервера: " . Core_DataBase::instance()->getVersion() . "\r\n\r\n"
				. 'SET NAMES utf8;' . "\r\n"
				. 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' . "\r\n"
				. 'SET SQL_NOTES=0;'
			);

		$aTables = Core_DataBase::instance()->getTables();
		foreach ($aTables as $sTablesName)
		{
			Core_DataBase::instance()->dump($sTablesName, $oCore_Out_File);
		}

		$oCore_Out_File->close();

		return $this;
	}
}