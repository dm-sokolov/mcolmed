<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cloud Module.
 *
 * @package HostCMS
 * @subpackage Cloud
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cloud_Module extends Core_Module
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
	protected $_moduleName = 'cloud';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if (1283580064 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
		{
			throw new Core_Exception(base64_decode('TW9kdWxlIENsb3VkIGlzIGZvcmJpZGRlbi4='), array(), 0, FALSE, 0, FALSE);
		}

		if (Core_Auth::logged())
		{
			Core_Router::add('cloud-callback.php', '/cloud-callback.php')
				->controller('Cloud_Command_Controller');
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
				'sorting' => 40,
				'block' => 2,
				'ico' => 'fa fa-cloud',
				'name' => Core::_('Cloud.menu'),
				'href' => "/admin/cloud/index.php",
				'onclick' => "$.adminLoad({path: '/admin/cloud/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}