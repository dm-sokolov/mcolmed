<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Siteuser_Group Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Siteuser
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Siteuser_Group_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oAdditionalTab->delete($this->getField('site_id'));

		$oUser_Controller_Edit = new User_Controller_Edit($this->_Admin_Form_Action);

		// Список сайтов
		$oSelect_Sites = Admin_Form_Entity::factory('Select');
		$oSelect_Sites
			->options($oUser_Controller_Edit->fillSites())
			->name('site_id')
			->value($this->_object->site_id)
			->caption(Core::_('Siteuser_Group.site_id'));

		$oMainTab->addAfter(
			$oSelect_Sites, $this->getField('description')
		);

		$title = $object->id
			? Core::_('Siteuser_Group.edit_title', $this->_object->name)
			: Core::_('Siteuser_Group.add_title');

		$this->title = $title;

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Siteuser_Group_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		if (Core_Array::getPost('default', 0))
		{
			$this->_object->setDefault();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Fill group list of site's users
	 * @param int $iSiteId site ID
	 * @return array
	 */
	public function fillSiteuserGroups($iSiteId)
	{
		$aReturn = array();
		$aChildren = Core_Entity::factory('Siteuser_Group')->getBySiteId($iSiteId);

		foreach ($aChildren as $oSiteuser_Group)
		{
			$aReturn[$oSiteuser_Group->id] = $oSiteuser_Group->name;
		}

		return $aReturn;
	}
}