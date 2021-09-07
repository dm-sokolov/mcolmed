<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * CDN Module.
 *
 * @package HostCMS
 * @subpackage Cdn
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cdn_Module extends Core_Module
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
	protected $_moduleName = 'cdn';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if (-1977579255 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
		{
			throw new Core_Exception(base64_decode('TW9kdWxlIENETiBpcyBmb3JiaWRkZW4u'), array(), 0, FALSE, 0, FALSE);
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
				'sorting' => 250,
				'block' => 3,
				'ico' => 'fa fa-wifi',
				'name' => Core::_('Cdn.menu'),
				'href' => "/admin/cdn/index.php",
				'onclick' => "$.adminLoad({path: '/admin/cdn/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}