<?php

$Shop_Cart_Controller_Show = Core_Page::instance()->object;

$oShop = $Shop_Cart_Controller_Show->getEntity();

// ------------------------------------------------
// Подготовка редиректа для PayPal
// ------------------------------------------------
if (isset($_REQUEST['paymentType']))
{
	// Получаем ID заказа
	$order_id = intval(Core_Array::getRequest('order_id'));

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (!is_null($oShop_Order->id))
	{
		// Вызов обработчика платежной системы
		Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
			->shopOrder($oShop_Order)
			->paymentProcessing();
	}
}

// ------------------------------------------------
// Подготовка редиректа для Оплаты с лицевого счета
// ------------------------------------------------
if (isset($_REQUEST['Pay']))
{
	// Получаем ID заказа
	$order_id = intval(Core_Array::getRequest('order_id'));

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (!is_null($oShop_Order->id))
	{
		// Вызов обработчика платежной системы
		Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
			->shopOrder($oShop_Order)
			->paymentProcessing();
	}
}

// ------------------------------------------------
// Обработка уведомления об оплате от RBK Money
// ------------------------------------------------
if ((isset($_POST['paymentStatus']) || isset($_GET['PayPalOrderConfirmation'])) && !isset($_REQUEST['paymentType']))
{
	// Получаем ID заказа
	$order_id = intval(Core_Array::getPost('orderId'));

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (!is_null($oShop_Order->id))
	{
		// Вызов обработчика платежной системы
		Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
			->shopOrder($oShop_Order)
			->paymentProcessing();
	}
}

// ------------------------------------------------
// Обработка уведомления об оплате от WebMoney
// ------------------------------------------------
if (isset($_POST['LMI_PAYEE_PURSE']))
{
	// Получаем ID заказа
	$order_id = intval(Core_Array::getPost('LMI_PAYMENT_NO'));

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (!is_null($oShop_Order->id))
	{
		// Вызов обработчика платежной системы
		Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
			->shopOrder($oShop_Order)
			->paymentProcessing();
	}
}

// ------------------------------------------------
// Обработка уведомления об оплате от ROBOKASSA
// ------------------------------------------------
if (isset($_REQUEST['SignatureValue']) && isset($_REQUEST['Culture']))
{
	// Получаем ID заказа
	$order_id = intval(Core_Array::getRequest('InvId'));

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (!is_null($oShop_Order->id))
	{
		// Вызов обработчика платежной системы
		Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
			->shopOrder($oShop_Order)
			->paymentProcessing();
	}
}
// ------------------------------------------------

// ------------------------------------------------
// Обработка уведомления об оплате от Authorize.net
// ------------------------------------------------
if (isset($_POST['x_response_code']))
{
	// Получаем ID заказа
	$order_id = intval(Core_Array::getPost('order_id'));

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (!is_null($oShop_Order->id))
	{
		// Вызов обработчика платежной системы
		Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
			->shopOrder($oShop_Order)
			->paymentProcessing();
	}
}

// ------------------------------------------------
// Обработка формы "Оплата через систему QIWI Кошелек"
// ------------------------------------------------
if (isset($_POST['qiwi_payment_options']))
{
	// Получаем ID заказа
	$order_id = intval(Core_Array::getPost('order_id'));

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (!is_null($oShop_Order->id))
	{
		// Вызов обработчика платежной системы
		Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
			->shopOrder($oShop_Order)
			->paymentProcessing();
		return TRUE;
	}
}

// ------------------------------------------------
// Вывод информации о статусе платежа после его совершения и перенаправления с платежной системы
// ------------------------------------------------
if (isset($_REQUEST['payment']) || (isset($_GET['action']) && ($_GET['action'] == 'PaymentSuccess' || $_GET['action'] == 'PaymentFail')))
{
	// Получаем ID заказа
	if (isset($_REQUEST['order_id']))
	{
		$order_id = intval(Core_Array::getRequest('order_id'));
	}
	//от Яндекса
	elseif(isset($_GET['orderNumber']))
	{
		$order_id = intval(Core_Array::getGet('orderNumber'));
	}
	//от IntellectMoney
	elseif(isset($_REQUEST['orderId']))
	{
		$order_id = intval(Core_Array::getGet('orderId'));
	}
	else
	{
		$order_id = intval(Core_Array::getRequest('InvId'));
	}

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (Core::moduleIsActive('siteuser'))
	{
		$siteuser_id = 0;

		$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
		if ($oSiteuser)
		{
			$siteuser_id = $oSiteuser->id;
		}
	}
	else
	{
		$siteuser_id = FALSE;
	}

	// Если заказ принадлежит текущему авторизированному пользователю
	if ($oShop_Order->siteuser_id == $siteuser_id)
	{
		if (Core_Array::getRequest('payment') == 'success' || Core_Array::getRequest('action') == 'PaymentSuccess')
		{
			?><h1>Подтверждение платежа</h1>
			<p>Спасибо, информация об оплате заказа <strong>№ <?php echo $oShop_Order->invoice?></strong>
получена.</p>
			<?php
		}
		else
		{
			?><h1>Платеж не получен</h1>
			<p>К сожалению при оплате заказа <strong>№ <?php echo $oShop_Order->invoice?></strong> произошла ошибка.</p>
			<?php
		}
	}
	// Для случаев, когда отключен модуль "Пользователи сайта"
	elseif ($siteuser_id === FALSE)
	{
		?><h1>Подтверждение платежа</h1>
		<p>Благодарим за посещение нашего магазина!</p>
		<?php
	}
	else
	{
		?><h1>Ошибка</h1>
		<p>Неверный номер заказа!</p>
		<?php
	}

	// Прерываем выполнение типовой динамической страницы
	return TRUE;
}

$xslName = Core_Array::get(Core_Page::instance()->libParams, 'cartXsl');

switch (Core_Array::getPost('recount') ? 0 : Core_Array::getPost('step'))
{
	// Адрес доставки
	case 1:
		// Сбрасываем информацию о последнем заказе
		$_SESSION['last_order_id'] = 0;

		$Shop_Address_Controller_Show = new Shop_Address_Controller_Show($oShop);

		$Shop_Address_Controller_Show->xsl(
				Core_Entity::factory('Xsl')->getByName(
					Core_Array::get(Core_Page::instance()->libParams, 'deliveryAddressXsl')
				)
			)
			->show();
	break;
	// Способ доставки
	case 2:
		$_SESSION['hostcmsOrder']['shop_country_id'] = intval(Core_Array::getPost('shop_country_id', 0));
		$_SESSION['hostcmsOrder']['shop_country_location_id'] = intval(Core_Array::getPost('shop_country_location_id', 0));
		$_SESSION['hostcmsOrder']['shop_country_location_city_id'] = intval(Core_Array::getPost('shop_country_location_city_id', 0));
		$_SESSION['hostcmsOrder']['shop_country_location_city_area_id'] = intval(Core_Array::getPost('shop_country_location_city_area_id', 0));
		$_SESSION['hostcmsOrder']['postcode'] = strval(Core_Array::getPost('postcode'));
		$_SESSION['hostcmsOrder']['address'] = strval(Core_Array::getPost('address'));
		$_SESSION['hostcmsOrder']['surname'] = strval(Core_Array::getPost('surname'));
		$_SESSION['hostcmsOrder']['name'] = strval(Core_Array::getPost('name'));
		$_SESSION['hostcmsOrder']['patronymic'] = strval(Core_Array::getPost('patronymic'));
		$_SESSION['hostcmsOrder']['company'] = strval(Core_Array::getPost('company'));
		$_SESSION['hostcmsOrder']['phone'] = strval(Core_Array::getPost('phone'));
		$_SESSION['hostcmsOrder']['fax'] = strval(Core_Array::getPost('fax'));
		$_SESSION['hostcmsOrder']['email'] = strval(Core_Array::getPost('email'));
		$_SESSION['hostcmsOrder']['description'] = strval(Core_Array::getPost('description'));

		$Shop_Delivery_Controller_Show = new Shop_Delivery_Controller_Show($oShop);

		$Shop_Delivery_Controller_Show
			->shop_country_id($_SESSION['hostcmsOrder']['shop_country_id'])
			->shop_country_location_id($_SESSION['hostcmsOrder']['shop_country_location_id'])
			->shop_country_location_city_id($_SESSION['hostcmsOrder']['shop_country_location_city_id'])
			->shop_country_location_city_area_id($_SESSION['hostcmsOrder']['shop_country_location_city_area_id'])
			->couponText(Core_Array::get(Core_Array::get($_SESSION, 'hostcmsOrder', array()), 'coupon_text'))
			->setUp()
			->xsl(
				Core_Entity::factory('Xsl')->getByName(
					Core_Array::get(Core_Page::instance()->libParams, 'deliveryXsl')
				)
			)
			->show();
	break;
	// Форма оплаты
	case 3:
		$_SESSION['hostcmsOrder']['shop_delivery_condition_id'] = intval(Core_Array::getPost('shop_delivery_condition_id', 0));

		$Shop_Payment_System_Controller_Show = new Shop_Payment_System_Controller_Show($oShop);

		$Shop_Payment_System_Controller_Show
			->xsl(
				Core_Entity::factory('Xsl')->getByName(
					Core_Array::get(Core_Page::instance()->libParams, 'paymentSystemXsl')
				)
			)
			->show();
	break;
	// Окончание оформления заказа
	case 4:
		$shop_payment_system_id = $_SESSION['hostcmsOrder']['shop_payment_system_id'] = intval(Core_Array::getPost('shop_payment_system_id', 0));

		// Если выбрана платежная система
		if ($_SESSION['hostcmsOrder']['shop_payment_system_id'])
		{
			Shop_Payment_System_Handler::factory(
				Core_Entity::factory('Shop_Payment_System', $shop_payment_system_id)
			)
			->orderParams($_SESSION['hostcmsOrder'])
			->execute();
		}
		else
		{
			?><h1>Ошибка! Не указана ни одна платежная система.</h1><?php
		}
	break;
	default:
		$Shop_Cart_Controller_Show
			->couponText(Core_Array::get(Core_Array::get($_SESSION, 'hostcmsOrder', array()), 'coupon_text'))
			->xsl(
				Core_Entity::factory('Xsl')->getByName($xslName)
			)
			->show();
}

// Блок авторизации пользователя
if (Core::moduleIsActive('siteuser'))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

	if (is_null($oSiteuser))
	{
		// Авторизация
		$Siteuser_Controller_Show = new Siteuser_Controller_Show(
			Core_Entity::factory('Siteuser')
		);

		$Siteuser_Controller_Show
			->location(Core::$url['path'])
			->xsl(
				Core_Entity::factory('Xsl')->getByName(Core_Array::get(Core_Page::instance()->libParams, 'userAuthorizationXsl'))
			)
			->show();

		// Регистрация
		$Siteuser_Controller_Show = new Siteuser_Controller_Show(
			Core_Entity::factory('Siteuser')
		);

		$Siteuser_Controller_Show->xsl(
				Core_Entity::factory('Xsl')->getByName(Core_Array::get(Core_Page::instance()->libParams, 'userRegistrationXsl'))
			)
			->location(Core::$url['path'])
			->fastRegistration(TRUE)
			->properties(TRUE)
			//->showMaillists(TRUE)
			->show();
	}
}