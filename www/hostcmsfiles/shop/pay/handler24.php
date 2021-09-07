<?php
/* Оплата через Яндекс.Деньги */
class Shop_Payment_System_Handler17 extends Shop_Payment_System_Handler
{
	public $_rub_currency_id = 1;
	
	/* Идентификатор магазина в системе Яндекс.Деньги. Выдается оператором системы. */
	private $_ShopID = 9999;
	
	/* Пароль магазина в системе Яндекс.Деньги. Выдается оператором системы. */
	private $_shopPassword = 'xxxx';

	private $_yandex_money_uri = 'https://demomoney.yandex.ru/eshop.xml';

	/* Номер витрины магазина в системе Яндекс.Деньги. Выдается оператором системы. */
	private $_scid = 9999;
	
	/* Код валюты */
	/*
	Возможные значения:
	643 — рубль Российской Федерации;
	10643 — тестовая валюта (демо-рублики демо-системы «Яндекс.Деньги»)
	*/
	private $_orderSumCurrencyPaycash = 643;
	
	/* Вызывается на 4-ом шаге оформления заказа*/
	public function execute()
	{
		parent::execute();

		$this->printNotification();

		return $this;
	}
	
	/* вычисление суммы товаров заказа */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_rub_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_rub_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount() );
	}
	
	protected function _processOrder()
	{
		parent::_processOrder();

		// Установка XSL-шаблонов в соответствии с настройками в узле структуры
		$this->setXSLs();

		// Отправка писем клиенту и пользователю
		$this->send();

		return $this;
	}
	
	/* обработка ответа от платёжной системы */
	public function paymentProcessing()
	{
			$this->ProcessResult();
			
			return TRUE;
	}
	
	/* оплачивает заказ */
	function ProcessResult()
	{
		$invoiceId = to_str($_POST['invoiceId']);
		
		$invoiceId = Core_Array::getPost('invoiceId');
		
		if($this->_shopOrder->system_information == '')
		{
			$this->_shopOrder->system_information = $invoiceId;
			$this->_shopOrder->save();
		}
		
		if($this->_shopOrder->system_information == $invoiceId)
		{
			/* проверяем заказ */
			$code = self::CheckOrder($_POST);
		}
		else
		{
			$code = 1000;
		}
		
		$response_params = $_POST;
		$response['requestDatetime'] = date("c");			
		/* генерируем XML ответа */
		$response = self::GenXMLResponseToYandex($response_params, $code);
		
		if(Core_Array::getPost('action', '') == 'paymentAviso' && $code == 0)
		{
			self::PayOrder($this->_shopOrder->id);
		}
		
		/* даем ответ Яндексу */
		self::SendResponseToYandex($response);
		
		return true;
	}
	
	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$Sum = $this->getSumWithCoeff();
		
		$oSiteUser = Core_Entity::factory('SiteUser')->getCurrent();
		
		?>
		<h2>Оплата через систему Яндекс.Деньги</h2>
		
		<form method="POST" action="<?php echo $this->_yandex_money_uri?>">
		<input class="wide" name="scid" value="<?php echo $this->_scid?>" type="hidden">
		<input type="hidden" name="ShopID" value="<?php echo $this->_ShopID?>">
		<input type="hidden" name="CustomerNumber" value="<?php echo (is_null($oSiteUser) ? 0 : $oSiteUser->id)?>">
		<input type="hidden" name="orderNumber" value="<?php echo $this->_shopOrder->id?>">
		<input type="hidden" name="orderSumCurrencyPaycash" value="<?php echo $this->orderSumCurrencyPaycash?>">
		<tr>
			<td>
				<table border = "1" cellspacing = "0" width = "400" bgcolor = "#FFFFFF" align = "center" bordercolor = "#000000">
					<tr>
						<td>Сумма, руб.</td>
						<td> <input type="text" name="Sum" value="<?php echo $Sum?>" readonly="readonly"> </td>
					</tr>
					<tr>
						<td>Номер заказа</td>
						<td> <input type="text" name="AccountNumber" value="<?php echo $this->_shopOrder->invoice?>" readonly="readonly"> </td>
					</tr>
				</table>
			</td>
		</tr>
		<table border="0" cellspacing="1" align="center" width="400" bgcolor="#CCCCCC" >
			<tr bgcolor="#FFFFFF">
				<td width="490"></td>
				<td width="48"><input type="submit" name = "BuyButton" value = "Submit"></td>
			</tr>
		</table>
		</form>
	<?php	
	}
	
	public function getInvoice()
	{
		return $this->getNotification();
	}
	
	/* проверяем заказ */
	private static function CheckOrder($order_params)
	{
		$site_user_id = $this->_shopOrder->siteuser_id;
		
		$Sum = $this->getSumWithCoeff();
		
		if(isset($order_params['shopId']) && isset($order_params['customerNumber']) && isset($order_params['orderSumAmount']) && isset($order_params['AccountNumber']) && isset($order_params['orderSumCurrencyPaycash']) && isset($order_params['action']) && isset($order_params['orderNumber']) && isset($order_params['orderSumBankPaycash']))
		{
			if(to_int($order_params['shopId']) == self::$ShopID && to_int($order_params['customerNumber']) == $site_user_id && to_float($order_params['orderSumAmount']) == $Sum && to_str($order_params['AccountNumber']) == $this->_shopOrder->invoice && to_int($order_params['orderSumCurrencyPaycash']) == self::$orderSumCurrencyPaycash)
			{
				$in_str = to_str($order_params['action']) . ";" . sprintf("%.2f", $Sum) . ";" . to_str(self::$orderSumCurrencyPaycash) . ";" . to_str($order_params['orderSumBankPaycash']) . ";" . to_str(self::$ShopID) . ";" . to_str($order_params['invoiceId']) . ";" . to_str($site_user_id) . ";" . to_str(self::$shopPassword);
				$hesh = strtoupper(md5($in_str));

				return (int)(!($hesh == to_str($order_params['md5'])));
			}
			elseif($order_params['action'] == 'checkOrder')
			{
				return 100;
			}
		}
		else
		{
			return 200;
		}
		return 1000;
	}
}