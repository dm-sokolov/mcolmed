<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Controller_Show = new Shop_Controller_Show($oShop);

$path = Core_Page::instance()->structure->getPath();

$Shop_Controller_Show
	->limit($oShop->items_on_page)
	->pattern($path . '(page-{page}/)')
	->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('path')
			->value($path)
	)
	->parseUrl()
	->group(FALSE);

$Shop_Controller_Show
	->shopItems()
	->queryBuilder()
	->clearOrderBy()
	->orderBy('shop_items.shop_group_id')
	->orderBy('shop_items.name');

$Shop_Controller_Show
	->shopGroups()
	->queryBuilder()
	->clearOrderBy()
	->orderBy('shop_groups.id');

$xslName = Core_Array::get(Core_Page::instance()->libParams, 'xsl');

$Shop_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->groupsMode('all')
	->itemsProperties(TRUE)
	->show();