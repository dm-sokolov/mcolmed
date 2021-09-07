<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Siteuser_Controller
 *
 * @package HostCMS
 * @subpackage Siteuser
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Siteuser_Controller
{
	/**
	 * Current Siteuser
	 * @var Siteuser_Model|NULL|FALSE
	 */
	static protected $_currentSiteuser = FALSE;

	/**
	 * Get Current Siteuser
	 * @param boolean $bCheckSite default FALSE
	 * @return Siteuser_Model|NULL
	 */
	static public function getCurrent($bCheckSite = FALSE)
	{
		if (self::$_currentSiteuser === FALSE)
		{
			self::$_currentSiteuser = NULL;

			// Идентификатор сессии уже был установлен
			if (Core_Session::hasSessionId())
			{
				$isActive = Core_Session::isActive();
				!$isActive && Core_Session::start();

				if (isset($_SESSION['siteuser_id']))
				{
					// Привязать сессию к IP
					if (isset($_SESSION['siteuser_ip'])
						&& $_SESSION['siteuser_ip'] != Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1')
					)
					{
						// Завершить текущую сессию
						self::unsetCurrent();

						return self::$_currentSiteuser;
					}

					$oSiteuser = Core_Entity::factory('Siteuser')->find(intval($_SESSION['siteuser_id']));
					if (!is_null($oSiteuser->id) && $oSiteuser->active
						&& (!$bCheckSite || defined('CURRENT_SITE') && $oSiteuser->site_id == CURRENT_SITE)
					)
					{
						$oSiteuser->updateLastActivity();

						self::$_currentSiteuser = $oSiteuser;
					}
				}
			}
		}

		return self::$_currentSiteuser;
	}

	/**
	 * Set Current Siteuser
	 * @param Siteuser_Model $oSiteuser
	 * @param int $expires default 2678400
	 * @param mixed $attachSessionToIp default NULL
	 */
	static public function setCurrent(Siteuser_Model $oSiteuser, $expires = 2678400, $attachSessionToIp = NULL)
	{
		Core_Session::setMaxLifeTime($expires);

		/*$isActive = Core_Session::isActive();
		!$isActive && */Core_Session::start();

		$_SESSION['siteuser_id'] = $oSiteuser->id;

		is_null($attachSessionToIp)
			&& $attachSessionToIp = !isset($_SERVER['HTTP_SAVE_DATA']) || strtolower($_SERVER['HTTP_SAVE_DATA']) !== 'on';

		// Если привязка адреса к сессии
		$attachSessionToIp
			&& $_SESSION['siteuser_ip'] = Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');

		self::$_currentSiteuser = $oSiteuser;
	}

	/**
	 * Unset Current Siteuser
	 */
	static public function unsetCurrent()
	{
		$isActive = Core_Session::isActive();
		Core_Session::start();

		if (isset($_SESSION['siteuser_id']))
		{
			unset($_SESSION['siteuser_id']);
		}

		if (isset($_SESSION['siteuser_ip']))
		{
			unset($_SESSION['siteuser_ip']);
		}

		// Not FALSE!
		self::$_currentSiteuser = NULL;

		!$isActive && Core_Session::close();
	}
}