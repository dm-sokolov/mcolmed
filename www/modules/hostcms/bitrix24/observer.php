<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
* Hostcms_Bitrix24_Observer
*
* @package HostCMS 6
* @version 6.x
* @author Hostmake LLC
* @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
*/
class Hostcms_Bitrix24_Observer
{
	// Заказ поступил
	static public function onAfterProcessOrder($object, $args)
	{
		$oShop_Order = $object->getShopOrder();

		$Hostcms_Bitrix24_Controller = new Hostcms_Bitrix24_Controller($oShop_Order->Shop->Site);
		$Hostcms_Bitrix24_Controller->newShopOrder($oShop_Order);
	}

	// Оплачен
	static public function onBeforePaid($object, $args)
	{
		if (!$object->paid)
		{
			$Hostcms_Bitrix24_Controller = new Hostcms_Bitrix24_Controller($object->Shop->Site);
			$Hostcms_Bitrix24_Controller->paidShopOrder($object);
		}
	}

	// Отменен, в свойствах объекта уже измененные данные
	static public function onAfterChangedOrder($object, $args)
	{
		$oShop_Order = $object->getShopOrder();

		if (!$oShop_Order->paid
			&& $oShop_Order->canceled
			&& $args[0] == 'cancelPaid'
		)
		{
			$Hostcms_Bitrix24_Controller = new Hostcms_Bitrix24_Controller($oShop_Order->Shop->Site);
			$Hostcms_Bitrix24_Controller->cancelShopOrder($oShop_Order);
		}
	}

	// Форма заполнена
	static public function onAfterProcess($object, $args)
	{
		$oForm_Fill = $args[0];

		$Hostcms_Bitrix24_Controller = new Hostcms_Bitrix24_Controller($oForm_Fill->Form->Site);
		$Hostcms_Bitrix24_Controller->formFill($oForm_Fill);
	}

	// Форма редактирования заказа
	static public function onAfterRedeclaredPrepareForm($controller, $args)
	{
		list($object, $Admin_Form_Controller) = $args;

		// Данное событие будет вызываться для всех форм, определяем с каким контроллером работаем
		switch (get_class($controller))
		{
			case 'Shop_Order_Controller_Edit':

				// Новый заказ или не был выгружен ранее
				if (!$object->id
					|| Core_Entity::factory('Hostcms_Bitrix24_Order')->getCountByshop_order_id($object->id) == 0)
				{
					$oMainTab = $controller->getTab('main');
					$oMainTab->add($oB24Row = Admin_Form_Entity::factory('Div')->class('row'));

					$oB24Row->add(
						Admin_Form_Entity::factory('Checkbox')
							->divAttr(array('class' => 'form-group col-xs-12 col-md-6'))
							->name('b24_export_order')
							->value(1)
							->checked(FALSE)
							->caption(Core::_('Hostcms_Bitrix24.export_shop_order'))
					);
				}
			break;
		}
	}

	// Обработка формы редактирования заказа
	static public function onAfterRedeclaredApplyObjectProperty($controller, $args)
	{
		list($Admin_Form_Controller) = $args;

		if (!is_null(Core_Array::getPost('b24_export_order')))
		{
			$oShop_Order = $controller->getObject();

			// Заказ не был выгружен ранее
			if (Core_Entity::factory('Hostcms_Bitrix24_Order')->getCountByshop_order_id($oShop_Order->id) == 0)
			{
				$Hostcms_Bitrix24_Controller = new Hostcms_Bitrix24_Controller($oShop_Order->Shop->Site);
				$Hostcms_Bitrix24_Controller->newShopOrder($oShop_Order);
			}
		}
	}
}