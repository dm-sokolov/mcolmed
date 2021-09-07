<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Yandexmarket observer
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Controller_Yandexmarket_Observer
{
	/**
	 * Идентификатор кампании на Яндексе
	 * @var mixed
	 */
	static public $campaignId = NULL;

	/**
	 * Идентификаторы для статусов заказа, напрмиер array('DELIVERY' => 3, 'PICKUP' => 4, 'DELIVERED' => 7)
	 * 	DELIVERY — заказ передан в доставку.
	 * 	PICKUP — заказ доставлен в пункт самовывоза.
	 * 	DELIVERED — заказ получен покупателем.
	 * @var NULL|int|array
	 */
	static public $deliveryStatusId = NULL;

	/**
	 * Отладочный токен, срок жизни - 365 дней
	 * @var mixed
	 */
	static public $token = NULL;

	/**
	 * Идентификатор приложения авторизации в oauth.yandex.ru
	 * @var mixed
	 */
	static public $clientId = NULL;

	/**
	 * Отменен, в свойствах объекта уже измененные данные
	 * @param object $object
	 * @param array $args
	 */
	static public function onAfterChangedOrder($object, $args)
	{
		// https://yandex.ru/dev/market/partner-dsbs/doc/dg/reference/put-campaigns-id-orders-id-status.html

		$oShop_Order = $object->getShopOrder();
		$shop_id = $oShop_Order->shop_id;

		$orderId = intval($oShop_Order->system_information);

		$campaignId = is_array(self::$campaignId)
			? Core_Array::get(self::$campaignId, $shop_id, 'unknown')
			: self::$campaignId;

		$token = is_array(self::$token)
			? Core_Array::get(self::$token, $shop_id, 'unknown')
			: self::$token;

		$clientId = is_array(self::$clientId)
			? Core_Array::get(self::$clientId, $shop_id, 'unknown')
			: self::$clientId;

		// Может быть задан:
		// - просто числом 1
		// - просто массивом array('DELIVERY' => ..)
		// - массивом для каждого магазина array(1 => 7)
		// - массивом массивов для каждого магазина array(1 => array('DELIVERY' => ..))
		$deliveryStatusId = is_array(self::$deliveryStatusId) && isset(self::$deliveryStatusId[$shop_id])
			? self::$deliveryStatusId[$shop_id]
			: self::$deliveryStatusId;

		if (!is_array($deliveryStatusId))
		{
			$deliveryStatusId = array('DELIVERY' => $deliveryStatusId);
		}

		if (!$oShop_Order->paid
			&& $oShop_Order->canceled
			&& $args[0] == 'cancelPaid'
			&& strlen($orderId)
		)
		{
			$sJson = json_encode(
				array(
					'order' => array(
						'status' => 'CANCELLED',
						'substatus' => 'SHOP_FAILED'
					)
				)
			);

			try
			{
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('PUT')
					->url("https://api.partner.market.yandex.ru/v2/campaigns/" . $campaignId . "/orders/{$orderId}/status")
					->additionalHeader('Authorization', 'OAuth oauth_token="' . $token . '" , oauth_client_id="' . $clientId . '"')
					->additionalHeader('Content-Type', 'application/json')
					->rawData($sJson)
					->execute();

				$aHeaders = $Core_Http->parseHeaders();
				$sStatus = Core_Array::get($aHeaders, 'status');
				$iStatusCode = $Core_Http->parseHttpStatusCode($sStatus);

				if ($iStatusCode != 200)
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write('Shop_Controller_Yandexmarket_Observer. Error request ' . $Core_Http->getUrl() . '; answer: ' . $Core_Http->getDecompressedBody());
				}
			}
			catch (Exception $e){}
		}

		// Смена статуса
		if (!$oShop_Order->paid
			&& !$oShop_Order->canceled
			&& in_array($args[0], array('apply', 'edit'))
			//&& $oShop_Order->shop_order_status_id == $deliveryStatusId
		)
		{
			foreach ($deliveryStatusId as $deliveryStatus => $shop_order_status_id)
			{
				if ($oShop_Order->shop_order_status_id == $shop_order_status_id)
				{
					$sJson = json_encode(
						array(
							'order' => array(
								'status' => $deliveryStatus
							)
						)
					);

					try
					{
						$Core_Http = Core_Http::instance('curl')
							->clear()
							->method('PUT')
							->url("https://api.partner.market.yandex.ru/v2/campaigns/" . $campaignId . "/orders/{$orderId}/status")
							->additionalHeader('Authorization', 'OAuth oauth_token="' . $token . '" , oauth_client_id="' . $clientId . '"')
							->additionalHeader('Content-Type', 'application/json')
							->rawData($sJson)
							->execute();

						$aHeaders = $Core_Http->parseHeaders();
						$sStatus = Core_Array::get($aHeaders, 'status');
						$iStatusCode = $Core_Http->parseHttpStatusCode($sStatus);

						if ($iStatusCode != 200)
						{
							Core_Log::instance()->clear()
								->status(Core_Log::$ERROR)
								->write('Shop_Controller_Yandexmarket_Observer. Error request ' . $Core_Http->getUrl() . '; answer: ' . $Core_Http->getDecompressedBody());
						}
					}
					catch (Exception $e){}
					
					break;
				}
			}
		}
	}
}