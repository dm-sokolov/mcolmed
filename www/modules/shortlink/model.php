<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shortlink_Model
 *
 * @package HostCMS
 * @subpackage Shortlink
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shortlink_Model extends Core_Entity
{
	/**
	* Model name
	* @var mixed
	*/
	protected $_modelName = 'shortlink';

	/**
	* Column consist item's name
	* @var string
	*/
	protected $_nameColumn = 'shortlink';

	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	* Belongs to relations
	* @var array
	*/
	protected $_belongsTo = array(
		'site' => array(),
		'shortlink_dir' => array(),
		'user' => array()
	);

	/**
	* List of preloaded values
	* @var array
	*/
	protected $_preloadValues = array(
		'active' => 1,
		'type' => 301,
		'hits' => 0
	);

	/**
	* Constructor.
	* @param int $id entity ID
	*/
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['site_id'] = CURRENT_SITE;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	* Change item status
	* @return self
	* @hostcms-event shortlink.onBeforeChangeActive
	* @hostcms-event shortlink.onAfterChangeActive
	*/
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	* Backend callback method
	* @return string
	*/
	public function sourceBackend()
	{
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
			->value(htmlspecialchars($this->source));

		$oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_A')
					->href($this->source)
					->target('_blank')
					->add(
						Core::factory('Core_Html_Entity_I')
							->class('fa fa-external-link')
					)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	* Backend callback method
	* @return string
	*/
	public function shortlinkBackend()
	{
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
			->value(htmlspecialchars($this->shortlink))
			->add(
				Core::factory('Core_Html_Entity_A')
					->href('/' . urlencode($this->shortlink))
					->target('_blank')
					->add(
						Core::factory('Core_Html_Entity_I')
							->class('fa fa-external-link')
					)
			);

		$oCore_Html_Entity_Div->execute();
	}

	public function generateShortlink()
	{
		$this->shortlink = Shortlink_Controller::encode($this->id);

		return $this->save();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shortlink.onBeforeGetRelatedSite
	 * @hostcms-event shortlink.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}