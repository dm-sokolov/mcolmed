<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Message Module.
 *
 * @package HostCMS
 * @subpackage Message
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Message_Module extends Core_Module
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
	public $date = '2021-02-16';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'message';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if (1283580064 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
		{
			throw new Core_Exception(base64_decode('TW9kdWxlIE1lc3NhZ2UgaXMgZm9yYmlkZGVuLg=='), array(), 0, FALSE, 0, FALSE);
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
				'block' => 0,
				'ico' => 'fa fa-weixin',
				'name' => Core::_('Message.menu'),
				'href' => "/admin/message/index.php",
				'onclick' => "$.adminLoad({path: '/admin/message/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}