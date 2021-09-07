<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cloud Empty Dataset.
 *
 * @package HostCMS
 * @subpackage Cloud
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cloud_Empty_Dataset extends Admin_Form_Dataset
{
	public function getCount(){}
	public function load(){}
	public function getEntity(){}
	//public function getObject(){}
}