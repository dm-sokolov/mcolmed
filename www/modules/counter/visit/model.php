<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Counter_Page_Model
 *
 * @package HostCMS
 * @subpackage Counter
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Counter_Visit_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'counter_session' => array(),
		'siteuser' => array()
	);

	/**
	 * Backend property
	 * @var mixed
	 */
	public $page = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $referrer = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $useragent = NULL;

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Backend callback method
	 * @return string
	 */
	public function pageBackend()
	{
		ob_start();

		Core::factory('Core_Html_Entity_A')
			->href($this->page)
			->value(
				htmlspecialchars(Core_Str::cut($this->page, 50))
			)
			->target('_blank')
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function referrerBackend()
	{
		ob_start();

		if ($this->referrer == '')
		{
			echo Core::_('Counter.tab');
		}
		else
		{
			Core::factory('Core_Html_Entity_A')
				->href($this->referrer)
				->value(
					htmlspecialchars(Core_Str::cut($this->referrer, 50))
				)
				->target('_blank')
				->execute();
		}

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function ipBackend()
	{
		return htmlspecialchars(Core_Str::hex2ip($this->ip));
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function siteuser_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		if ($this->siteuser_id)
		{
			$windowId = $oAdmin_Form_Controller->getWindowId();

			Core::factory('Core_Html_Entity_I')
				->class('fa fa-user')
				->execute();

			Core::factory('Core_Html_Entity_A')
				->href($oAdmin_Form_Controller->getAdminActionLoadHref('/admin/siteuser/index.php', 'edit', NULL, 0, $this->Siteuser->id))
				->onclick("$.openWindowAddTaskbar({path: '/admin/siteuser/index.php', additionalParams: '&hostcms[checked][0][{$this->Siteuser->id}]=1&hostcms[action]=edit', shortcutImg: '" . '/modules/skin/' . Core_Skin::instance()->getSkinName() . '/images/module/siteuser.png' . "', shortcutTitle: 'undefined', Minimize: true}); return false")
				->value(htmlspecialchars($this->Siteuser->login))
				->execute();
		}

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function additionalBackend()
	{
		ob_start();

		if ($this->counter_session_id)
		{
			$sUseragent = $this->Counter_Session->Counter_Useragent->useragent;
			if ($sUseragent != '')
			{
				$browser = htmlspecialchars(Core_Browser::getBrowser($sUseragent));

				if (!is_null($browser))
				{
					$ico = Core_Browser::getBrowserIco($browser);

					!is_null($ico)
						&& $browser = '<i class="' . $ico . '"></i> ' . $browser;
				}

				echo $browser . ' ';

				if (Counter_Controller::checkBot($sUseragent))
				{
					?><span class="label label-sm label-info"><?php echo Core::_('Counter.crawler')?></span> <?php
				}
			}

			if ($this->Counter_Session->counter_os_id)
			{
				?><span class="label label-sm label-success"><?php echo htmlspecialchars($this->Counter_Session->Counter_Os->os)?></span> <?php
			}

			if ($this->Counter_Session->counter_display_id)
			{
				?><span class="label label-sm label-warning"><?php echo htmlspecialchars($this->Counter_Session->Counter_Display->display)?></span> <?php
			}
		}

		return ob_get_clean();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event counter_visit.onBeforeGetRelatedSite
	 * @hostcms-event counter_visit.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}