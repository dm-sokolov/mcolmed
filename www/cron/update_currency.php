<?php 

/**
 * Обновление валют на текущий день по курсу ЦБ.
 * Внимание! Текущей валютой должен быть установлен рубль с коэффициентом пересчета 1.
 */

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

setlocale(LC_NUMERIC, 'POSIX');

$url = 'http://www.cbr.ru/scripts/XML_daily.asp';

$Core_Http = Core_Http::instance()
	->url($url)
	->port(80)
	->timeout(10)
	->execute();

$xml = $Core_Http->getBody();

$oXml = @simplexml_load_string($xml);

if (is_object($oXml))
{
	foreach ($oXml->Valute as $Valute)
	{
		$CharCode = strval($Valute->CharCode);
		
		$oCurrency = Core_Entity::factory('Shop_Currency')->getByCode($CharCode);
		if (!is_null($oCurrency))
		{
			$iNominal = intval($Valute->Nominal);
			$Value = floatval(str_replace(',', '.', strval($Valute->Value)) / $iNominal);
			$oCurrency->exchange_rate = $Value;
			$oCurrency->save();
			
			echo "Updated currency {$CharCode} rate is {$Value}\n";
		}
	}
}

echo "OK\n";