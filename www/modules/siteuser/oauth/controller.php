<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Siteuser_Oauth_Controller
 *
 * @package HostCMS
 * @subpackage Siteuser
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Siteuser_Oauth_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array();
	
	/**
	 * Config
	 * @var array
	 */
	protected $_config = array();
	
	/**
	 * Build oauth provider class by provider id
	 * @param int $iOAuthProviderId provider id
	 * @return mixed
	 */
	static public function factory($iOAuthProviderId = 0)
	{
		if ($iOAuthProviderId == 0)
		{
			throw new Core_Exception("Can't create OAuth provider class with empty provider's id");
		}
		
		$oSiteuser_Identity_Provider = Core_Entity::factory('Siteuser_Identity_Provider')->find($iOAuthProviderId);
		
		if (is_null($oSiteuser_Identity_Provider))
		{
			throw new Core_Exception("Can't find OAuth provider class with id = %id", array('%id' => $iOAuthProviderId));
		}

		$sOAuthProviderName = ucfirst($oSiteuser_Identity_Provider->name);
	
		$sProviderClassName = "Siteuser_Oauth_{$sOAuthProviderName}_Controller";

		return class_exists($sProviderClassName)
			? new $sProviderClassName()
			: NULL;
	}
	
	/**
	 * Execute the business logic
	 */
	abstract public function execute();
}