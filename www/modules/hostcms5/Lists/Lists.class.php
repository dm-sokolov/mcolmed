<?php

/**
 * Система управления сайтом HostCMS v. 5.xx
 *
 * Copyright © 2005-2011 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 *
 * Класс модуля "Списки".
 *
 * Файл: /modules/Lists/Lists.class.php
 *
 * @package HostCMS 5
 * @author Hostmake LLC
 * @version 5.x
 */
class lists
{
	/**
	* Код ошибки
	*
	* @var int
	* @access private
	*/
	var $error;

	/**
	* Массив
	*
	* @var array
	* @access private
	*/
	var $CacheListItems = array();

	/**
	 * Массив элементов
	 *
	 * @var array
	 * @access private
	 */
	var $CacheGetListsItems = array();

	function getArrayListDir($oListDir)
	{
		return array (
			'list_dir_id' => $oListDir->id,
			'list_dir_parent_id' => $oListDir->parent_id,
			'list_dir_name' => $oListDir->name,
			'list_dir_description' => $oListDir->description,
			'site_id' => $oListDir->site_id,
			'users_id' => $oListDir->user_id
		);
	}

	function getArrayList($oList)
	{
		return array (
			'lists_id' => $oList->id,
			'list_dir_id' => $oList->list_dir_id,
			'lists_name' => $oList->name,
			'lists_description' => $oList->description,
			'users_id' => $oList->user_id,
			'site_id' => $oList->site_id
		);
	}

	function getArrayListItem($oList_Item)
	{
		return array (
			'lists_items_id' => $oList_Item->id,
			'lists_id' => $oList_Item->list_id,
			'lists_items_value' => $oList_Item->value,
			'lists_items_order' => $oList_Item->sorting,
			'lists_items_description' => $oList_Item->description,
			'lists_items_active' => $oList_Item->active,
			'users_id' => $oList_Item->user_id
		);
	}

	/**
	* Устаревший метод. Вставка/обновление данных о списке
	*
	* @param int $type параметр, определяющий будет производиться вставка или обновление данных о списке (0 – вставка, 1 - обновление)
	* @param $lists_id идентификатор списка, для которого обновляется информация. При вставке $lists_id = 0
	* @param string $lists_name название списка
	* @param string_type $lists_description описание списка
	* @param int $users_id идентификатор пользователя, если false - берется текущий пользователь.
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $type = 0;
	* $lists_id = 0;
	* $lists_name = 'Новый список';
	* $lists_description = 'Описание списка';
	*
	* $newid = $lists->insert_lists($type, $lists_id, $lists_name, $lists_description);
	*
	* // Распечатаем результат
	* if ($newid)
	* {
	* 	echo 'Список добавлен';
	* }
	*
	* ?>
	* </code>
	* @return int идентификатор вставленного/обновленного списка
	* @see InsertList
	*/
	function insert_lists($type, $lists_id, $lists_name, $lists_description, $users_id = false, $site_id = false)
	{
		$param = array();
		$param['lists_id'] = $lists_id;
		$param['lists_name'] = $lists_name;
		$param['lists_description'] = $lists_description;
		$param['site_id'] = $site_id;
		$param['users_id'] = $users_id;

		return $this->InsertList($param);
	}

	/**
	* Вставка/обновление данных о списке
	*
	* @param $param массив параметров
	* - $param['lists_id'] идентификатор редактируемого списка
	* - $param['list_dir_id'] идентификатор раздела
	* - $param['lists_name'] название списка
	* - $param['lists_description'] описание списка
	* - $param['site_id'] идентификатор сайта, к которому относится список. По умолчанию - текущий сайт
	* - $param['users_id'] идентификатор пользователя центра администрирования, если false (по умолчанию) - берется текущий пользователь.
    * @return int идентификатор добавленного/измененного списка	или код ошибки.
	* <br />Коды ошибок:
	* -1 - не задано название списка
	* -2 - сайту уже принадлежит список с переданным названием
	*/
	function InsertList($param)
	{
		$param = Core_Type_Conversion::toArray($param);

		if (!isset($param['lists_id']) || $param['lists_id'] == 0)
		{
			$param['lists_id'] = NULL;
		}

		$oList = Core_Entity::factory('List', $param['lists_id']);

		$list_name = trim(Core_Type_Conversion::toStr($param['lists_name']));
		if (strlen($list_name) > 0)
		{
			$oList->name = $list_name;
		}
		elseif (is_null($param['lists_id'])) // При добавлении списка не задано его название - ошибка
		{
			return -1;
		}

		$site_id = isset($param['site_id'])
			? intval($param['site_id'])
			: intval(CURRENT_SITE);

		// Проверяем наличие списка с таким же названием, принадлежащего сайту, к которому относится список
		$oListTest = Core_Entity::factory('List')->getByNameAndSite($list_name, $site_id);

		// Сайту уже принадлежит список с таким именем как у добавляемого/редактируемого списка
		if (!is_null($oListTest) && $oList->id != $oListTest->id)
		{
			return -2;
		}

		if (isset($param['lists_description']))
		{
			$oList->description = $param['lists_description'];
		}

		if (isset($param['list_dir_id']))
		{
			$oList->list_dir_id = intval($param['list_dir_id']);
		}

		if (is_null($oList->id))
		{
			$oList->site_id = $site_id;
		}
		else
		{
			// Список переносится на другой сайт
			if ($site_id != $oList->site_id)
			{
				$oList->list_dir_id = 0;
				$oList->site_id = $site_id;
			}
		}

		if (is_null($oList->id) && isset($param['users_id']) && $param['users_id'])
		{
			$oList->user_id = intval($param['users_id']);
		}

		$oList->save();

		return $oList->id;
	}

	/**
	* Устаревший метод. Вставка/обновление данных об элементе списка
	*
	* @param int $type параметр, определяющий будет производится вставка или обновление данных об элементе списка (0 – вставка, 1 - обновление)
	* @param int $lists_items_id  идентификатор элемента списка, для которого обновляется информация
	* @param int $lists_id идетификатор списка, к которому относится вставляемый/обновляемый элемент
	* @param int $lists_items_value значение элемента списка
	* @param int $lists_items_order порядок сортировки для элемента списка
	* @param int $users_id идентификатор пользователя, если false - берется текущий пользователь.
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $type = 0;
	* $lists_items_id = 0;
	* $lists_id = 18;
	* $lists_items_value = 'Значение списка';
	* $lists_items_order = 10;
	* $lists_items_description = 'Описание элемента списка';
	*
	* $newid = $lists->insert_lists_items($type, $lists_items_id, $lists_id, $lists_items_value, $lists_items_order, $lists_items_description);
	*
	* // Распечатаем результат
	* if ($newid)
	* {
	* 	echo 'Элемент списка добавлен';
	* }
	* else
	* {
	* 	echo 'Ошибка! Элемент списка не добавлен!';
	* }
	* ?>
	* </code>
	* @return int идентификатор вставленного/обновленного элемента списка
	* @see InsertListItem
	*/
	function insert_lists_items($type, $lists_items_id, $lists_id, $lists_items_value,
	$lists_items_order = 0, $lists_items_description = '', $users_id = false)
	{
		$param['lists_items_id'] = $lists_items_id;
		$param['lists_id'] = $lists_id;
		$param['lists_items_value'] = $lists_items_value;
		$param['lists_items_order'] = $lists_items_order;
		$param['lists_items_description'] = $lists_items_description;
		$param['users_id'] = $users_id;

		/* Возвращаем id добавленного/измененного элемента*/
		return $this->InsertListItem($param);
	}

	/**
	* Вставка/обновление данных об элементе списка
	*
	* @param array $param массив параметров
	* - $param['lists_items_id'] идентификатор изменяемого элемента списка
	* - $param['lists_id'] идетификатор списка, к которому относится вставляемый/обновляемый элемент
	* - $param['lists_items_value'] значение элемента списка
	* - $param['lists_items_order'] порядок сортировки элемента списка
	* - $param['lists_items_description'] описание элемента списка
	* - $param['lists_items_active'] активность элемента списка (0 - неактивен, 1 - активен). По умолчанию активен
	* - $param['users_id'] идентификатор пользователя, если false - берется текущий пользователь.
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $param['lists_id'] = 18;
	* $param['lists_items_value'] = 'Значение списка';
	* $param['lists_items_order'] = 10;
	* $param['lists_items_description'] = 'Описание элемента списка';
	*
	* $newid = $lists->InsertListItem($param);
	*
	* // Распечатаем результат
	* if ($newid)
	* {
	* 	echo 'Элемент списка добавлен';
	* }
	* else
	* {
	* 	echo 'Ошибка! Элемент списка не добавлен!';
	* }
	* ?>
	* </code>
	* @return int идентификатор вставленного/обновленного элемента списка
	*/
	function InsertListItem($param)
	{
		$param = array($param);

		if (!isset($param['lists_items_id']) || $param['lists_items_id'] == 0)
		{
			$param['lists_items_id'] = NULL;
		}

		$oList_Item = Core_Entity::factory('List_Item', $param['lists_items_id']);

		// Добавляем элемент списка и не задан список
		if (is_null($oList_Item->id)
		&& (!isset($param['lists_id']) || intval($param['lists_id']) == 0))
		{
			return FALSE;
		}

		if (isset($param['lists_id']))
		{
			$oList_Item->list_id = intval($param['lists_id']);
		}

		$list_item_value = Core_Type_Conversion::toStr($param['lists_items_value']);

		if (strlen($list_item_value = trim($list_item_value)) > 0)
		{
			$oList_Item->value = $list_item_value;
		}
		elseif(is_null($oList_Item->id))
		{
			return FALSE;
		}

		$oList_Item_SameValue = Core_Entity::factory('List', $oList_Item->list_id)
			->List_Items->getByValue($list_item_value);

		if (!is_null($oList_Item_SameValue)
			&& $oList_Item->id != $oList_Item_SameValue->id)
		{
			return FALSE;
		}

		if (isset($param['lists_items_order']))
		{
			$oList_Item->sorting = intval($param['lists_items_order']);
		}

		if (isset($param['lists_items_description']))
		{
			$oList_Item->description = $param['lists_items_description'];
		}

		if (isset($param['lists_items_active']))
		{
			$list_item_active = intval($param['lists_items_active']) != 0 ? 1 : 0;
			$oList_Item->active = $list_item_active;
		}
		elseif (is_null($oList_Item->id))
		{
			$oList_Item->active = 1;
		}

		if (is_null($oList_Item->id) && isset($param['users_id']) && $param['users_id'])
		{
			$oList_Item->user_id = intval($param['users_id']);
		}

		if (!is_null($oList_Item->id))
		{
			/* Очистка файлового кэша*/
			if (class_exists('Cache'))
			{
				$cache = & singleton('Cache');
				$cache_name = 'LIST_ITEM';
				$cache->DeleteCacheItem($cache_name, $oList_Item->id);
			}
		}

		$oList_Item->save();

		// Добавляем элемент в кэш
		$this->CacheListItems[$oList_Item->id] = $this->getArrayListItem($oList_Item);

		return $oList_Item->id;
	}

	/**
	* Устаревший метод
	*
	* @param int $lists_id
	* @return resource
	* @see GetList()
	* @access private
	*/
	function select_lists($lists_id)
	{
		return $this->GetList($lists_id);
	}

	/**
	* Получение данных о списках
	*
	* @param mixed $lists_id идентификатор выбираемого списка, если идентификатор равен false, то производится выбор информации о всех списках
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 4;
	*
	* $resource = $lists->GetList($lists_id);
	*
	* // Распечатаем результат
	* $row = mysql_fetch_assoc($resource);
	*
	* print_r($row);
	* ?>
	* </code>
	* @return resource с данными о выбранных списках
	* @see SelectList()
	*/
	function GetList($lists_id)
	{
		$queryBuilder = Core_QueryBuilder::select(
				array('id', 'lists_id'),
				'list_dir_id',
				array('name', 'lists_name'),
				array('description', 'lists_description'),
				array('user_id', 'users_id'),
				'site_id')
			->from('lists')
			->where('deleted', '=', 0);

		if ($lists_id !== FALSE && $lists_id != -1)
		{
			$queryBuilder->where('id', '=', $lists_id);
		}

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение данных о списке
	*
	* @param mixed $lists_id идентификатор выбираемого списка
	* @param array $param ассоциативный массив параметров
	* - bool $param['cache_off'] - если параметр установлен - данные не кэшируются
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 4;
	*
	* $row = $lists->SelectList($lists_id);
	*
	* // Распечатаем результат
	* print_r($row);
	* ?>
	* </code>
	* @return mixed массив с данными о списке или false
	*/
	function SelectList($lists_id, $param = array())
	{
		$lists_id = intval($lists_id);

		// Кэш для хранения информации о списках
		static $CacheSelectList = array();

		if (!isset($param['cache_off']) && isset($CacheSelectList[$lists_id]))
		{
			return $CacheSelectList[$lists_id];
		}

		$oList = Core_Entity::factory('List')->find($lists_id);

		$row = $this->getArrayList($oList);

		if (!isset($param['cache_off']))
		{
			$CacheSelectList[$lists_id] = $row;
		}

		return $row;
	}

	/**
	* Получение данных об элементе списка
	*
	* @param mixed $lists_items_id идентификатор выбираемого элемента. Если идетификатор = false или идентификатор = -1 - выбираются все элементы
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_items_id = 4;
	*
	* $resource = $lists->select_lists_items($lists_items_id);
	*
	* // Распечатаем результат
	* $row = mysql_fetch_assoc($resource);
	*
	* print_r($row);
	*
	* ?>
	* </code>
	* @return resource данные об элементе списка
	*/
	function select_lists_items($lists_items_id)
	{
		$queryBuilder = Core_QueryBuilder::select(
			array('id', 'lists_items_id'),
			array('list_id', 'lists_id'),
			array('value', 'lists_items_value'),
			array('sorting', 'lists_items_order'),
			array('description', 'lists_items_description'),
			array('active', 'lists_items_active'),
			array('user_id', 'users_id')
		)
		->from('list_items')
		->where('deleted', '=', 0)
		->orderBy('sorting', 'ASC')
		->orderBy('value', 'ASC');

		if ($lists_items_id === false || $lists_items_id == -1)
		{
			// Выбираем только активные элементы
			$queryBuilder->where('active', '=', 1);
		}
		else
		{
			$lists_items_id = intval($lists_items_id);
			$queryBuilder->where('id', '=', $lists_items_id);
		}

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение данных об элементах списка
	*
	* @param int $lists_id идентификатор списка
	* @param array $property дополнительные свойства
	* - $property['orderfield'] - поле сортировки, по умолчанию lists_items_order
	* - $property['ordertype'] - направление сортировки, по умолчанию по возрастанию
	* - $property['item_activity'] array массив значений активности элементов. Может содержать следующие элементы:
	* <ul>
	* <li>active - активные элементы (по умолчанию);
	* <li>inactive - неактивные элементы;
	* </ul>
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 4;
	*
	* $resource = $lists->SelectListsItems($lists_id);
	*
	* // Распечатаем результат
	* while($row = mysql_fetch_assoc($resource))
	* {
	* 	print_r($row);
	* }
	* ?>
	* </code>
	* @return resource данных об элементах списка
	*/
	function SelectListsItems($lists_id, $property = array())
	{
		$lists_id = intval($lists_id);

		if (!is_array($property))
		{
			$property = array();
		}

		$queryBuilder = Core_QueryBuilder::select(
			array('id', 'lists_items_id'),
			array('list_id', 'lists_id'),
			array('value', 'lists_items_value'),
			array('sorting', 'lists_items_order'),
			array('description', 'lists_items_description'),
			array('active', 'lists_items_active'),
			array('user_id', 'users_id')
		)
		->from('list_items')
		->where('list_id', '=', $lists_id)
		->where('deleted', '=', 0);

		// Если не задано направление сортировки, то сортируем по возрастанию
		$property['ordertype'] = Core_Type_Conversion::toStr($property['ordertype']);
		if (trim(strtolower($property['ordertype'])) != 'DESC')
		{
			$property['ordertype'] = 'ASC';
		}

		// Если не задано поле сортировки, сортируем по порядку сортировки и значению
		if (!isset($property['orderfield']))
		{
			$queryBuilder
				->orderBy('sorting', $property['ordertype'])
				->orderBy('value', $property['ordertype']);
		}
		else
		{
			$queryBuilder
				->orderBy($property['orderfield'], $property['ordertype']);
		}

		if (!isset($property['item_activity']))
		{
			$property['item_activity'] = array('active');
		}

		// Если только активные (без неактивных)
		if (in_array('active', $property['item_activity']) && !in_array('inactive', $property['item_activity']))
		{
			$queryBuilder->where('active', '=', 1);
		}
		// только неактивные
		elseif (in_array('inactive', $property['item_activity']) && !in_array('active', $property['item_activity']))
		{
			$queryBuilder->where('active', '=', 0);
		}

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение данных об элементах списка
	*
	* @param int $lists_id идентификатор списка
	* @param array $property дополнительные свойства
	* - $property['orderfield'] - поле сортировки, по умолчанию lists_items_order
	* - $property['ordertype'] - направление сортировки, по умолчанию по возрастанию
	* - $property['item_activity'] array массив значений активности элементов. Может содержать следующие элементы:
	* <ul>
	* <li>active - активные элементы (по умолчанию);
	* <li>inactive - неактивные элементы;
	* </ul>
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 4;
	*
	* $rows = $lists->GetListsItems($lists_id);
	*
	* // Распечатаем результат
	* print_r($rows);
	*
	* ?>
	* </code>
	* @return array данных об элементах списка
	*/
	function GetListsItems($lists_id, $property = array())
	{
		if (isset($this->CacheGetListsItems[$lists_id]))
		{
			return $this->CacheGetListsItems[$lists_id];
		}

		$resource = $this->SelectListsItems($lists_id, $property);

		$this->CacheGetListsItems[$lists_id] = array();

		while ($row = mysql_fetch_assoc($resource))
		{
			$this->CacheGetListsItems[$lists_id][] = $row;
		}

		return $this->CacheGetListsItems[$lists_id];
	}

	/**
	* Получение данных об элементе списка
	*
	* @param int $lists_items_id идентификатор элемента списка
	* @param array $param ассоциативный массив параметров
	* - bool $param['cache_off'] - если параметр установлен - данные не кэшируются
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_items_id = 4;
	*
	* $row = $lists->GetListItem($lists_items_id);
	*
	* // Распечатаем результат
	* print_r($row);
	* ?>
	* </code>
	* @return mixed ассоциативный массив с данными об элементе списка или false, если элемент не найден
	*/
	function GetListItem($lists_items_id, $param = array())
	{
		$lists_items_id = intval($lists_items_id);

		if (!$lists_items_id)
		{
			return FALSE;
		}

		/* Если есть в кэше - возвращаем из кэша*/
		if (isset($this->CacheListItems[$lists_items_id]))
		{
			return $this->CacheListItems[$lists_items_id];
		}

		$param = Core_Type_Conversion::toArray($param);

		/* Проверка на наличие в файловом кэше*/
		$cache_name = 'LIST_ITEM';

		if (class_exists('Cache') && !isset($param['cache_off']))
		{
			$cache = & singleton('Cache');
			if ($in_cache = $cache->GetCacheContent($lists_items_id, $cache_name))
			{
				/*  Записываем в кэш*/
				$this->CacheListItems[$lists_items_id] = $in_cache['value'];
				return $in_cache['value'];
			}
		}

		$oList_Item = Core_Entity::factory('List_Item')->find($lists_items_id);
		$row = $this->getArrayListItem($oList_Item);

		/*  Записываем в кэш*/
		$this->CacheListItems[$lists_items_id] = $row;

		/* Запись в файловый кэш*/
		if (class_exists('Cache') && !isset($param['cache_off']))
		{
			$cache = & singleton('Cache');
			$cache->Insert($lists_items_id, $row, $cache_name);
		}

		return $row;
	}

	/**
	* Проверка наличия значения в списке
	*
	* @param int $lists_id идентификатор списка
	* @param string $list_item_value значение элемента списка
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 4;
	* $list_item_value = 'Значение элемента';
	*
	* $row = $lists->GetListItemIfIssetValue($lists_id, $list_item_value);
	*
	* // Распечатаем результат
	* print_r($row);
	* ?>
	* </code>
	* @return mixed информация об элементе списка с таким значением или false, если элемента с данным значением не существует
	*/
	function GetListItemIfIssetValue($lists_id, $list_item_value)
	{
		$lists_id = intval($lists_id);
		$list_item_value = trim(Core_Type_Conversion::toStr($list_item_value));

		$oList_Items = Core_Entity::factory('List', $lists_id)
			->List_Items
			->getByValue($list_item_value);

		if (!is_null($oList_Items))
		{
			return $this->getArrayListItem($oList_Items);
		}

		return FALSE;
	}

	/**
	* Удаление списка
	*
	* @param int $lists_id идентификатор удаляемого списка
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 5;
	*
	* $result = $lists->del_lists($lists_id);
	*
	* if ($result)
	* {
	* 	echo "Удаление выполнено успешно";
	* }
	* else
	* {
	* 	echo "Ошибка удаления";
	* }
	* ?>
	* </code>
	* @return boolean
	*/
	function del_lists($lists_id)
	{
		$lists_id = intval($lists_id);
		return Core_Entity::factory('List', $lists_id)->markDeleted();
	}

	/**
	* Удаление элемента списка
	*
	* @param int $lists_items_id идентификатор удаляемого элемента списка
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_items_id = 71;
	*
	* $result = $lists->del_lists_items($lists_items_id);
	*
	* if ($result)
	* {
	* 	echo "Удаление выполнено успешно";
	* }
	* else
	* {
	* 	echo "Ошибка удаления";
	* }
	* ?>
	* </code>
	* @return boolean
	*/
	function del_lists_items($lists_items_id)
	{
		$lists_items_id = intval($lists_items_id);

		/* Очистка файлового кэша*/
		if (class_exists('Cache'))
		{
			$cache = & singleton('Cache');
			$cache_name = 'LIST_ITEM';
			$cache->DeleteCacheItem($cache_name, $lists_items_id);
		}

		return Core_Entity::factory('List_Item', $lists_items_id)->markDeleted();
	}

	/**
	* Копирование списка
	*
	* @param int $lists_id идентификатор копируемого списка
	* @param int $site_id идентификатор сайта, на который следует перенести скопированный список, если не передан, используется текущий
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 2;
	*
	* $result = $lists->CopyList($lists_id);
	*
	* if ($result)
	* {
	* 	echo "Копирование выполнено успешно";
	* }
	* else
	* {
	*	echo "Ошибка копирования";
	* }
	* ?>
	* </code>
	* @return boolean
	*/
	function CopyList($lists_id, $site_id = FALSE)
	{
		$lists_id = intval($lists_id);

		$oList = Core_Entity::factory('List')->find($lists_id);
		$oNewList = $oList->copy();

		//$oNewList->name = $oList->name;

		if ($site_id !== FALSE)
		{
			$site_id = intval($site_id);
			$oNewList->site_id = $site_id;
		}

		$oNewList->save();

		return $oNewList->id;
	}

	/**
	* Копирование элемента списка
	*
	* @param int $list_item_id идентификатор копируемого элемента списка
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $list_item_id = 8;
	*
	* $newid = $lists->CopyListItem($list_item_id);
	*
	* // Распечатаем результат
	* if ($newid)
	* {
	* 	echo 'Элемент списка скопирован';
	* }
	* else
	* {
	* 	echo 'Ошибка! Элемент списка не скопирован!';
	* }
	* ?>
	* </code>
	* @return int идентификатор копии элемента списка в случае успешного завершения, false - в противном случае
	*/
	function CopyListItem($list_item_id)
	{
		$list_item_id = intval($list_item_id);

		$oList_Item = Core_Entity::factory('List_Item')->find($list_item_id);
		$oNewList_Item = $oList_Item->copy();

		return $oNewList_Item->id;
	}

	/**
	* Отображение элементов списка. Внутренний метод
	*
	* @param int $lists_id идентификатор списка, элементы которого необходимо отобразить
	* @param string $xsl_name название XSL-шаблона, используемого для отображения списка
	* @param array $external_propertys массив дополнительных парметров, добавляемых в XML
	*
	*/
	function ShowList($lists_id, $xsl_name, $external_propertys = array())
	{
		$external_xml = new ExternalXml();

		$lists_id = Core_Type_Conversion::toInt($lists_id);
		$xsl_name = Core_Type_Conversion::toStr($xsl_name);

		$xmlData = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xmlData .= '<document>'."\n";
		$xmlData .= $external_xml->GenXml($external_propertys);

		// получаем данные о списке
		$row_list = $this->SelectList($lists_id);

		$xmlData .= '<list id="' . str_for_xml($row_list['lists_id']) . '">' . "\n";
		$xmlData .= '<name>' . str_for_xml($row_list['lists_name']) . '</name>' . "\n";
		$xmlData .= '<description>' . str_for_xml($row_list['lists_description']) . '</description>' . "\n";

		$result_items = $this->SelectListsItems($lists_id);

		$xmlData .= '<items>'."\n";

		// формируем в цикле XML для элементов списка
		while($row_items = mysql_fetch_assoc($result_items))
		{
			$xmlData .= '<item id="' . str_for_xml($row_items['lists_items_id']) . '">' . "\n";
			$xmlData .= '<value>' . str_for_xml($row_items['lists_items_value']) . '</value>' . "\n";
			$xmlData .= '<order>' . str_for_xml($row_items['lists_items_order']) . '</order>' . "\n";
			$xmlData .= '</item>' . "\n";
		}

		$xmlData .= '</items>'."\n";
		$xmlData .= '</list>'."\n";
		$xmlData .= '</document>';

		$xsl = & singleton('xsl');
		$result = $xsl->build($xmlData, $xsl_name);

		echo $result;
	}

	/**
	* Получение элементов списка в виде resource.
	*
	* @param int $lists_id идентификатор списка
	* - array $param['item_activity'] array массив значений активности элементов. Может содержать следующие элементы:
	* <ul>
	* <li>active - активные элементы (по умолчанию);
	* <li>inactive - неактивные элементы;
	* </ul>
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 8;
	*
	* $resource = $lists->GetListItemsValuesById($lists_id);
	*
	* // Распечатаем результат
	* while($row = mysql_fetch_assoc($resource))
	* {
	* 	print_r($row);
	* }
	* ?>
	* </code>
	* @return resource с элементами списка или false
	* @see GetListItemsById()
	*/
	function GetListItemsValuesById($lists_id, $param = array())
	{
		$queryBuilder = Core_QueryBuilder::select(
			array('id', 'lists_items_id'),
			array('list_id', 'lists_id'),
			array('value', 'lists_items_value'),
			array('sorting', 'lists_items_order'),
			array('description', 'lists_items_description'),
			array('active', 'lists_items_active'),
			array('user_id', 'users_id')
		)
		->from('list_items')
		->where('deleted', '=', 0)
		->orderBy('sorting', 'ASC')
		->orderBy('value', 'ASC');

		if (!isset($param['item_activity']))
		{
			$param['item_activity'] = array('active');
		}

		// Если только активные (без неактивных)
		if (in_array('active', $param['item_activity']) && !in_array('inactive', $param['item_activity']))
		{
			$queryBuilder->where('active', '=', 1);
		}
		// только неактивные
		elseif (in_array('inactive', $param['item_activity']) && !in_array('active', $param['item_activity']))
		{
			$queryBuilder->where('active', '=', 0);
		}

		if ($lists_id !== false && $lists_id != -1)
		{
			$lists_id = intval($lists_id);
			$queryBuilder->where('list_id', '=', $lists_id);
		}

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Получение элементов списка в виде массива
	*
	* @param int $lists_id идентификатор списка
	* @param array $param ассоциативный массив параметров
	* - bool $param['cache_off'] - если параметр установлен - данные не кэшируются
	* - array $param['item_activity'] array массив значений активности элементов. Может содержать следующие элементы:
	* <ul>
	* <li>active - активные элементы (по умолчанию);
	* <li>inactive - неактивные элементы;
	* </ul>
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 8;
	*
	* $array = $lists->GetListItemsById($lists_id);
	*
	* if (is_array($array) && count($array) > 0)
	* {
	* 	// Распечатаем результат
	* 	foreach ($array as $row)
	* 	{
	* 		print_r($row);
	* 	}
	* }
	* ?>
	* </code>
	* @return array массив с элементами
	*/
	function GetListItemsById($lists_id, $param = array())
	{
		$lists_id = intval($lists_id);
		$cache_name = $lists_id . '_' . serialize($param);

		static $CacheGetListItemsById = array();

		if (!isset($param['cache_off']) && isset($CacheGetListItemsById[$cache_name]))
		{
			return $CacheGetListItemsById[$cache_name];
		}

		if (!isset($param['item_activity']))
		{
			$param['item_activity'] = array('active');
		}

		$queryBuilder = Core_QueryBuilder::select(
			array('id', 'lists_items_id'),
			array('list_id', 'lists_id'),
			array('value', 'lists_items_value'),
			array('sorting', 'lists_items_order'),
			array('description', 'lists_items_description'),
			array('active', 'lists_items_active'),
			array('user_id', 'users_id')
		)->from('list_items')
		->where('list_id', '=', $lists_id)
		->where('deleted', '=', 0)
		->orderBy('sorting', 'ASC')
		->orderBy('value', 'ASC');

		// Если только активные (без неактивных)
		if (in_array('active', $param['item_activity']) && !in_array('inactive', $param['item_activity']))
		{
			$queryBuilder->where('active', '=', 1);
		}
		// только неактивные
		elseif (in_array('inactive', $param['item_activity']) && !in_array('active', $param['item_activity']))
		{
			$queryBuilder->where('active', '=', 0);
		}

		$aList_Items = $queryBuilder->execute()->asAssoc()->result();

		// Кэшируем
		if (!isset($param['cache_off']))
		{
			$CacheGetListItemsById[$cache_name] = $aList_Items;
		}

		return $aList_Items;
	}

	/**
	* Генерация XML для всех элементов списка
	*
	* @param int $lists_id идентификатор списка
	* <code>
	* <?php
	* $lists = & singleton('lists');
	*
	* $lists_id = 8;
	*
	* $xml = $lists->GenXml4ListItems($lists_id);
	*
	* // Распечатаем результат
	* echo nl2br(htmlspecialchars($xml));
	* ?>
	* </code>
	* @return string строка с XML или пустая строка
	*/
	function GenXml4ListItems($lists_id)
	{
		if (!$lists_id)
		{
			return '';
		}

		$list_array = $this->GetListItemsById($lists_id);

		if (!$list_array)
		{
			// Значений свойств нет
			return '';
		}

		// Извлекаем информацию о списке
		$list_row = $this->SelectList($lists_id);

		if (!$list_row)
		{
			// Неудалось получить информацию о списке
			return '';
		}

		$xmlData = '<list_items id="' . $lists_id . '">' . "\n";
		$xmlData .= '<lists_name>' . str_for_xml($list_row['lists_name']) . '</lists_name>' . "\n";
		$xmlData .= '<lists_description>' . str_for_xml($list_row['lists_description']) . '</lists_description>' . "\n";

		if (is_array($list_array) && count($list_array) > 0)
		{
			foreach ($list_array as $row)
			{
				$xmlData .= '<list_item id="' . Core_Type_Conversion::toInt($row['lists_items_id']) . '">' . "\n";
				$xmlData .= '<list_item_value>' . str_for_xml($row['lists_items_value']) . '</list_item_value>' . "\n";
				$xmlData .= '<lists_item_description>' . str_for_xml($row['lists_items_description']) . '</lists_item_description>' . "\n";
				$xmlData .= '<lists_items_order>' . str_for_xml($row['lists_items_order']) . '</lists_items_order>' . "\n";
				$xmlData .= '<lists_items_active>' . str_for_xml($row['lists_items_active']) . '</lists_items_active>' . "\n";
				$xmlData .= '</list_item>' . "\n";
			}
		}

		$xmlData .= '</list_items>' . "\n";
		return $xmlData;
	}

	/**
	 * Получение списков определенного сайта
	 *
	 * @param int $site_id идентификатор сайта
	 * <code>
	 * <?php
	 * $lists = & singleton('lists');
	 *
	 * $site_id = 1;
	 *
	 * $resource = $lists->GetAllListsForSite($site_id);
	 *
	 *  // Распечатаем результат
	 * while($row = mysql_fetch_assoc($resource))
	 * {
	 * 	print_r($row);
	 * }
	 * ?>
	 * </code>
	 * @return resource
	 */
	function GetAllListsForSite($site_id)
	{
		$site_id = intval($site_id);

		$queryBuilder = Core_QueryBuilder::select(
			array('id', 'lists_id'),
			'list_dir_id',
			array('name', 'lists_name'),
			array('description', 'lists_description'),
			array('user_id', 'users_id'),
			'site_id')
		->from('lists')
		->where('site_id', '=', $site_id)
		->where('deleted', '=', 0)
		->orderBy('name', 'ASC');

		return $queryBuilder->execute()->getResult();
	}

	/**
	* Добавление/редактирование раздела списков
	*
	* @param array $param массив параметров
	* - $param['list_dir_id'] идентификатор изменяемого раздела списков
	* - $param['list_dir_parent_id'] идентификатор родительского раздела списков
	* - $param['list_dir_name'] название раздела списков
	* - $param['list_dir_description'] описание раздела списков
	* - $param['site_id'] идентификатор сайта
	* - $param['users_id'] идентификатор пользователя, если false - берется текущий пользователь
	* @return int идентификатор вставленного/обновленного раздела списков
	*/
	function InsertListDir($param)
	{
		if (!isset($param['list_dir_id']) || $param['list_dir_id'] == 0)
		{
			$param['list_dir_id'] = NULL;
		}

		$oList_Dir = Core_Entity::factory('List_Dir', $param['list_dir_id']);

		if (isset($param['list_dir_parent_id']))
		{
			$oList_Dir->parent_id = intval($param['list_dir_parent_id']);
		}

		if (isset($param['list_dir_name']))
		{
			$oList_Dir->name = $param['list_dir_name'];
		}

		if (isset($param['list_dir_description']))
		{
			$oList_Dir->description = $param['list_dir_description'];
		}

		$site_id = isset($param['site_id'])
			? intval($param['site_id'])
			: CURRENT_SITE;

		if (is_null($oList_Dir->id) && isset($param['users_id']) && $param['users_id'])
		{
			$oList_Dir->user_id = intval($param['users_id']);
		}

		$oList_Dir->save();

		return $oList_Dir->id;
	}

	/**
	* Получение информации о разделе списков
	*
	* @param $list_dir_id идентификатор раздела списков
	* @return mixed массив с информацией о разделе списков, если раздел существует или false в противном случае
	*/
	function GetListDir($list_dir_id)
	{
		$list_dir_id = intval($list_dir_id);
		$oList_Dir = Core_Entity::factory('List_Dir')->find($list_dir_id);

		if (!is_null($oList_Dir->id))
		{
			return $this->getArrayListDir($oList_Dir);
		}

		return FALSE;
	}

	/**
	* Удаление раздела списков
	* @param $list_dir_id идентификатор раздела списков
	*/
	function DeleteListDir($list_dir_id)
	{
		$list_dir_id = intval($list_dir_id);

		return Core_Entity::factory('List_Dir', $list_dir_id)->markDeleted();
	}

	/**
	 * Построение массива пути от текущего раздела к корневому
	 *
	 * @param int $list_dir_id идентификатор раздела списков, для которого необходимо построить путь
	 * @param array $return_path_array служебный параметр
	 * @return array ассоциативный массив, элементы которого содержат информацию о разделах, составляющих путь от текущего до корневого
	 */
	function GetListDirPathArray($list_dir_id, $return_path_array = array())
	{
		$list_dir_id = intval($list_dir_id);

		if ($list_dir_id != 0)
		{
			$row = $this->GetListDir($list_dir_id);
			$return_path_array[$row['list_dir_id']] = $row;
			$return_path_array = $this->GetListDirPathArray($row['list_dir_parent_id'], $return_path_array);
		}

		return $return_path_array;
	}

	/**
	* Получение информации о разделах списков
	*
	* @param $list_dir_parent_id идентификатор родительского раздела
	* @param $site_id идентификатор сайта, если равен false - идентификатор сайта не учитывается
	* @return resource
	*/
	function GetAllListDirs($list_dir_parent_id, $site_id = CURRENT_SITE)
	{
		$list_dir_parent_id = intval($list_dir_parent_id);

		$queryBuilder = Core_QueryBuilder::select(
			array('id', 'list_dir_id'),
			array('parent_id', 'list_dir_parent_id' ),
			array('name', 'list_dir_name'),
			array('description', 'list_dir_description'),
			'site_id',
			array('user_id', 'users_id')
			)
			->from('list_dirs')
			->where('deleted', '=', 0)
			->where('parent_id', '=', $list_dir_parent_id);

		if ($site_id !== false)
		{
			$site_id = intval($site_id);
			$queryBuilder->where('site_id', '=', $site_id);
		}

		return $queryBuilder->execute()->getResult();
	}

	/**
	 * Формирование дерева разделов списков
	 *
	 * @param int $list_dir_parent_id идентификатор раздела, относительно которого строится дерево разделов
	 * @param int $site_id идентификатор сайта
	 * @param string $separator символ, отделяющий раздел нижнего уровня от родительского раздела
	 * @param int $list_dir_id идентификатор раздела, который вместе с его подразделами не нужно включать в дерево разделов, если id = false, то включать в дерево разделов все подразделы.
	 * @param array $param дополнительные параметры
	 * - $param['array'] - служебный элемент
	 * - $param['sum_separator'] - служебный элемент
	 * @return array двумерный массив, содержащий дерево разделов
	 */
	function GetListDirsTree($list_dir_parent_id, $site_id = CURRENT_SITE,
	$separator = '', $list_dir_id = false, $param = array())
	{
		$list_dir_parent_id = intval($list_dir_parent_id);
		$site_id = intval($site_id);

		if ($list_dir_id !== false)
		{
			$list_dir_id = intval($list_dir_id);
		}

		if (!isset($param['sum_separator']))
		{
			$param['sum_separator'] = $separator;
		}
		else
		{
			$param['sum_separator'] = $param['sum_separator'] . $separator;
		}

		$array = array();

		// Получаем информацию о дочерних разделах
		$list_dir_resource = $this->GetAllListDirs($list_dir_parent_id);

		while($list_dir_row = mysql_fetch_assoc($list_dir_resource))
		{
			if ($list_dir_id != $list_dir_row['list_dir_id'])
			{
				$list_dir_row['separator'] = $param['sum_separator'];
				$array[] = $list_dir_row;

				// Объединяем выбранные данные с данными из подразделов
				$array = array_merge($array, $this->GetListDirsTree($list_dir_row['list_dir_id'], $site_id, $separator, $list_dir_id, $param));
			}
		}

		return $array;
	}
}

if (-1977579255 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
{
	die();
}