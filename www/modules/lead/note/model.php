<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead_Note_Model
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Note_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'lead' => array(),
		'user' => array()
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'text';

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function authorBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		// Автор
		$oUser = $this->User;

		if ($oUser->id)
		{
			$oUser->showAvatarWithName();
		}

		return ob_get_clean();
	}

	/**
	 * Check user access to admin form action
	 * @param User_Model $oUser user object
	 * @param string $actionName admin form action name
	 * @return bool
	 */
	public function checkBackendAccess($actionName, $oUser)
	{
		switch ($actionName)
		{
			case 'edit':
				if ($this->user_id == $oUser->id)
				{
					return TRUE;
				}
			break;
			case 'markDeleted':
				if ($this->user_id == $oUser->id || $this->Lead->user_id == $oUser->id)
				{
					return TRUE;
				}
			break;
			case 'delete':
			case 'undelete':
				if ($oUser->superuser)
				{
					return TRUE;
				}
			break;
			case 'addLeadNote':
				return is_null($this->id);
			break;
		}

		return FALSE;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event lead_note.onBeforeGetRelatedSite
	 * @hostcms-event lead_note.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Lead->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}