<?php
@ini_set('display_errors', 1);
error_reporting(E_ALL);
@set_time_limit(90000);

// Временная директория
$sTemporaryDirectory = CMS_FOLDER . TMP_DIR . "1c_exchange_files/";

// Магазин для выгрузки
$oShop = Core_Entity::factory('Shop')->find(Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

// Размер блока выгружаемых данных (100000000 = 100 мБ)
$iFileLimit = 100000000;

// bugfix
usleep(10);

// Решение проблемы авторизации при PHP в режиме CGI
if (isset($_REQUEST['authorization'])
|| (isset($_SERVER['argv'][0])
			&& empty($_SERVER['PHP_AUTH_USER'])
			&& empty($_SERVER['PHP_AUTH_PW'])))
{
	$authorization_base64 = isset($_REQUEST['authorization'])
		? $_REQUEST['authorization']
		: mb_substr($_SERVER['argv'][0], 14);

	$authorization = base64_decode(mb_substr($authorization_base64, 6));
	$authorization_explode = explode(':', $authorization);

	if (count($authorization_explode) == 2)
	{
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = $authorization_explode;
	}

	unset($authorization);
}

if (!isset($_SERVER['PHP_AUTH_USER']))
{
	header('WWW-Authenticate: Basic realm="HostCMS"');
	header('HTTP/1.0 401 Unauthorized');
	exit;
}
elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
{
	$answr = Core_Auth::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

	Core_Auth::setCurrentSite();

	$oUser = Core_Entity::factory('User')->getByLogin(
		$_SERVER['PHP_AUTH_USER']
	);

	if ($answr !== TRUE || !is_null($oUser) && $oUser->read_only)
	{
		// авторизация не пройдена
		exit('Authentication failed!');
	}
}
else
{
	exit();
}

if (!is_null($sType = Core_Array::getGet('type'))
	&& ($sType == 'catalog' || $sType == 'sale')
	&& !is_null($sMode = Core_Array::getGet('mode'))
	&& $sMode == 'checkauth')
{
	// Удаляем файлы предыдущего сеанса
	Core_File::DeleteDir($sTemporaryDirectory);

	// Генерируем Guid сеанса обмена
	$sGUID = Core_Guid::get();
	setcookie("1c_exchange", $sGUID);
	echo sprintf("\xEF\xBB\xBFsuccess\n1c_exchange\n%s", $sGUID);
}
elseif (!is_null($sType = Core_Array::getGet('type'))
	&& ($sType == 'catalog' || $sType == 'sale')
	&& !is_null($sMode = Core_Array::getGet('mode'))
	&& $sMode == 'init')
{
	echo sprintf("\xEF\xBB\xBFzip=no\nfile_limit=%s", $iFileLimit);
}
elseif (!is_null($sType = Core_Array::getGet('type'))
	&& $sType == 'catalog'
	&& !is_null($sMode = Core_Array::getGet('mode'))
	&& $sMode == 'file' && !is_null($sFileName = Core_Array::getGet('filename')))
{
	$sFullFileName = $sTemporaryDirectory.$sFileName;
	Core_File::mkdir(dirname($sFullFileName), CHMOD, TRUE);
	if (file_put_contents($sFullFileName, file_get_contents("php://input")) && @chmod($sFullFileName, CHMOD_FILE))
	{
		echo "\xEF\xBB\xBFsuccess";
	}
	else
	{
		echo sprintf("\xEF\xBB\xBFfailure\nCan't save incoming data to file: $sFullFileName");
	}
}
elseif (!is_null($sType = Core_Array::getGet('type'))
	&& $sType == 'catalog'
	&& !is_null($sMode = Core_Array::getGet('mode'))
	&& $sMode == 'import' && !is_null($sFileName = Core_Array::getGet('filename')))
{
	try
	{
		$oShop_Item_Import_Cml_Controller = new Shop_Item_Import_Cml_Controller($sTemporaryDirectory.$sFileName);
		$oShop_Item_Import_Cml_Controller->iShopId = $oShop->id;
		$oShop_Item_Import_Cml_Controller->iShopGroupId = 0;
		$oShop_Item_Import_Cml_Controller->sPicturesPath = $sTemporaryDirectory;
		$oShop_Item_Import_Cml_Controller->importAction = 1;
		$oShop_Item_Import_Cml_Controller->sShopDefaultPriceName = 'Розничная';
		$oShop_Item_Import_Cml_Controller->import();
		echo "\xEF\xBB\xBFsuccess";
	}
	catch(Exception $exc)
	{
		echo sprintf("\xEF\xBB\xBFfailure\n%s", $exc->getMessage());
	}
}
elseif (!is_null($sType = Core_Array::getGet('type'))
	&& $sType == 'sale'
	&& !is_null($sMode = Core_Array::getGet('mode'))
	&& $sMode == 'query')
{
	$oXml = new Core_SimpleXMLElement(sprintf(
		"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<КоммерческаяИнформация ВерсияСхемы=\"2.04\" ДатаФормирования=\"%s\"></КоммерческаяИнформация>",
		date("Y-m-d")));

	$aShopOrders = $oShop->Shop_Orders->getAllByUnloaded(0);

	foreach($aShopOrders as $oShopOrder)
	{
		$oOrderXml = $oXml->addChild('Документ');
		$oOrderXml->addChild('Ид', $oShopOrder->id);
		$oOrderXml->addChild('Номер', $oShopOrder->id);
		$datetime = explode(' ', $oShopOrder->datetime);
		$date = $datetime[0];
		$time = $datetime[1];
		$oOrderXml->addChild('Дата', $date);
		$oOrderXml->addChild('ХозОперация', 'Заказ товара');
		$oOrderXml->addChild('Роль', 'Продавец');
		$oOrderXml->addChild('Валюта', $oShopOrder->Shop_Currency->name);
		$oOrderXml->addChild('Курс', $oShopOrder->shop_currency_id > 0 && $oShop->shop_currency_id > 0 ? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency($oShopOrder->Shop_Currency, $oShop->Shop_Currency) : 0);
		$oOrderXml->addChild('Сумма', $oShopOrder->getAmount());

		$oContractor = $oOrderXml->addChild('Контрагенты');
		$oContractor = $oContractor->addChild('Контрагент');
		$oContractor->addChild('Ид', $oShopOrder->siteuser_id);
		$oContractor->addChild('Наименование', ($oShopOrder->name == '' ? 'Контрагент #'.$oShopOrder->siteuser_id : $oShopOrder->name));
		$oContractor->addChild('Роль', 'Покупатель');
		$oContractor->addChild('ПолноеНаименование', sprintf("%s %s", $oShopOrder->name,  $oShopOrder->patronymic));
		$oContractor->addChild('Фамилия', $oShopOrder->surname);
		$oContractor->addChild('Имя', $oShopOrder->name);
		$oContractor->addChild('АдресРегистрации')->addChild('Представление', $oShopOrder->address);

		$oOrderXml->addChild('Время', $time);
		$oOrderXml->addChild('Комментарий', $oShopOrder->description);

		$oOrderItems = $oOrderXml->addChild('Товары');

		$aOrderItems = $oShopOrder->Shop_Order_Items->findAll();

		foreach($aOrderItems as $oOrderItem)
		{
			$oCurrentItem = $oOrderItems->addChild('Товар');
			$oCurrentItem->addChild('Ид', $oOrderItem->type == 1 ? 'ORDER_DELIVERY' : $oOrderItem->Shop_Item->guid);
			$oCurrentItem->addChild('Наименование', $oOrderItem->name);
			$oCurrentItem->addChild('БазоваяЕдиница', $oOrderItem->Shop_Item->Shop_Measure->name);
			$oCurrentItem->addChild('ЦенаЗаЕдиницу', $oOrderItem->price);
			$oCurrentItem->addChild('Количество', $oOrderItem->quantity);
			$oCurrentItem->addChild('Сумма', $oOrderItem->price * $oOrderItem->quantity);

			$oProperty = $oCurrentItem->addChild('ЗначенияРеквизитов');
			$oValue = $oProperty->addChild('ЗначениеРеквизита');
			$oValue->addChild('Наименование', 'ВидНоменклатуры');
			$oValue->addChild('Значение', $oOrderItem->type == 1 ? 'Услуга' : 'Товар');
			$oValue = $oProperty->addChild('ЗначениеРеквизита');
			$oValue->addChild('Наименование', 'ТипНоменклатуры');
			$oValue->addChild('Значение', $oOrderItem->type == 1 ? 'Услуга' : 'Товар');
		}
	}

	header('Content-type: text/html; charset=UTF-8');
	echo "\xEF\xBB\xBF";
	echo $oXml->asXML();
}
elseif (!is_null($sType = Core_Array::getGet('type'))
	&& $sType == 'sale'
	&& !is_null($sMode = Core_Array::getGet('mode'))
	&& $sMode == 'success')
{
	$aShopOrders = $oShop->Shop_Orders->getAllByUnloaded(0);

	foreach($aShopOrders as $oShopOrder)
	{
		$oShopOrder->unloaded = 1;
		$oShopOrder->save();
	}

	echo "\xEF\xBB\xBFsuccess\n";
}
elseif (!is_null($sType = Core_Array::getGet('type'))
	&& $sType == 'sale'
	&& !is_null($sMode = Core_Array::getGet('mode'))
	&& $sMode == 'file')
{
	echo "\xEF\xBB\xBFsuccess\n";
}

die();