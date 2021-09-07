<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Advertisement_Group Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Advertisement
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Advertisement_Group_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$this->title(
			$this->_object->id
				? Core::_('Advertisement_Group.edit_title', $this->_object->name)
				: Core::_('Advertisement_Group.add_title')
		);

		return $this;
	}
}