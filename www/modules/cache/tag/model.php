<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cache_Tag_Model
 *
 * @package HostCMS
 * @subpackage Cache
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cache_Tag_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
	
	/**
	 * Has revisions
	 *
	 * @param boolean
	 */
	protected $_hasRevisions = FALSE;
}