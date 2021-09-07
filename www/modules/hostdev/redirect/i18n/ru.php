<?php

/**
 * Redirects
 *
 * @version 1.35
 * @author Eugeny Panikarowsky - evgenii_panikaro@mail.ru
 * @copyright © 2018 Eugeny Panikarowsky
 *
*/

defined('HOSTCMS') || exit('HostCMS: access denied.');

return array(
		'model_name' => 'Редиректы',
		'menu' => 'Редиректы',
		'listurl' => 'Список редиректов',
		'id'	=> 'Код',
		'old_url'	=> '<acronym title="Старый URL">URL</acronym>',
		'data_old_url' => 'Старый URL',
		'data_new_url' => 'Новый URL',
		'type'	=> 'Тип нового URL',
		'new_url'	=> '<acronym title="Новый URL">Новый URL</acronym>',
		'old_url_description' => 'Старый URL',
		'redirect' => 'Редирект',
		'referer'	=> 'Реферер',
		'append'	=> 'Не подставлять остальной путь вместо звездочки',
		'referer_description' => 'Реферер',
	
		'delete_url' => 'Удалить URL',
		'edit_url'	=> 'Редактировать URL',
		'redirect_add' => 'Добавить URL',
	
		'form_listurl' =>'Редиректы: список редиректов',
		'form_listurl_description' => '',
		'active' => 'Активность',
		'activedesc' => '',
		'loadGroupsList'=> 'Загрузка информационных групп',
		'loadGroupsListdesc' => '',
		'loadShopGroupsList' => 'Загрузка групп интернет-магазина',
		'loadShopGroupsListDesc' => '',
		'loadShopItemList' => 'Загрузка товаров интернет-магазина',
		'loadShopItemListDesc' => '',
	
		'changeActive_success' => 'Активность редиректа успешно изменена',
		'markDeleted_success' => 'Редирект успешно удален',
		'undelete_success' => 'Редирект успешно восстановлен',
		'delete_success' => 'Редирект успешно удален из корзины',
		'edit_success' => 'Редирект успешно изменен',
		'edit_title' => 'Редактирование редиректа',
		'add_title'	=> 'Добавление редиректа',
	
		'structure' => 'Узел структуры нового URL',
		'infgroup'	=> 'Группа информационной системы',
		'infitem'	=> 'Информационный элемент',
		'shop_id' => 'Интернет-магазин',
		'shop_group_id' => 'Группа интернет-магазина',
		'shop_item_id'	=> 'Товар',
	);
