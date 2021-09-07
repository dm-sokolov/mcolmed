<?php
/**
 * Lead.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'lead');

// Код формы
$iAdmin_Form_Id = 273;
$sAdminFormAction = '/admin/lead/shop/item/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

//$oAdmin_Form->show_operations = 0;

$iLeadId = intval(Core_Array::getGet('lead_id', 0));
$oLead = Core_Entity::factory('Lead', $iLeadId);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Lead_Shop_Item.lead_shop_items_title'))
	->pageTitle(Core::_('Lead_Shop_Item.lead_shop_items_title'))
	->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	);

$oAdmin_Form_Controller->addExternalReplace('{lead_id}', $oLead->id);

$oUser = Core_Auth::getCurrentUser();

if (is_null(Core_Array::getGet('hideMenu')))
{
	if ($oAdmin_Form->Admin_Form_Actions->checkAllowedActionForUser($oUser, 'edit'))
	{
		$windowId = $oAdmin_Form_Controller->getWindowId();

		if ($oLead->shop_id)
		{
			$oShop = $oLead->Shop;

			$oAdmin_Form_Controller->addEntity(
				Admin_Form_Entity::factory('Code')
					->html('
						<div class="add-event margin-bottom-20">
							<form action="/admin/lead/shop/item/index.php" method="POST">
								<div class="input-group">
									<input type="text" id="shop_item_name" name="shop_item_name" class="form-control" placeholder="' . Core::_('Lead_Shop_Item.placeholderShopItemName') . '" />
									<span class="input-group-btn bg-azure bordered-azure">
										<button id="sendForm" class="btn btn-azure" type="submit" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('addShopItem') . '">
											<i class="fa fa-check no-margin"></i>
										</button>
									</span>
									<input type="hidden" id="shop_item_id" name="shop_item_id" value="0"/>
									<input type="hidden" id="shop_item_rate" name="shop_item_rate" value="0"/>
								</div>
								<script type="text/javascript">
									$(\'#' . $windowId . ' #shop_item_name\').autocompleteShopItem({ shop_id: ' . $oShop->id . ', shop_currency_id: ' . $oShop->shop_currency_id . ' }, function(event, ui) {
										$(\'#' . $windowId . ' #shop_item_id\').val(typeof ui.item.id !== \'undefined\' ? ui.item.id : 0);
										$(\'#' . $windowId . ' #shop_item_rate\').val(typeof ui.item.rate !== \'undefined\' ? ui.item.rate : 0);
									});

									$(\'#' . $windowId . ' :input\').on(\'click\', function() { mainFormLocker.unlock() });
								</script>
							</form>
						</div>
					')
			);
		}
		else
		{
			$oAdmin_Form_Controller->addEntity(
				Admin_Form_Entity::factory('Code')
					->html('<div class="alert alert-danger">' . Core::_('Lead_Shop_Item.shop_not_select') . '</div>')
			);
		}
	}
}
else
{
	$oAdmin_Form_Controller->showOperations(FALSE);
}

// Быстрое добавление товара сделки
if ($oAdmin_Form_Controller->getAction() == 'addShopItem'
	&& strlen($sShopItemName = trim(Core_Array::getRequest('shop_item_name')))
)
{
	$iLeadId = intval(Core_Array::getRequest('lead_id'));

	if ($iLeadId && ($oLead = Core_Entity::factory('Lead')->find($iLeadId)) && !is_null($oLead->id))
	{
		$iShopItemId = intval(Core_Array::getRequest('shop_item_id'));
		$iShopItemRate = intval(Core_Array::getRequest('shop_item_rate'));

		$oShop_Item = Core_Entity::factory('Shop_Item')->find($iShopItemId);

		$oUser = Core_Auth::getCurrentUser();

		$oLeadShopItem = Core_Entity::factory('Lead_Shop_Item');

		$oLeadShopItem->lead_id	= $iLeadId;
		$oLeadShopItem->shop_item_id = !is_null($oShop_Item->id) ? $oShop_Item->id : 0;
		$oLeadShopItem->shop_currency_id = !is_null($oShop_Item->id) ? $oShop_Item->shop_currency_id : 0;
		$oLeadShopItem->name = !is_null($oShop_Item->id) ? $oShop_Item->name : $sShopItemName;
		$oLeadShopItem->quantity = 1;
		$oLeadShopItem->price = !is_null($oShop_Item->id) ? $oShop_Item->price : 0;
		$oLeadShopItem->marking	= !is_null($oShop_Item->id) ? $oShop_Item->marking : '';
		$oLeadShopItem->rate = !is_null($oShop_Item->id) ? $iShopItemRate : 0;
		$oLeadShopItem->user_id	= $oUser->id;
		$oLeadShopItem->type = !is_null($oShop_Item->id) ? $oShop_Item->type : 0;

		$oLeadShopItem->save();
	}
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

if (is_null(Core_Array::getGet('hideMenu')))
{
	// Элементы меню
	$oAdmin_Form_Entity_Menus
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Lead_Shop_Item.lead_shop_item_menu_add'))
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					//$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
					"$.modalLoad({path: '{$oAdmin_Form_Controller->getPath()}', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&lead_id={$oLead->id}', windowId: 'id_content'}); return false"
				)
		);
}

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oLead_Shop_Item_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Lead_Shop_Item_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLead_Shop_Item_Controller_Edit);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Lead_Shop_Item')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('lead_shop_items.*', array(Core_QueryBuilder::expression('ROUND((`price` +  ROUND(`price` * `rate` / 100, 2)) * `quantity`, 2)'), 'sum')))
	)
	->addCondition(
		array('where' => array('lead_shop_items.lead_id', '=', $oLead->id))
	);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();