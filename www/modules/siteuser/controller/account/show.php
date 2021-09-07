<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ лицевых счетов пользователя сайта.
 *
 * <code>
 * $oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
 *
 * $Siteuser_Controller_Account_Show = new Siteuser_Controller_Account_Show(
 * 	$oSiteuser
 * );
 *
 * $Siteuser_Controller_Account_Show
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('СписокЛицевыхСчетов')
 * 	)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Siteuser
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Siteuser_Controller_Account_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop', // идентификатор магазина, для которого выводится список транзакций
		'pattern',
		'patternParams',
	);

	/**
	 * Constructor.
	 * @param Siteuser_Model $oSiteuser user
	 */
	public function __construct(Siteuser_Model $oSiteuser)
	{
		parent::__construct($oSiteuser->clearEntities());

		$oSiteuser->showXmlProperties(TRUE);

		$oStructure = Core_Entity::factory('Structure', CURRENT_STRUCTURE_ID);

		$this->pattern = $oStructure->getPath() . '({path})(shop-{shop}/)';
	}

	/**
	 * Add transactions amount to XML
	 * @param Shop_Model $oShop shop
	 * @return self
	 */
	protected function _addTransactionsAmount(Shop_Model $oShop)
	{
		$oSiteuser = $this->getEntity();

		$amount = $oSiteuser->getTransactionsAmount($oShop);
		$oShop->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('transaction_amount')
				->value($amount)
		);
		
		return $this;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Siteuser_Controller_Account_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oSiteuser = $this->getEntity();

		// Список транзакций
		if ($this->shop)
		{
			$oShop = Core_Entity::factory('Shop', $this->shop)
				->clearEntities();

			if ($oShop->site_id != $oSiteuser->site_id)
			{
				throw new Core_Exception('Wrong shop. Access denied');
			}

			$this->addEntity($oShop);

			$aShop_Siteuser_Transactions = $oSiteuser->getTransactions($oShop);
			foreach ($aShop_Siteuser_Transactions as $oShop_Siteuser_Transaction)
			{
				$oShop->addEntity($oShop_Siteuser_Transaction->clearEntities());
			}

			$this->_addTransactionsAmount($oShop);
		}
		else
		{
			$aShops = $oSiteuser->Site->Shops->findAll();
			foreach ($aShops as $oShop)
			{
				$this->addEntity($oShop->clearEntities());
				$this->_addTransactionsAmount($oShop);
			}
		}

		return parent::show();
	}

	/**
	 * Parse URL and set controller properties
	 * @return Siteuser_Controller_Account_Show
	 * @hostcms-event Siteuser_Controller_Account_Show.onBeforeParseUrl
	 * @hostcms-event Siteuser_Controller_Account_Show.onAfterParseUrl
	 */
	public function parseUrl()
	{
		Core_Event::notify(get_class($this) . '.onBeforeParseUrl', $this);

		$Core_Router_Route = new Core_Router_Route($this->pattern);
		$this->patternParams = $matches = $Core_Router_Route->applyPattern(Core::$url['path']);

		if (isset($matches['shop']) && $matches['shop'] != '')
		{
			$this->shop($matches['shop']);
		}

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

		return $this;
	}
}