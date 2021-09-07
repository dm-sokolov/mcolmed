<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Counter_Session_Model
 *
 * @package HostCMS
 * @subpackage Counter
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Counter_Session_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'counter_display' => array(),
		'counter_useragent' => array(),
		'counter_os' => array(),
		'counter_browser' => array(),
		'counter_device' => array(),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'counter_page' => array()
	);

	/**
	 * Backend property
	 * @var mixed
	 */
	public $date = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $adminCount = NULL;

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event counter_session.onBeforeGetRelatedSite
	 * @hostcms-event counter_session.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}

	/**
	 * Count
	 * @var int
	 */
	//static protected $_count = NULL;

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controler $oAdmin_Form_Controler
	 * @return string
	 */
	/*public function adminCount($oAdmin_Form_Field, $oAdmin_Form_Controler)
	{
		if (is_null(self::$_count))
		{
			$aObjects = $oAdmin_Form_Controler->getDataset(0)->getObjects();

			foreach ($aObjects as $oObject)
			{
				self::$_count += $oObject->adminCount;
			}
		}

		if (self::$_count > 0)
		{
			return $this->adminCount . '(' . sprintf("%.2f%%", $this->adminCount * 100 / self::$_count) . ')';
		}

		return '';
	}*/
}