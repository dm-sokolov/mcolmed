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
class Replace_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '1.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2016-03-25';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'replace';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->menu = array(
			array(
				'sorting' => 270,
				'block' => 3,
				'ico' => 'fa fa-strikethrough',
				'name' => Core::_('replace.menu'),
				'href' => "/admin/replace/index.php",
				'onclick' => "$.adminLoad({path: '/admin/replace/index.php'}); return false"
			)
		);
	}
}