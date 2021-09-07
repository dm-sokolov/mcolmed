<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Hostcms_Bitrix24.
 *
 * @package HostCMS 6\Hostcms_Bitrix24
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Hostcms_Bitrix24_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '1.4';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2020-07-08';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'hostcms_bitrix24';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		Core_Event::attach('Shop_Payment_System_Handler.onAfterProcessOrder', array('Hostcms_Bitrix24_Observer', 'onAfterProcessOrder'));
		Core_Event::attach('Form_Controller_Show.onAfterProcess', array('Hostcms_Bitrix24_Observer', 'onAfterProcess'));
		Core_Event::attach('shop_order.onBeforePaid', array('Hostcms_Bitrix24_Observer', 'onBeforePaid'));
		Core_Event::attach('Shop_Payment_System_Handler.onAfterChangedOrder', array('Hostcms_Bitrix24_Observer', 'onAfterChangedOrder'));
		Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('Hostcms_Bitrix24_Observer', 'onAfterRedeclaredPrepareForm'));
		Core_Event::attach('Shop_Order_Controller_Edit.onAfterRedeclaredApplyObjectProperty', array('Hostcms_Bitrix24_Observer', 'onAfterRedeclaredApplyObjectProperty'));
	}

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 270,
				'block' => 3,
				'ico' => 'fa fa-refresh',
				'name' => Core::_('Hostcms_Bitrix24.menu'),
				'href' => "/admin/hostcms/bitrix24/index.php",
				'onclick' => "$.adminLoad({path: '/admin/hostcms/bitrix24/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}

	/**
	 * Install.
	 */
	public function install()
	{
		// Создание таблиц модуля
		$query = "
			CREATE TABLE IF NOT EXISTS `hostcms_bitrix24_forms` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `form_fill_id` int(11) NOT NULL,
			  `lead_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `form_fill_id` (`form_fill_id`),
			  KEY `lead_id` (`lead_id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

			CREATE TABLE IF NOT EXISTS `hostcms_bitrix24_orders` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `shop_order_id` int(11) NOT NULL,
			  `deal_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `shop_order_id` (`shop_order_id`),
			  KEY `deal_id` (`deal_id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

			CREATE TABLE IF NOT EXISTS `hostcms_bitrix24_siteusers` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `siteuser_id` int(11) NOT NULL,
			  `lead_id` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `siteuser_id` (`siteuser_id`),
			  KEY `lead_id` (`lead_id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
		";

		// Выполняем запрос
		Sql_Controller::instance()->execute($query);
	}

	/**
	 * Uninstall.
	 */
	public function uninstall()
	{
		// Удаление таблицы модуля
		$query = "
			DROP TABLE IF EXISTS `hostcms_bitrix24_forms`;
			DROP TABLE IF EXISTS `hostcms_bitrix24_orders`;
			DROP TABLE IF EXISTS `hostcms_bitrix24_siteusers`;
		";

		// Выполняем запрос
		Sql_Controller::instance()->execute($query);
	}
}