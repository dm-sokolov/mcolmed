<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Hostcms replace.
 *
 * @package HostCMS
 * @subpackage Hostcms_Replace
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Hostcms_Replace_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '1.6';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2021-04-01';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'hostcms_replace';

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
				'ico' => 'fa fa-database',
				'name' => Core::_('Hostcms_Replace.menu'),
				'href' => "/admin/hostcms/replace/index.php",
				'onclick' => "$.adminLoad({path: '/admin/hostcms/replace/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}