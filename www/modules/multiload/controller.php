<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Контроллер модуля.
 * 
 * @author KAD Systems (©) 2017
 * @date 2017-05-04
 */
class Multiload_Controller
{
	/**
	 * The singleton instances.
	 *
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Config array
	 *
	 * @var array
	 */
	static protected $_aConfig = NULL;

	/**
	 * Register an existing instance as a singleton.
	 *
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Конструктор.
	 *
	 * @return void
	 */
	protected function __construct()
	{
		self::getConfig();
	}

	/**
	 * Возвращает настройки модуля.
	 *
	 * @return array
	 */
	public static function getConfig()
	{
		if (is_null(self::$_aConfig))
		{
			self::$_aConfig = Core_Config::instance()->get('multiload_config', array()) + array(
				'shop_item_tab' => TRUE,
				'informationsystem_item_tab' => TRUE,
			);
		}

		return self::$_aConfig;
	}

	/**
	 * Возвращает список информационных систем сайта.
	 *
	 * @param  integer  $siteId
	 * @return array
	 */
	public function getInfomationsystems($siteId)
	{
		$aInformationsystems = array();
		$aoInformationsystems = Core_Entity::factory('Informationsystem')->getAllBysite_id($siteId);

		foreach ($aoInformationsystems as $oInformationsystem)
		{
			$aInformationsystems[$oInformationsystem->id] = $oInformationsystem->name;
		}

		return $aInformationsystems;
	}

	/**
	 * Возвращает массив групп информационной системы.
	 *
	 * @param  integer  $informationsystemId
	 * @return array
	 */
	function getInfomationsystemGroups($informationsystemId)
	{
		$oAdminFormAction = Core_Entity::factory('Admin_Form', 12)
			->Admin_Form_Actions
			->getByName('edit');

		$oInformationsystemItemControllerEdit = new Informationsystem_Item_Controller_Edit($oAdminFormAction);

		$aInformationsystemGroups = $oInformationsystemItemControllerEdit->fillInformationsystemGroup($informationsystemId, 0);

		/*
		$aInformationsystemGroups = array();
		$aoGroups = Core_Entity::factory("Informationsystem_group")->getAllByinformationsystem_id($infsysid);
		
		foreach ($aoGroups as $oGroup)
		{
			$aInformationsystemGroups[$oGroup->id] = $oGroup->name;
		}*/

		return $aInformationsystemGroups;
	}

	/**
	 * Возвращает массив элементов информационной группы.
	 *
	 * @param  integer  $informationsystemId
	 * @param  integer  $informationsystemGroupId
	 * @return array
	 */
	public function getInfomationsystemItems($informationsystemId, $informationsystemGroupId)
	{
		$aInformationsystemItems = array();
		$oInformationsystemItems = Core_Entity::factory('Informationsystem_Item');

		if ($informationsystemId != 0)
		{
			$oInformationsystemItems->queryBuilder()->where('informationsystem_items.informationsystem_id', '=', $informationsystemId);
		}

		$oInformationsystemItems->queryBuilder()
			->where('informationsystem_items.informationsystem_group_id', '=', $informationsystemGroupId)
			->orderBy('informationsystem_items.datetime', 'DESC');

		$oInformationsystemItems = $oInformationsystemItems->findAll();	

		foreach ($oInformationsystemItems as $oInformationsystemItem)
		{
			$aInformationsystemItems[$oInformationsystemItem->id] = "[" . $oInformationsystemItem->id . "] " .$oInformationsystemItem->name;
		}		

		return $aInformationsystemItems;
	}

	/**
	 * Возвращает список доп. свойств типа «Файл» для информационной системы.
	 *
	 * @param  integer  $informationsystemId
	 * @return array
	 */
	public function getInfomationsystemItemProperties($informationsystemId)
	{
		$aProperties = array();
		$oInformationsystemItemPropertyList = Core_Entity::factory('Informationsystem_Item_Property_List', $informationsystemId);

		$oProperties = $oInformationsystemItemPropertyList->Properties;
		$oProperties->queryBuilder()
			->orderBy('properties.id');
		$oProperties = $oProperties->findAll();

		foreach ($oProperties as $oProperty)
		{
			if ($oProperty->type == 2)
			{
				$aProperties[$oProperty->id] = "[" . $oProperty->id . "] " .$oProperty->name;
			}
		}

		return $aProperties;
	}

	/**
	 * Возвращает массив интернет-магазинов сайта.
	 *
	 * @param  integer  $siteId
	 * @return array
	 */	
	public function getShops($siteId)
	{
		$aShops = array();
		$aoShops = Core_Entity::factory('Shop')->getAllBysite_id($siteId);

		foreach ($aoShops as $oShop)
		{
			$aShops[$oShop->id] = "[" . $oShop->id . "] " .$oShop->name;
		}		

		return $aShops;
	}

	/**
	 * Возвращает массив групп интернет-магазина.
	 *
	 * @param  integer  $shopId
	 * @return array
	 */
	public function getShopGroups($shopId)
	{
		$oAdminFormAction = Core_Entity::factory('Admin_Form', 12)
			->Admin_Form_Actions
			->getByName('edit');

		$oShopItemControllerEdit = new Shop_Item_Controller_Edit($oAdminFormAction);

		return $oShopItemControllerEdit->fillShopGroup($shopId, 0);
	}

	/**
	 * Возвращает массив товаров интернет-магазина.
	 *
	 * @param  integer  $shopId
	 * @param  integer  $shopGroupId
	 * @return array
	 */
	public function getShopItems($shopId, $shopGroupId)
	{
		$aShopItems = array();
		$aoShopItems = Core_Entity::factory('Shop_Item');

		if ($shopId != 0)
		{
			$aoShopItems->queryBuilder()
				->where('shop_items.shop_id', '=', $shopId);
		}

		$aoShopItems->queryBuilder()
			->where('shop_items.shop_group_id', '=', $shopGroupId)
			->orderBy('shop_items.datetime', 'DESC');

		$aoShopItems = $aoShopItems->findAll();	

		foreach ($aoShopItems as $oShopItem)
		{
			$aShopItems[$oShopItem->id] = '[' . $oShopItem->id . '] ' . $oShopItem->name;
		}

		return $aShopItems;
	}

	/**
	 * Возвращает список доп. свойств типа «Файл» для интернет-магазина.
	 *
	 * @param  integer  $shopId
	 * @param  integer  $shopGroupId
	 * @return array
	 */
	public function getShopItemProperties($shopId, $shopGroupId)
	{
		$aProperties = array();

		$oShopItemPropertyList = Core_Entity::factory('Shop_Item_Property_List', $shopId);
		$aoProperties = $oShopItemPropertyList->getPropertiesForGroup($shopGroupId);

		/*
		$oProperties = $oShopItemPropertyList->Properties;
		$oProperties->queryBuilder()->orderby("id");
		$aoProperties = $oProperties->findAll();
		*/	

		foreach ($aoProperties as $oProperty)
		{
			if ($oProperty->type == 2)
			{
				$aProperties[$oProperty->id] = $oProperty->name;
			}
		}

		return $aProperties;
	}
}