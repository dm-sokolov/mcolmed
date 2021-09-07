<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * REPLACE.
 *
 * @package HostCMS 6\Replace
 * @version 6.x
 * @author art studio Morozov&Pimnev
 * @copyright © 2020 ООО Арт-студио "Морозов и Пимнев" (Morozov&Pimnev LLC), http://www.morozovpimnev.ru
 */
class Replace_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '2.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2020-09-22';

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

	public function install()
	{
		// 6.5.4 => 144
		if (HOSTCMS_UPDATE_NUMBER < 144)
		{
			$sFilesDir = CMS_FOLDER . DIRECTORY_SEPARATOR . 'files';

			if (is_dir($sFilesDir))
			{
				Core_File::copyDir($sFilesDir, CMS_FOLDER);
			}
		}
	}
}