<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Controller_Show = new Shop_Controller_Show($oShop);

$Shop_Controller_Show
	->limit($oShop->items_on_page)
	->parseUrl();

// Обработка скачивания файла электронного товара
$guid = Core_Array::getGet('download_file');
if (strlen($guid))
{
	$oShop_Order_Item_Digital = Core_Entity::factory('Shop_Order_Item_Digital')->getByGuid($guid);

	if (!is_null($oShop_Order_Item_Digital) && $oShop_Order_Item_Digital->Shop_Order_Item->Shop_Order->shop_id == $oShop->id)
	{
		$iDay = 7;

		// Проверяем, доступна ли ссылка (Ссылка доступна в течение суток после оплаты)
		if (Core_Date::sql2timestamp($oShop_Order_Item_Digital->Shop_Order_Item->Shop_Order->payment_datetime) > time() - 24 * 60 * 60 * $iDay)
		{
			$oShop_Item_Digital = $oShop_Order_Item_Digital->Shop_Item_Digital;
			if ($oShop_Item_Digital->filename != '')
			{
				Core_File::download($oShop_Item_Digital->getFullFilePath(), $oShop_Item_Digital->filename);
				exit();
			}
		}
		else
		{
			Core_Message::show(Core::_('Shop_Order_Item_Digital.time_is_up', $iDay));
		}
	}

	Core_Page::instance()->response->status(404)->sendHeaders()->showBody();
	exit();
}

// Сравнение товаров
if (Core_Array::getRequest('compare'))
{
	$shop_item_id = intval(Core_Array::getRequest('compare'));
	
	if (Core_Entity::factory('Shop_Item', $shop_item_id)->shop_id == $oShop->id)
	{
		Core_Session::start();
		if (isset($_SESSION['hostcmsCompare'][$oShop->id][$shop_item_id]))
		{
			unset($_SESSION['hostcmsCompare'][$oShop->id][$shop_item_id]);
		}
		else
		{
			$_SESSION['hostcmsCompare'][$oShop->id][$shop_item_id] = 1;
		}
	}
	exit();
}

// Текстовая информация для указания номера страницы, например "страница"
$pageName = Core_Array::get(Core_Page::instance()->libParams, 'page')
	? Core_Array::get(Core_Page::instance()->libParams, 'page')
	: 'страница';

// Разделитель в заголовке страницы
$pageSeparator = Core_Array::get(Core_Page::instance()->libParams, 'separator')
	? Core_Page::instance()->libParams['separator']
	: ' / ';

$aTitle = array($oShop->name);
$aDescription = array($oShop->name);
$aKeywords = array($oShop->name);

if (!is_null($Shop_Controller_Show->tag) && Core::moduleIsActive('tag'))
{
	$oTag = Core_Entity::factory('Tag')->getByPath($Shop_Controller_Show->tag);
	if ($oTag)
	{
		$aTitle[] = Core::_('Shop.tag', $oTag->name);
		$aDescription[] = Core::_('Shop.tag', $oTag->name);
		$aKeywords[] = Core::_('Shop.tag', $oTag->name);
	}
}

if ($Shop_Controller_Show->group)
{
	$oShop_Group = Core_Entity::factory('Shop_Group', $Shop_Controller_Show->group);

	do {
		$aTitle[] = $oShop_Group->seo_title != ''
			? $oShop_Group->seo_title
			: $oShop_Group->name;

		$aDescription[] = $oShop_Group->seo_description != ''
			? $oShop_Group->seo_description
			: $oShop_Group->name;

		$aKeywords[] = $oShop_Group->seo_keywords != ''
			? $oShop_Group->seo_keywords
			: $oShop_Group->name;

	} while($oShop_Group = $oShop_Group->getParent());
}

if ($Shop_Controller_Show->item)
{
	$oShop_Item = Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item);

	$aTitle[] = $oShop_Item->seo_title != ''
		? $oShop_Item->seo_title
		: $oShop_Item->name;

	$aDescription[] = $oShop_Item->seo_description != ''
		? $oShop_Item->seo_description
		: $oShop_Item->name;

	$aKeywords[] = $oShop_Item->seo_keywords != ''
		? $oShop_Item->seo_keywords
		: $oShop_Item->name;
}

if ($Shop_Controller_Show->producer)
{
	$oShop_Producer = Core_Entity::factory('Shop_Producer', $Shop_Controller_Show->producer);
	$aKeywords[] = $aDescription[] = $aTitle[] = $oShop_Producer->name;
}

if ($Shop_Controller_Show->page)
{
	array_unshift($aTitle, $pageName . ' ' . ($Shop_Controller_Show->page + 1));
}

if (count($aTitle) > 1)
{
	$aTitle = array_reverse($aTitle);
	$aDescription = array_reverse($aDescription);
	$aKeywords = array_reverse($aKeywords);

	Core_Page::instance()->title(implode($pageSeparator, $aTitle));
	Core_Page::instance()->description(implode($pageSeparator, $aDescription));
	Core_Page::instance()->keywords(implode($pageSeparator, $aKeywords));
}

Core_Page::instance()->object = $Shop_Controller_Show;