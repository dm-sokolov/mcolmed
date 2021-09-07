/**
 * Redirects
 *
 * @version 1.35
 * @author Eugeny Panikarowsky - evgenii_panikaro@mail.ru
 * @copyright © 2018 Eugeny Panikarowsky
 *
*/

function getShopItems(windowId, group) {

	$.ajaxRequest({
		path: '/admin/hostdev/redirect/index.php',
		context: 'selshop_item_id',
		callBack: $.loadSelectOptionsCallback,
		action: 'loadShopItemList',
		additionalParams: 'shop_group_id=' + group + '&shop_id=' + $('#selshop_id').val(),
		windowId: windowId
	});
	return false;
}

function getInformationItems(windowId, group) {
	$.ajaxRequest({
		path: '/admin/informationsystem/item/index.php',
		context: 'selinformationsystem_item_id',
		callBack: $.loadSelectOptionsCallback,
		action: 'loadInformationItemList',
		additionalParams: 'informationsystem_group_id=' + group + '&informationsystem_id=' + $('#selinformationsystem_id').val(),
		windowId: windowId
	});
	return false;
}

function getInformationSystems(windowId, system_id) {
	$.ajaxRequest({
		path: '/admin/hostdev/redirect/index.php',
		context: 'selinformationsystem_group_id',
		callBack: $.loadSelectOptionsCallback,
		action: 'loadGroupsList',
		additionalParams: 'informationsystem_id=' + system_id,
		windowId: windowId});
	getInformationItems(windowId, 0);
	return false;
}

function getShops(windowId, shop_id) {
	$.ajaxRequest({
		path: '/admin/hostdev/redirect/index.php',
		context: 'selshop_group_id',
		callBack: $.loadSelectOptionsCallback,
		action: 'loadShopGroupsList',
		additionalParams: 'shop_id=' + shop_id,
		windowId: windowId});
	getShopItems(windowId, 0);
	return false;
}

function ShowRedirectRows(windowId, index) {
	
	if (window.hostcms >= 140) {
		var windowId = $.getWindowId(windowId);
	} else {
		var windowId = getWindowId(windowId);
	}
	var default_value = 'none',
			structure_id = 'none',
			informationsystem_group_id = 'none',
			informationsystem_item_id = 'none',
			informationsystem_id = 'none',
			shop_id = 'none',
			shop_group_id = 'none',
			shop_item_id = 'none';
	index = parseInt(index);

	switch (index) {
		case 0: // строка
			default_value = 'block';
			default_value_dis = true;
			break;
		case 1: // Узел структуры
			structure_id = 'block';
			structure_dis = true;
			break;
		case 2: // Информационная группа
		case 3:
			informationsystem_group_id = 'block';
			informationsystem_group_dis = true;
			informationsystem_id = 'block';
			if (index === 3) {
				informationsystem_item_id = 'block';
			}
			break;
		case 4: // Интернет магазин
		case 5:
			shop_id = 'block';
			shop_group_id = 'block';
			if (index === 5) {
				shop_item_id = 'block';
			}
			break;
		case 6: // Визуальный редактор
			default_value = 'block';
			break;
		case 3: // Список
			list_id = 'block';
			break;
		case 7: // Флажок
			default_value_checked = 'block';
			break;
		case 8: // Дата
			default_value_date = 'block';
			break;
		case 9: // ДатаВремя
			default_value_datetime = 'block';
			break;
	}
//	alert($("#" + windowId + " #structure_id").val());
	$("#" + windowId + " #default_value").css('display', default_value);
	$("#" + windowId + " #structure_id").css('display', structure_id);
	$("#" + windowId + " #informationsystem_group_id").css('display', informationsystem_group_id);
	$("#" + windowId + " #informationsystem_id").css('display', informationsystem_id);
	$("#" + windowId + " #informationsystem_item_id").css('display', informationsystem_item_id);
	$("#" + windowId + " #shop_id").css('display', shop_id);
	$("#" + windowId + " #shop_group_id").css('display', shop_group_id);
	$("#" + windowId + " #shop_item_id").css('display', shop_item_id);
}