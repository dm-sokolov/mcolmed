<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Backup Module.
 *
 * @package HostCMS
 * @subpackage Backup
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Backup_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.9';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2021-08-23';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'backup';

	/**
	 * List of Schedule Actions
	 * @var array
	 */
	protected $_scheduleActions = array(
		0 => 'backupFiles',
		1 => 'backupDatabase',
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if (-1977579255 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
		{
			throw new Core_Exception(base64_decode('TW9kdWxlIEJhY2t1cCBpcyBmb3JiaWRkZW4u'), array(), 0, FALSE, 0, FALSE);
		}
	}

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 200,
				'block' => 3,
				'ico' => 'fa fa-shield',
				'name' => Core::_('Backup.title'),
				'href' => "/admin/backup/index.php",
				'onclick' => "$.adminLoad({path: '/admin/backup/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}

	/**
	 * Notify module on the action on schedule
	 * @param int $action action number
	 * @param int $entityId entity ID
	 * @return array
	 */
	public function callSchedule($action, $entityId)
	{
		$oBackup_Controller = new Backup_Controller();

		switch ($action)
		{
			// Backup Files
			case 0:
				$oBackup_Controller->backupFiles(BACKUP_DIR);
			break;
			// Backup Database
			case 1:
				$oBackup_Controller->backupDatabase(BACKUP_DIR);
			break;
		}
	}
}