<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Backup. Контроллер создания резервной копии базы данных
 *
 * @package HostCMS
 * @subpackage Backup
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Backup_Controller_Database extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$oBackup_Controller = new Backup_Controller();
		$oBackup_Controller->backupDatabase(BACKUP_DIR);

		return $this;
	}
}