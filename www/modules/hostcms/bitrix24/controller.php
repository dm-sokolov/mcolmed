<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Hostcms_Bitrix24_Controller.
 *
 * @package HostCMS 6\Hostcms_Bitrix24
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Hostcms_Bitrix24_Controller extends Core_Servant_Properties
{
	/**
	 * Main config array
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Constructor.
	 * @param Site_Model $oSite Site object
	 */
	public function __construct(Site_Model $oSite)
	{
		parent::__construct();

		$aConfig = Core_Config::instance()->get('hostcms_bitrix24_config', array());

		if (isset($aConfig[$oSite->id]))
		{
			$this->_config = $aConfig[$oSite->id];
		}
		else
		{
			throw new Core_Exception("B24: Config for current site doesn`t exist!");
		}
	}

	/*
	 * CURL send data
	 * @param string $method REST API method
	 * @param array $query query array
	 * @return array|NULL
	 */
	protected function _sendData($method, $query)
	{
		if (isset($this->_config['http'])
			&& strlen($this->_config['http'])
			&& isset($this->_config['secret'])
			&& strlen($this->_config['secret'])
			&& isset($this->_config['bitrix_user'])
			&& strlen($this->_config['bitrix_user'])
		)
		{
			$url = 'https://' . $this->_config['http'] . '/rest/' . $this->_config['bitrix_user'] . '/' . $this->_config['secret'];

			try {
				$oCore_Http = Core_Http::instance('curl')
					->clear()
					->url($url . '/' . $method . '/')
					->method('POST')
					->rawData(http_build_query($query))
					->timeout(5)
					->execute();

				return json_decode($oCore_Http->getBody(), TRUE);
			} catch (Exception $e) {
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write($e->getMessage());
			}
		}
		else
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write('B24: Config Options Not Set');
		}

		return NULL;
	}

	/**
	 * Export siteusers
	 * @param Siteuser_Model $oSiteuser Siteuser object
	 */
	public function siteuser(Siteuser_Model $oSiteuser)
	{
		if (!is_null($oSiteuser) && $this->_config['siteuser'])
		{
			$queryData = array(
				'fields' => array(
					'STATUS_ID' => 'NEW',
					'OPENED' => 'Y',
					'SOURCE_ID' => 'WEB',
				),
				'params' => array(
					'REGISTER_SONET_EVENT' => 'Y'
				)
			);

			$aSocialTypes = array(
				'ВКонтакте' => 'VK',
				'Facebook' => 'FACEBOOK',
				'Instagram' => 'INSTAGRAM',
				'Skype' => 'SKYPE',
				'Viber' => 'VIBER',
				'Telegram' => 'TELEGRAM',
				'ICQ' => 'ICQ'
			);

			$bCompany = $bPerson = FALSE;

			$aSiteuser_People = $oSiteuser->Siteuser_People->findAll();
			if (isset($aSiteuser_People[0]))
			{
				$oSiteuser_Person = $aSiteuser_People[0];

				$queryData['fields']['NAME'] = strval($oSiteuser_Person->name);
				$queryData['fields']['SECOND_NAME'] = strval($oSiteuser_Person->patronymic);
				$queryData['fields']['LAST_NAME'] = strval($oSiteuser_Person->surname);

				// Телефоны
				$aDirectory_Phones = $oSiteuser_Person->Directory_Phones->findAll(FALSE);
				foreach ($aDirectory_Phones as $oDirectory_Phone)
				{
					$queryData['fields']['PHONE'][] = array('VALUE' => $oDirectory_Phone->value, 'VALUE_TYPE' => 'WORK');
				}

				// E-mail
				$aDirectory_Emails = $oSiteuser_Person->Directory_Emails->findAll(FALSE);
				foreach ($aDirectory_Emails as $oDirectory_Email)
				{
					$queryData['fields']['EMAIL'][] = array('VALUE' => $oDirectory_Email->value, 'VALUE_TYPE' => 'WORK');
				}

				// Мессенджеры
				$aDirectory_Messengers = $oSiteuser_Person->Directory_Messengers->findAll(FALSE);
				foreach ($aDirectory_Messengers as $oDirectory_Messenger)
				{
					$valueType = isset($aSocialTypes[$oDirectory_Messenger->Directory_Messenger_Type->name])
						? $aSocialTypes[$oDirectory_Messenger->Directory_Messenger_Type->name]
						: 'OTHER';

					$queryData['fields']['IM'][] = array('VALUE' => $oDirectory_Messenger->value, 'VALUE_TYPE' => $valueType);
				}

				// Соц.сети
				$aDirectory_Socials = $oSiteuser_Person->Directory_Socials->findAll(FALSE);
				foreach ($aDirectory_Socials as $oDirectory_Social)
				{
					$valueType = isset($aSocialTypes[$oDirectory_Social->Directory_Social_Type->name])
						? $aSocialTypes[$oDirectory_Social->Directory_Social_Type->name]
						: 'OTHER';

					$queryData['fields']['IM'][] = array('VALUE' => $oDirectory_Social->value, 'VALUE_TYPE' => $valueType);
				}

				// Сайты
				$aDirectory_Websites = $oSiteuser_Person->Directory_Websites->findAll(FALSE);
				foreach ($aDirectory_Websites as $oDirectory_Website)
				{
					$queryData['fields']['WEB'][] = array('VALUE' => $oDirectory_Website->value, 'VALUE_TYPE' => 'WORK');
				}

				$queryData['fields']['BIRTHDATE'] = date('c', Core_Date::sql2timestamp($oSiteuser_Person->birthday));

				// Должность
				$queryData['fields']['POST'] = strval($oSiteuser_Person->post);

				// Обращение (0 - мужской, 1 - женский)
				$queryData['fields']['HONORIFIC'] = $oSiteuser_Person->sex == 0
					? 'HNR_RU_1'
					: 'HNR_RU_2';

				// Адрес клиента
				$queryData['fields']['ADDRESS_POSTAL_CODE'] = strval($oSiteuser_Person->postcode);
				$queryData['fields']['ADDRESS_COUNTRY'] = strval($oSiteuser_Person->country);
				$queryData['fields']['ADDRESS_CITY'] = strval($oSiteuser_Person->city);
				$queryData['fields']['ADDRESS'] = strval($oSiteuser_Person->address);

				$bPerson = TRUE;
			}

			$aSiteuser_Companies = $oSiteuser->Siteuser_Companies->findAll();
			if (isset($aSiteuser_Companies[0]))
			{
				$oSiteuser_Company = $aSiteuser_Companies[0];

				$queryData['fields']['COMPANY_TITLE'] = strval($oSiteuser_Company->name);

				$bCompany = TRUE;
			}

			if ($bPerson)
			{
				$title = $oSiteuser_Person->getFullName();
			}
			elseif ($bCompany)
			{
				$title = $oSiteuser_Company->name;

				$aDirectory_Addresses = $oSiteuser_Company->Directory_Addresses->findAll(FALSE);
				if (isset($aDirectory_Addresses[0]))
				{
					$oDirectory_Address = $aDirectory_Addresses[0];

					// Адрес компании
					$queryData['fields']['ADDRESS_POSTAL_CODE'] = strval($oDirectory_Address->postcode);
					$queryData['fields']['ADDRESS_COUNTRY'] = strval($oDirectory_Address->country);
					$queryData['fields']['ADDRESS_CITY'] = strval($oDirectory_Address->city);
					$queryData['fields']['ADDRESS'] = strval($oDirectory_Address->value);
				}

				// Телефоны
				$aDirectory_Phones = $oSiteuser_Company->Directory_Phones->findAll(FALSE);
				foreach ($aDirectory_Phones as $oDirectory_Phone)
				{
					$queryData['fields']['PHONE'][] = array('VALUE' => $oDirectory_Phone->value, 'VALUE_TYPE' => 'WORK');
				}

				// E-mail
				$aDirectory_Emails = $oSiteuser_Company->Directory_Emails->findAll(FALSE);
				foreach ($aDirectory_Emails as $oDirectory_Email)
				{
					$queryData['fields']['EMAIL'][] = array('VALUE' => $oDirectory_Email->value, 'VALUE_TYPE' => 'WORK');
				}

				// Мессенджеры
				$aDirectory_Messengers = $oSiteuser_Company->Directory_Messengers->findAll(FALSE);
				foreach ($aDirectory_Messengers as $oDirectory_Messenger)
				{
					$valueType = isset($aSocialTypes[$oDirectory_Messenger->Directory_Messenger_Type->name])
						? $aSocialTypes[$oDirectory_Messenger->Directory_Messenger_Type->name]
						: 'OTHER';

					$queryData['fields']['IM'][] = array('VALUE' => $oDirectory_Messenger->value, 'VALUE_TYPE' => $valueType);
				}

				// Соц.сети
				$aDirectory_Socials = $oSiteuser_Company->Directory_Socials->findAll(FALSE);
				foreach ($aDirectory_Socials as $oDirectory_Social)
				{
					$valueType = isset($aSocialTypes[$oDirectory_Social->Directory_Social_Type->name])
						? $aSocialTypes[$oDirectory_Social->Directory_Social_Type->name]
						: 'OTHER';

					$queryData['fields']['IM'][] = array('VALUE' => $oDirectory_Social->value, 'VALUE_TYPE' => $valueType);
				}

				// Сайты
				$aDirectory_Websites = $oSiteuser_Company->Directory_Websites->findAll(FALSE);
				foreach ($aDirectory_Websites as $oDirectory_Website)
				{
					$queryData['fields']['WEB'][] = array('VALUE' => $oDirectory_Website->value, 'VALUE_TYPE' => 'WORK');
				}
			}
			else
			{
				$title = $oSiteuser->login;
			}

			$queryData['fields']['TITLE'] = strval($title);

			// $aResult['result'] - содержит ID нового лида в Битрикс24
			$aResult = $this->_sendData('crm.lead.add', $queryData);

			if (isset($aResult['error']))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('siteuser() error: ' . $aResult['error_description']);
			}

			if (isset($aResult['result']))
			{
				// Сохраняем связь пользователя сайта HostCMS и лида в Битрикс24
				$oHostcms_Bitrix24_Siteuser = Core_Entity::factory('Hostcms_Bitrix24_Siteuser');
				$oHostcms_Bitrix24_Siteuser
					->siteuser_id($oSiteuser->id)
					->lead_id(intval($aResult['result']))
					->save();
			}
		}
	}

	/**
	 * Create new deal by shop order
	 * @param Shop_Order_Model $oShop_Order Shop order object
	 * @return self
	 * @hostcms-event Hostcms_Bitrix24_Controller.onNewShopOrder
	 */
	public function newShopOrder(Shop_Order_Model $oShop_Order)
	{
		if (!is_null($oShop_Order) && $this->_config['shop'])
		{
			$queryData = array(
				'fields' => array(
					'TYPE_ID' => 'GOODS', // Продажа товара
					'STAGE_ID' => $this->_config['new_order'],
					'TITLE' => Core::_('Hostcms_Bitrix24.shop_order', $oShop_Order->invoice),
					'OPPORTUNITY' => $oShop_Order->getAmount(),
					'CURRENCY_ID' => $oShop_Order->Shop_Currency->code,
					'PROBABILITY' => 50,
				),
				'params' => array(
					'REGISTER_SONET_EVENT' => 'Y'
				)
			);

			$comment = '';

			if ($oShop_Order->shop_payment_system_id)
			{
				$comment .= Core::_('Hostcms_Bitrix24.order_paymentsystem', $oShop_Order->Shop_Payment_System->name) . "\r\n";
			}

			if ($oShop_Order->shop_delivery_id)
			{
				$comment .= Core::_('Hostcms_Bitrix24.shop_delivery', $oShop_Order->Shop_Delivery->name);

				if ($oShop_Order->shop_delivery_condition_id)
				{
					$comment .= " ({$oShop_Order->Shop_Delivery_Condition->name})";
				}

				$comment .= "\r\n";
			}

			if (strlen($oShop_Order->description))
			{
				$comment .= Core::_('Hostcms_Bitrix24.shop_order_description', $oShop_Order->description) . "\r\n";
			}

			$queryData['fields']['COMMENTS'] = nl2br(trim($comment));

			// Контакт сделки
			$iContactId = $this->_getContactId($oShop_Order);
			if (!is_null($iContactId))
			{
				$queryData['fields']['CONTACT_ID'] = $iContactId;
			}

			Core_Event::notify(get_class($this) . '.onNewShopOrder', $this, array($queryData, $oShop_Order));

			$aReturn = Core_Event::getLastReturn();

			is_array($aReturn) && count($aReturn) && $queryData = $aReturn;

			// Создаем сделку
			$aResult = $this->_sendData('crm.deal.add', $queryData);

			if (isset($aResult['error']))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('newShopOrder() error: ' . $aResult['error_description']);
			}

			// $aResult['result'] - содержит ID новой сделки в Битрикс24
			if (isset($aResult['result']))
			{
				$iDealId = intval($aResult['result']);

				// Сохраняем связь заказа HostCMS и сделки в Битрикс24
				$oHostcms_Bitrix24_Order = Core_Entity::factory('Hostcms_Bitrix24_Order');
				$oHostcms_Bitrix24_Order
					->shop_order_id($oShop_Order->id)
					->deal_id($iDealId)
					->save();

				$queryProductsData = array(
					'id' => $iDealId,
					'rows' => array(),
				);

				$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll(FALSE);
				foreach ($aShop_Order_Items as $oShop_Order_Item)
				{
					$iProductId = $this->_getProductId($oShop_Order_Item);

					// Добавляем товар, если такого нет в списке товаров Битрикс24
					if (!is_null($iProductId))
					{
						$queryProductsData['rows'][] = array(
							'PRODUCT_ID' => $iProductId,
							'QUANTITY' =>$oShop_Order_Item->quantity,
							'PRICE' => $oShop_Order_Item->price + $oShop_Order_Item->price * ($oShop_Order_Item->rate / 100),
							'TAX_RATE' => $oShop_Order_Item->rate,
							'TAX_INCLUDED' => 'N',
						);
					}
				}

				// Связываем созданный товар со сделкой
				$aResult = $this->_sendData('crm.deal.productrows.set', $queryProductsData);

				if (isset($aResult['error']))
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write("newShopOrder(), set product {$oShop_Order_Item->name} error: " . $aResult['error_description']);
				}
			}
		}

		return $this;
	}

	/**
	 * Update deal when shop order paid
	 * @param Shop_Order_Model $oShop_Order Shop order object
	 */
	public function paidShopOrder(Shop_Order_Model $oShop_Order)
	{
		if (!is_null($oShop_Order) && $this->_config['shop'])
		{
			$oHostcms_Bitrix24_Order = Core_Entity::factory('Hostcms_Bitrix24_Order')->getByShop_order_id($oShop_Order->id);

			if (!is_null($oHostcms_Bitrix24_Order))
			{
				$queryData = array(
					'id' => $oHostcms_Bitrix24_Order->deal_id,
					'fields' => array(
						'STAGE_ID' => $this->_config['paid_order'],
						'PROBABILITY' => 100,
					),
					'params' => array(
						'REGISTER_SONET_EVENT' => 'Y'
					)
				);

				$aResult = $this->_sendData('crm.deal.update', $queryData);

				if (isset($aResult['error']))
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write('paidShopOrder() error: ' . $aResult['error_description']);
				}
			}
		}
	}

	/**
	 * Update deal when shop order cancel
	 * @param Shop_Order_Model $oShop_Order Shop order object
	 */
	public function cancelShopOrder(Shop_Order_Model $oShop_Order)
	{
		if (!is_null($oShop_Order) && $this->_config['shop'])
		{
			$oHostcms_Bitrix24_Order = Core_Entity::factory('Hostcms_Bitrix24_Order')->getByShop_order_id($oShop_Order->id);

			if (!is_null($oHostcms_Bitrix24_Order))
			{
				$queryData = array(
					'id' => $oHostcms_Bitrix24_Order->deal_id,
					'fields' => array(
						'STAGE_ID' => $this->_config['canceled_order'],
						'PROBABILITY' => 0,
					),
					'params' => array(
						'REGISTER_SONET_EVENT' => 'Y'
					)
				);

				$aResult = $this->_sendData('crm.deal.update', $queryData);

				if (isset($aResult['error']))
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write('cancelShopOrder() error: ' . $aResult['error_description']);
				}
			}
		}
	}

	/*
	 * Get product ID by name
	 * @param Shop_Order_Item_Model $oShop_Order_Item shop order item object
	 * @return int|NULL
	 */
	protected function _getProductId(Shop_Order_Item_Model $oShop_Order_Item)
	{
		$queryData = array(
			'order' => array(
				'ID' => 'ASC'
			),
			'filter' => array (
				'NAME' => $oShop_Order_Item->name,
			),
			'select' => array('ID', 'NAME')
		);

		$aResult = $this->_sendData('crm.product.list', $queryData);

		if (isset($aResult['result'][0]))
		{
			return intval($aResult['result'][0]['ID']);
		}
		else
		{
			$currency = $oShop_Order_Item->Shop_Order->Shop_Currency->code;

			$queryData = array(
				'fields' => array(
					'NAME' => $oShop_Order_Item->name,
					'CURRENCY_ID' => $currency,
					'PRICE' => $oShop_Order_Item->price
				)
			);

			if ($oShop_Order_Item->rate)
			{
				$iVatId = $this->_getVatId($oShop_Order_Item->rate);

				if (!is_null($iVatId))
				{
					$queryData['fields']['VAT_ID'] = $iVatId;
				}
			}

			if ($oShop_Order_Item->shop_item_id)
			{
				$oShop_Measure = $oShop_Order_Item->Shop_Item->shop_measure_id
					? $oShop_Order_Item->Shop_Item->Shop_Measure
					: $oShop_Order_Item->Shop_Order->Shop->Shop_Measure;

				$iMeasureId = $this->_getMeasureId($oShop_Measure);

				if (!is_null($iMeasureId))
				{
					$queryData['fields']['MEASURE'] = $iMeasureId;
				}
			}

			$aResult = $this->_sendData('crm.product.add', $queryData);

			if (isset($aResult['result']))
			{
				return intval($aResult['result']);
			}

			if (isset($aResult['error']))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write("getProductId(), add {$oShop_Order_Item->name} error: " . $aResult['error_description']);

				return NULL;
			}
		}

		return NULL;
	}

	/*
	 * Get company ID by name
	 * @param Shop_Order_Model $oShop_Order shop order object
	 * @return int|NULL
	 */
	protected function _getCompanyId(Shop_Order_Model $oShop_Order)
	{
		$queryData = array(
			'order' => array(
				'ID' => 'ASC'
			),
			'filter' => array (
				'TITLE' => $oShop_Order->company,
			),
			'select' => array('ID', 'TITLE')
		);

		$aResult = $this->_sendData('crm.company.list', $queryData);

		if (isset($aResult['result'][0]))
		{
			return intval($aResult['result'][0]['ID']);
		}
		else
		{
			$queryCompanyData = array(
				'fields' => array(
					'COMPANY_TYPE' => 'CUSTOMER',
					'TITLE' => $oShop_Order->company,
					'INDUSTRY' => 'OTHER',
				)
			);


			if (strlen($oShop_Order->phone))
			{
				$queryCompanyData['fields']['PHONE'] = array(
					array('VALUE' => $oShop_Order->phone, 'VALUE_TYPE' => 'WORK')
				);
			}

			if (strlen($oShop_Order->email))
			{
				$queryCompanyData['fields']['EMAIL'] = array(
					array('VALUE' => $oShop_Order->email, 'VALUE_TYPE' => 'WORK')
				);
			}

			$aResult = $this->_sendData('crm.company.add', $queryCompanyData);

			if (isset($aResult['result']))
			{
				 return intval($aResult['result']);
			}

			if (isset($aResult['error']))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('getCompanyId() error: ' . $aResult['error_description']);

				return NULL;
			}
		}

		return NULL;
	}

	/*
	 * Get contact ID by email
	 * @param Shop_Order_Model $oShop_Order shop order object
	 * @return int|NULL
	 */
	protected function _getContactId(Shop_Order_Model $oShop_Order)
	{
		$filter = array('EMAIL' => $oShop_Order->email);
		!strlen(trim($oShop_Order->email)) && $filter = array('PHONE' => $oShop_Order->phone);

		$queryData = array(
			'order' => array(
				'ID' => 'ASC'
			),
			/*'filter' => array (
				'EMAIL' => $oShop_Order->email,
			),*/
			'filter' => $filter,
			'select' => array('ID', 'NAME', 'LAST_NAME')
		);

		$aResult = $this->_sendData('crm.contact.list', $queryData);

		if (isset($aResult['result'][0]))
		{
			return intval($aResult['result'][0]['ID']);
		}
		else
		{
			// Добавляем контакт, если такого нет в списке контактов Битрикс24
			$queryContactData = array(
				'fields' => array(
					'STATUS_ID' => 'NEW',
					'OPENED' => 'Y',
					'TYPE_ID' => 'CLIENT',
					'SOURCE_ID' => 'WEB',
					'NAME' => $oShop_Order->name,
					'SECOND_NAME' => $oShop_Order->patronymic,
					'LAST_NAME' => $oShop_Order->surname
				),
				'params' => array(
					'REGISTER_SONET_EVENT' => 'Y'
				)
			);

			if (strlen($oShop_Order->phone))
			{
				$queryContactData['fields']['PHONE'] = array(
					array('VALUE' => $oShop_Order->phone, 'VALUE_TYPE' => 'WORK')
				);
			}

			if (strlen($oShop_Order->email))
			{
				$queryContactData['fields']['EMAIL'] = array(
					array('VALUE' => $oShop_Order->email, 'VALUE_TYPE' => 'WORK')
				);
			}

			$aResult = $this->_sendData('crm.contact.add', $queryContactData);

			if (isset($aResult['result']))
			{
				if (strlen($oShop_Order->company))
				{
					$iConpanyId = $this->_getCompanyId($oShop_Order);

					if (!is_null($iConpanyId))
					{
						$queryAddCompanyData = array(
							'id' => intval($aResult['result']),
							'fields' => array(
								'COMPANY_ID' => $iConpanyId
							)
						);

						$this->_sendData('crm.contact.company.add', $queryAddCompanyData);
					}
				}

				 return intval($aResult['result']);
			}

			if (isset($aResult['error']))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('getContactId() error: ' . $aResult['error_description']);

				return NULL;
			}
		}

		return NULL;
	}

	/**
	 * Get VAT id by rate
	 * @param int $rate shop tax rate
	 * @return int|NULL
	 */
	protected function _getVatId($rate)
	{
		$queryData = array(
			'order' => array(
				'ID' => 'ASC'
			),
			'filter' => array(
				'RATE' => $rate,
			),
			'select' => array('ID', 'NAME', 'RATE')
		);

		$aResult = $this->_sendData('crm.vat.list', $queryData);

		if (isset($aResult['result'][0]))
		{
			return intval($aResult['result'][0]['ID']);
		}
		else
		{
			$queryData = array(
				'fields' => array(
					'ACTIVE' => 'Y',
					'NAME' => Core::_('Hostcms_Bitrix24.tax', $rate),
					'RATE' => $rate
				)
			);

			$aResult = $this->_sendData('crm.vat.add', $queryData);

			if (isset($aResult['result']))
			{
				return intval($aResult['result']);
			}

			if (isset($aResult['error']))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('getVatId() error: ' . $aResult['error_description']);

				return NULL;
			}
		}

		return NULL;
	}

	/**
	 * Get measure id
	 * @param Shop_Measure_Model $oShop_Measure shop measure object
	 * @return int|NULL
	 */
	protected function _getMeasureId(Shop_Measure_Model $oShop_Measure)
	{
		$measure_id = NULL;

		$queryData = array(
			'order' => array(
				'ID' => 'ASC'
			),
			'select' => array('ID', 'CODE', 'SYMBOL_RUS')
		);

		$aResult = $this->_sendData('crm.measure.list', $queryData);

		if (isset($aResult['result']))
		{
			foreach ($aResult['result'] as $aData)
			{
				if ($aData['SYMBOL_RUS'] == $oShop_Measure->name)
				{
					$measure_id = intval($aData['ID']);

					break;
				}
			}

			if (is_null($measure_id))
			{
				// Создаем единицу измерения
				$queryData = array(
					'fields' => array(
						'MEASURE_TITLE' => $oShop_Measure->description,
						'SYMBOL_RUS' => $oShop_Measure->name,
						'CODE' => $oShop_Measure->okei,
						'IS_DEFAULT' => 'N'
					)
				);

				$aResult = $this->_sendData('crm.measure.add', $queryData);

				if (isset($aResult['result']))
				{
					$measure_id = intval($aResult['result']);
				}

				if (isset($aResult['error']))
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write('getMeasureId() error: ' . $aResult['error_description']);

					$measure_id = NULL;
				}
			}
		}

		return $measure_id;
	}

	/**
	 * Export form
	 * @param Form_Fill_Model $oForm_Fill Form fill object
	 */
	public function formFill(Form_Fill_Model $oForm_Fill)
	{
		if (!is_null($oForm_Fill) && $this->_config['form'])
		{
			$aAvailableFields = array(
				'name' => 'NAME',
				'surname' => 'LAST_NAME',
				'patronymic' => 'SECOND_NAME',
				'text' => 'COMMENTS',
				'email' => 'EMAIL',
				'phone' => 'PHONE'
			);

			$comment = '';

			$queryData = array(
				'fields' => array(
					'STATUS_ID' => 'NEW',
					'OPENED' => 'Y',
					'SOURCE_ID' => 'WEBFORM',
					'TITLE' => Core::_('Hostcms_Bitrix24.form_title', $oForm_Fill->Form->name)
				),
				'params' => array(
					'REGISTER_SONET_EVENT' => 'Y'
				)
			);

			$aForm_Fill_Fields = $oForm_Fill->Form_Fill_Fields->findAll();
			foreach ($aForm_Fill_Fields as $oForm_Fill_Field)
			{
				$oForm_Field = $oForm_Fill_Field->Form_Field;

				if (array_key_exists($oForm_Field->name, $aAvailableFields))
				{
					if ($oForm_Field->name == 'email' || $oForm_Field->name == 'phone')
					{
						$queryData['fields'][$aAvailableFields[$oForm_Field->name]] = array(
							array('VALUE' => $oForm_Fill_Field->value, 'VALUE_TYPE' => 'WORK')
						);
					}
					else
					{
						$queryData['fields'][$aAvailableFields[$oForm_Field->name]] = $oForm_Fill_Field->value;
					}
				}
				else
				{
					$comment .= "{$oForm_Field->caption}: {$oForm_Fill_Field->value}\r\n";
				}
			}

			$queryData['fields']['COMMENTS'] = nl2br(trim($comment));

			$aResult = $this->_sendData('crm.lead.add', $queryData);

			if (isset($aResult['error']))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$ERROR)
					->write('formFill() error: ' . $aResult['error_description']);
			}

			if (isset($aResult['result']))
			{
				// Сохраняем связь заполненой формы HostCMS и лида в Битрикс24
				$oHostcms_Bitrix24_Form = Core_Entity::factory('Hostcms_Bitrix24_Form');
				$oHostcms_Bitrix24_Form
					->form_fill_id($oForm_Fill->id)
					->lead_id(intval($aResult['result']))
					->save();
			}
		}
	}

	/**
	 * Debug
	 */
	protected function _debug($data)
	{
		echo "<pre>";
		var_dump($data);
		echo "</pre>";
	}
}