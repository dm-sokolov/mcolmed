<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Hostcms_Bitrix24_Order_Model.
 *
 * @package HostCMS
 * @subpackage Hostcms_Bitrix24
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Hostcms_Bitrix24_Order_Model extends Core_Entity{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_order' => array(),
	);

	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'hostcms_bitrix24_order';
	
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;	}