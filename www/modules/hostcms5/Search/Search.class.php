<?php

/**
 * Система управления сайтом HostCMS v. 5.xx
 *
 * Copyright © 2005-2011 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 *
 * Класс модуля "Поиск".
 *
 * Файл: /modules/Search/Search.class.php
 *

 * @package HostCMS 5
 * @author Hostmake LLC
 * @version 5.x
 */
class Search
{
	/**
	 * Массив соответствия идентификаторов модулей в таблице страниц модулям,
	 * Structure = 0,
	 * InformationSystem = 1,
	 * Forums = 2,
	 * shop = 3,
	 * helpdesk = 4,
	 * SiteUsers = 5
	 * Используется при вызове функции обратного вызова для генерации информации об элементе при поиске.
	 * @var array
	 */
	public $ModuleClass = array(
		0 => 'Structure',
		1 => 'InformationSystem',
		2 => 'Forums',
		3 => 'shop',
		4 => 'helpdesk',
		5 => 'SiteUsers'
	);

	/**
	 * Функция обратного вызова для отображения блока
	 * на основной странице центра администрирования.
	 *
	 */
	public function AdminMainPage()
	{

	}

	/**
	 * Получение информации о проиндексированных страницах
	 *
	 * @param int $page_id идентификатор страницы (если равен false, то возвращает информацию обо всех проиндексированных страницах)
	 * @return resource
	 */
	public function GetPageInfo($page_id = FALSE)
	{
		$queryBuilder = Core_QueryBuilder::select(
				array('id', 'search_page_id'),
				array('url', 'search_page_address'),
				array('title', 'search_page_name'),
				array('datetime', 'search_page_date'),
				array('size', 'search_page_size'),
				array('inner', 'search_page_is_inner'),
				array('module', 'search_page_module'),
				array('module_id', 'search_page_module_entity_id'),
				array('module_value_type', 'search_page_module_value_type'),
				array('module_value_id', 'search_page_module_value_id'),
				array('site_id', 'site_id')
			)->from('search_pages');

		if ($page_id != -1 && $page_id !== FALSE)
		{
			$queryBuilder->where('id', '=', $ip_id);
		}

		//$queryBuilder->where('deleted', '=', 0);

		return $queryBuilder->execute()->getResult();
	}

	/**
	 * Метод определения числа проиндексированных страниц
	 *
	 * @param int $site_id идентификатор сайта, по умолчанию FALSE
	 * <code>
	 * <?php
	 * $Search = new Search();
	 *
	 * $site_id = CURRENT_SITE;
	 *
	 * $count = $Search->GetPageCount($site_id);
	 *
	 * echo $count;
	 * ?>
	 * </code>
	 */
	public function GetPageCount($site_id = FALSE)
	{
		return Search_Controller::getPageCount($site_id);
	}

	/**
	 * Метод очищает HTML тегов, получает основу слова, хеширует и возвращает массив хэшей слов
	 *
	 * @param string $text исходный текст
	 * @param array $param массив дополнительных параметров
	 * - $param['hash_function'] = 'md5' {'md5','crc32',''} используемая ХЭШ-функция
	 * @return array массив хэшей
	 * @access private
	 */
	public function ClearHtml($text, $param = array())
	{
		return Search_Controller::getHashes($text, $param);
	}

	/**
	 * Метод удаления из индекса страницы с указанным адресом
	 *
	 * @param string $page_address адрес удаляемой из индекса страницы
	 * @return boolean результат выполнения запроса
	 */
	public function Delete_search_words($page_address, $site_id)
	{
		$oSearch_Page = Core_Entity::factory('Site', $site_id)->Search_Pages->getByUrl($page_address);

		if (!is_null($oSearch_Page))
		{
			$oSearch_Page->delete();
		}

		return TRUE;
	}

	/**
	 * Метод формирует индекс для переданного блока страниц
	 *
	 * @param array $param массив добавляемых страниц:
	 * - $param[$i][0] string имя страницы;
	 * - $param[$i][1] string адрес страницы;
	 * - $param[$i][2] string текст страницы;
	 * - $param[$i][3] float размер страницы;
	 * - $param[$i][4] int идентификатор сайта (0 - для всех сайтов);
	 * - $param[$i][5] array массив идентификаторов групп пользователей (0 - для всех групп)
	 * - $param[$i][6] string дата создания страницы
	 * - $param[$i][7] int search_page_module - модуль, которому принадлежит элемемент.
	 * <br />Имеет значения:
	 * <br />Структура сайта - 0,
	 * <br />Информационные системы - 1,
	 * <br />Форум - 2,
	 * <br />Интернет-магазин - 3,
	 * <br />HelpDesk - 4.
	 * <br />Пользователи сайта - 5.
	 * - $param[$i][8] int search_page_module_entity_id - ID элемента модуля, которому принадлежит элемемент. Например, ID магазина, ID информационной системы и т.д.
	 * - $param[$i][9] int Флаг, указывающий, внутренний поиск (внутри ЦА), или внешний (0 - внешний, 1 - внутренний)
	 * - $param[$i][10] int search_page_module_value_type - тип индексируемого элемента. Может принимать целое значение. Например, 1 - группы, 2 - товары и т.д.
	 * - $param[$i][11] int search_page_module_value_id - ID индексируемого элемента. Может принимать целое значение. Например, 1 - группы, 2 - товары и т.д.
	 *
	 * @return boolean результат выполнения запроса на добавление записей
	 */
	public function Insert_search_word($param = array())
	{
		$param = Core_Type_Conversion::toArray($param);

		if (count($param) != 0)
		{
			$queryBuilder = Core_QueryBuilder::insert('search_words')
				->columns('hash', 'search_page_id', 'weight');

			foreach ($param AS $term)
			{
				if (is_array($term) && count($term) > 0)
				{
					// Удаляем данные о странице, если она уже была проиндексирована
					$this->Delete_search_words($term[1], $term[4]);

					$file_name = mb_substr(strip_tags(Core_Type_Conversion::toStr($term[0])), 0, 255);
					$text = Core_Type_Conversion::toStr($term[2]);
					$term[5] = Core_Type_Conversion::toArray($term[5]);

					if (!isset($term[9]))
					{
						$search_page_is_inner = 0;
					}
					elseif ($term[9] > 0)
					{
						$search_page_is_inner = 1;
					}
					else
					{
						$search_page_is_inner = 0;
					}

					$Search_Page = Core_Entity::factory('Search_Page');
					$Search_Page->site_id = Core_Type_Conversion::toInt($term[4]);
					$Search_Page->title = $file_name;
					$Search_Page->url = Core_Type_Conversion::toStr($term[1]);
					$Search_Page->datetime = $term[6];
					$Search_Page->size = Core_Type_Conversion::toFloat($term[3]) * 1024;
					$Search_Page->inner = $search_page_is_inner;
					$Search_Page->module = Core_Type_Conversion::toInt($term[7]);
					$Search_Page->module_id = Core_Type_Conversion::toInt($term[8]);
					$Search_Page->module_value_type = Core_Type_Conversion::toInt($term[10]);
					$Search_Page->module_value_id = Core_Type_Conversion::toInt($term[11]);
					$Search_Page->save();

					// Определяем ID последней добавленной в таблицу страниц записи
					$page_id = $Search_Page->id;

					// Заполняем таблицу доступов к проиндексированной странице
					$count_groups = count($term[5]);

					foreach ($term[5] as $siteuser_group_id)
					{
						$oSearch_Page_Siteuser_Group = Core_Entity::factory('Search_Page_Siteuser_Group');
						$oSearch_Page_Siteuser_Group->siteuser_group_id = $siteuser_group_id;
						$Search_Page->add($oSearch_Page_Siteuser_Group);
					}

					$array = array_merge(
						$this->ClearHtml($text, array('hash_function' => 'crc32')), $this->ClearHtml($file_name, array('hash_function' => 'crc32'))
					);

					$words = array_unique($array);

					$weight = array();
					foreach ($array as $term)
					{
						if (!isset($weight[$term]))
						{
							$weight[$term] = 1;
						}
						else
						{
							$weight[$term] += 1 / count($array);
						}
					}

					$count = 0;

					// Insert words for page
					foreach ($words as $word)
					{
						$queryBuilder->values($word, $page_id, $weight[$word]);

						if ($count * 25 / 1024 > 512)
						{
							$queryBuilder->execute();
							$queryBuilder->clearValues();
							$count = 0;
						}
						else
						{
							$count++;
						}
					}
				}
			}

			if ($count > 0)
			{
				$queryBuilder->execute();
				$queryBuilder->clearValues();
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Метод очистки кэша
	 *
	 * @access private
	 */
	public function clear_stem_cache()
	{
		Search_Stemmer::instance('ru')->clearCache();
	}

	function getArraySearchPage($oSearch_Page)
	{
		return array(
			'search_page_id' => $oSearch_Page->id,
			'search_page_address' => $oSearch_Page->url,
			'search_page_name' => $oSearch_Page->title,
			'search_page_date' => $oSearch_Page->datetime,
			'search_page_size' => $oSearch_Page->size,
			'search_page_is_inner' => $oSearch_Page->inner,
			'search_page_module' => $oSearch_Page->module,
			'search_page_module_entity_id' => $oSearch_Page->module_id,
			'search_page_module_value_type' => $oSearch_Page->module_value_type,
			'search_page_module_value_id' => $oSearch_Page->module_value_id,
			'site_id' => $oSearch_Page->site_id
		);
	}

	/**
	 * Поиск списка слов в индексе. Примеры использования см. в руководстве по интеграции.
	 *
	 * @param string $query поисковой запрос
	 * @param array $param массив дополнительных параметров:
	 * - $param['site_user_id'] int идентификатор пользователя сайта для определения прав доступа;
	 * - $param['current_page'] int номер текущей страницы результатов поиска (счет с 1);
	 * - $param['items_on_page'] int количество записей на странице;
	 * - $param['site_id'] mixed идентификатор или массив идентификаторов сайтов, по которым производится поиск. Если необходим поиск по текущему сайту, укажите значение CURRENT_SITE;
	 * - $param['search_page_module'] массив модулей, ключами которого являются номера модулей, а значениями — массив идентификаторов элементов или массив с дополнительными условиями отбора элементов модулей
	 * <br />Имеет значения:
	 * <br />Структура сайта - 0,
	 * <br />Информационные системы - 1,
	 * <br />Форум - 2,
	 * <br />Интернет-магазин - 3,
	 * <br />HelpDesk - 4,
	 * <br />Пользователи сайта - 5.
	 *
	 * <br /><b>Пример поиска по информационной системе с номером 5 и 7, а также по магазину с номером 17.</b>
	 * <code>
	 * $property['search_page_module'] = array(
	 * 1 => array (5, 7),
	 * 3 => array (17));
	 * </code>
	 *
	 * <br /><b>Пример поиска по информационной системе с номером 5 и 7 (с дополнительным условием поиска только по информационным элементам), а также по магазину с номером 17.</b>
	 * <code>
	 * $property['search_page_module'] = array(
	 * 	1 => array (5,
	 * 		array('search_page_module_entity_id' => 7,
	 * 		'search_page_module_value_type' => 2)),
	 * 	3 => array (17));
	 * </code>
	 *
	 * При указании массива с дополнительными условиями он может принимать следующие аргументы:
	 * <br /><b>search_page_module_entity_id</b> - целое число, ID сущности, например, магазин с кодом 7
	 * <br /><b>search_page_module_value_type</b> - целое число или массив, ID типа, например, 1 - группа, 2 - элемент (или товар)
	 * <br /><b>search_page_module_value_id</b> - целое число или массив, ID сущности указанного типа (например, ID товара или группы) при поиске только по ним.
	 *
	 * - $param['order_field'] int поле сортировки (0 - по релевантности, 1 - по дате). по умолчанию 0.
	 * - $param['order_direction'] int направление сортировки (ASC, DESC). по умолчанию DESC.
	 * - $param['search_page_is_inner'] int осуществлять поиск по внешним страницам (значение 0), внутреним (значение 1) или всем страницам (значение FALSE). по умолчанию имеет значение 0.
	 * @return mixed результат поиска:<br />
	 * array массив страниц:<br />
	 * $result[$i]['search_page_name'] string название страницы;<br />
	 * $result[$i]['search_page_address'] string адрес страницы;<br />
	 * $result[$i]['search_page_date'] string дата индексации;<br />
	 * $result[$i]['search_page_size'] float размер страницы;<br />
	 * boolean FALSE если ничего не найдено
	 *
	 * Пример поиска по информационной системе с номером 5 и 7, а также по магазину с номером 17.
	 * <code>
	 * $Search = new Search();
	 * $property['search_page_module'] = array(
	 * 	1 => array (5, 7),
	 * 	3 => array (17));
	 *
	 * $Search->GoSearch($words, Core_Type_Conversion::toStr($GLOBALS['LA']['xsl']), $property);
	 * </code>
	 *
	 * Пример поиска по информационной системе с номером 5 и 7 (с дополнительным условием поиска только по информационным элементам), а также по магазину с номером 17.
	 * <code>
	 * $Search = new Search();
	 *
	 * $property['search_page_module'] = array(
	 * 1 => array (5,
	 *      array('search_page_module_entity_id' => 7, 'search_page_module_value_type' => 2)),
	 * 3 => array (17));
	 *
	 * $Search->GoSearch($words, Core_Type_Conversion::toStr($GLOBALS['LA']['xsl']), $property);
	 * </code>
	 *
	 */
	public function SearchWords($query, $param = array('len' => 200))
	{
		$query = strval($query);

		/* Добавляем в журнал поисковых запросов */
		$inquery_param = array();
		$inquery_param['search_log_text'] = $query;
		$this->InsertSearchInQuery($inquery_param);

		$param = Core_Type_Conversion::toArray($param);

		$oQueryBuilderSelect = Core_QueryBuilder::select('search_pages.*')
			->straightJoin()
			->sqlCalcFoundRows()
			;

		if (!isset($param['order_direction']))
		{
			$param['order_direction'] = 'DESC';
		}

		$query = urldecode($query);

		$param['len'] = isset($param['len'])
			? intval($param['len'])
			: 200;

		$lenght = mb_strlen($query) > $param['len']
			? $param['len']
			: mb_strlen($query);

		$query = mb_substr($query, 0, $lenght);

		if (isset($param['site_id']))
		{
			$aSiteId = array(0);
			if (is_array($param['site_id']) && count($param['site_id']) > 0)
			{
				foreach ($param['site_id'] as $site_id)
				{
					$aSiteId[] = intval($site_id);
				}
			}
			elseif ($param['site_id'] != 0)
			{
				$aSiteId[] = intval($param['site_id']);
			}

			$oQueryBuilderSelect->where('search_pages.site_id', 'IN', $aSiteId);
		}

		$current_page = Core_Type_Conversion::toInt($param['current_page']);

		if ($current_page < 1)
		{
			$current_page = 1;
		}

		$items_on_page = isset($param['items_on_page']) && $param['items_on_page'] > 0
			? intval($param['items_on_page'])
			: 10;

		// Формируем ограничение по модулям и по элементам модулей
		//$search_page_module_sql = '';

		if (isset($param['search_page_module']) && $param['search_page_module'] !== FALSE)
		{
			if (count($param['search_page_module']) > 0)
			{
				$oQueryBuilderSelect
					->setAnd()
					->open();

				//$search_page_module_sql_array = array();

				foreach ($param['search_page_module'] as $search_page_module => $search_page_module_entity_array)
				{
					$search_page_module = intval($search_page_module);

					$oQueryBuilderSelect
						->open();

					if (is_array($search_page_module_entity_array))
					{
						$entity_array = array();

						foreach ($search_page_module_entity_array as $key => $value)
						{
							if (is_array($value))
							{
								// при передаче массива search_page_module_entity_id обязателен
								if (isset($value['search_page_module_entity_id']))
								{
									$search_page_module_entity_id = intval($value['search_page_module_entity_id']);

									if (isset($value['search_page_module_value_type']))
									{
										if (is_array($value['search_page_module_value_type'])
										&& count($value['search_page_module_value_type']) > 0)
										{
											$aValueType = array();

											foreach ($value['search_page_module_value_type'] as $value_type)
											{
												$aValueType[] = intval($value);
											}

											//$sValueTypeSql = ' AND search_page_module_value_type IN(' . implode(', ', $aValueType) .  ')';

											$oQueryBuilderSelect
											->where('module_value_type', 'IN', $aValueType);

										}
										else
										{
											$value['search_page_module_value_type'] = intval($value['search_page_module_value_type']);

											//$sValueTypeSql = " AND search_page_module_value_type = '{$value['search_page_module_value_type']}'";

											$oQueryBuilderSelect
											->where('module_value_type', '=', $value['search_page_module_value_type']);
										}
									}
									else
									{
										//$sValueTypeSql = '';
									}

									if (isset($value['search_page_module_value_id']))
									{
										if (is_array($value['search_page_module_value_id'])
										&& count($value['search_page_module_value_id']) > 0)
										{
											$aValueId = array();

											foreach ($value['search_page_module_value_id'] as $value_id)
											{
												$aValueId[] = intval($value_id);
											}

											//$sValueIdSql = ' AND search_page_module_value_id IN(' . implode(', ', $aValueId) .  ')';

											$oQueryBuilderSelect
											->where('module_value_id', 'IN', $aValueId);
										}
										else
										{
											$value['search_page_module_value_id'] = intval($value['search_page_module_value_id']);
											//$sValueIdSql = " AND search_page_module_value_id = '{$value['search_page_module_value_id']}'";
											$oQueryBuilderSelect
											->where('module_value_id', '=', $value['search_page_module_value_id']);
										}
									}
									else
									{
										//$sValueIdSql = '';
									}

									$oQueryBuilderSelect
									->setAnd()
									->where('module', '=', $search_page_module)
									->where('module_id', '=', $search_page_module_entity_id)
									->setOr();

									//$search_page_module_sql_array[] = "search_page_module = '{$search_page_module}' AND search_page_module_entity_id = {$search_page_module_entity_id} {$sValueTypeSql} {$sValueIdSql}";
								}
							}
							else // если не массив
							{
								$entity_array[$key] = intval($value);
							}
						}

						if (count($entity_array) > 0)
						{
							$oQueryBuilderSelect
							->where('module', '=', $search_page_module)
							->setAnd()
							->where('module_id', 'IN', $entity_array)
							->setOr();

							//$search_page_module_sql_array[] = "search_page_module = '{$search_page_module}' AND search_page_module_entity_id IN (" . implode(', ', $entity_array) . ")";
						}
					}
					else // Если не массив, то ограничиваем только по модулю
					{
						//$search_page_module_sql_array[] = "search_page_module = '{$search_page_module}'";

						$oQueryBuilderSelect
						->where('module', '=', $search_page_module)
						->setOr();
					}

					$oQueryBuilderSelect
					->close()
					->setOr();
				}

				/*if (count($search_page_module_sql_array) > 0)
				{
					$search_page_module_sql = ' AND ((' . implode(") OR (", $search_page_module_sql_array) . '))';
				}*/

				$oQueryBuilderSelect
					->close()->setAnd();
			}
		}

		$usergroups = array(0);
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = isset($param['site_user_id'])
				? Core_Entity::factory('Siteuser', $param['site_user_id'])
				: Core_Entity::factory('Siteuser')->getCurrent();

			if ($oSiteuser)
			{
				$aSiteuserGroups = $oSiteuser->Siteuser_Groups->findAll();
				foreach($aSiteuserGroups as $aSiteuserGroup)
				{
					$usergroups[] = $aSiteuserGroup->id;
				}
			}
		}

		$oQueryBuilderSelect->where('search_page_siteuser_groups.siteuser_group_id', 'IN', $usergroups);

		$return = FALSE;

		if (strlen($query) > 0)
		{
			$words = $this->ClearHtml($query, array('hash_function' => 'crc32'));

			// Удаляем из результата повторения слов
			$words = array_unique($words);

			if (count($words) > 0)
			{
				$return = array();

				// Поиск в подзапросе всегда идет по весу
				$subQuery = Core_QueryBuilder::select('search_page_id')
					//->select(array('SUM(weight)', 'weight'))
					//->select(array('COUNT(id)', 'count'))
					->from('search_words')
					->where('hash', 'IN', $words)
					->groupBy('search_page_id')
					->having('COUNT(id)', '=', count($words))
					->orderBy('SUM(weight)', $param['order_direction']);

				$begin = ($current_page - 1) * $items_on_page;
				if ($begin < 0)
				{
					$begin = 0;
				}

				// Поиск по умолчанию ведем по внешним страницам
				if (!isset($param['search_page_is_inner']) || $param['search_page_is_inner'] === 0)
				{
					$oQueryBuilderSelect->where('search_pages.inner', '=', 0);
				}
				elseif ($param['search_page_is_inner'] == 1)
				{
					$oQueryBuilderSelect->where('search_pages.inner', '=', 1);
				}

				if (!isset($param['order_field']))
				{
					$param['order_field'] = 0;
				}

				// Поле сортировки
				switch ($param['order_field'])
				{
					default:
					case 0:
						$page_order_field = NULL;
					break;
					case 1:
						$page_order_field = 'search_page_date';
					break;
				}
				if ($page_order_field)
				{
					$oQueryBuilderSelect->orderBy($page_order_field, $param['order_direction']);
				}

				$oQueryBuilderSelect
					->from(array($subQuery, 'tmp'))
					->join('search_pages', 'search_pages.id', '=', 'tmp.search_page_id')
					->join('search_page_siteuser_groups', 'search_page_id');

				$oQueryBuilderSelect
					->limit($items_on_page)
					->offset($begin);

				// Load model columns BEFORE FOUND_ROWS()
				// SHOW FULL COLUMNS FROM
				Core_Entity::factory('Search_Page')->getTableColums();

				$aRows = $oQueryBuilderSelect->execute()->asObject('Search_Page_Model')->result();

				// Определим количество элементов
				$queryBuilderSame = Core_QueryBuilder::select(array('FOUND_ROWS()', 'count'));
				$count_array = $queryBuilderSame->execute()->asAssoc()->current(FALSE);

				$return['all_count'] = $count_array
					? $count_array['count']
					: 0;

				foreach ($aRows as $row)
				{
					$return[] = $this->getArraySearchPage($row);
				}
			}
		}

		return $return;
	}

	/**
	 * Метод выводит результаты поиска запроса $query с помощью XSL-шаблона
	 * $xslname в соответствии с дополнительными параметрами
	 *
	 * @param string $query текст поискового запроса
	 * @param string $xslname имя XSL шаблона
	 * @param array $param массив дополнительных параметров
	 * - $param['site_user_id'] int идентификатор пользователя сайта для определения прав доступа;
	 * - $param['current_page'] int номер текущей страницы результатов поиска (счет с 1);
	 * - $param['items_on_page'] int количество записей на странице;
	 * - $param['len'] int максимальная длина поискового запроса, значение по умолчанию 200;
	 * - $param['site_id'] mixed идентификатор или массив идентификаторов сайтов, по которым производится поиск. Если необходим поиск по текущему сайту, укажите значение CURRENT_SITE
	 * - $param['search_page_module'] массив модулей, ключами которого являются номера модулей, а значениями — массив идентификаторов элементов или массив с дополнительными условиями отбора элементов модулей
	 * - $param['order_field'] int поле сортировки (0 - по релевантности, 1 - по дате). по умолчанию 0
	 * - $param['order_direction'] int направление сортировки (ASC, DESC). по умолчанию DESC
	 * - $param['search_page_is_inner'] int осуществлять поиск по внешним страницам (значение 0), внутреним (значение 1) или всем страницам (значение FALSE). по умолчанию имеет значение 0
	 * @param array $external_propertys массив дополнительных свойств для включения в XML
	 * <br/>
	 * Описание и примеры вызовов см. у метода SearchWords()
	 * <br/>
	 * Пример поиска по информационной системе с номером 5 и 7, а также по магазину с номером 17.
	 * <code>
	 * $Search = new Search();
	 * $property['search_page_module'] = array(
	 * 1 => array (5, 7),
	 * 3 => array (17));
	 *
	 * $Search->GoSearch($words, Core_Type_Conversion::toStr($GLOBALS['LA']['xsl']), $property);
	 * </code>
	 *
	 * Пример поиска по информационной системе с номером 5 и 7 (с дополнительным условием поиска только по информационным элементам), а также по магазину с номером 17.
	 * <code>
	 * $Search = new Search();
	 *
	 * $property['search_page_module'] = array(
	 * 	1 => array (5,
	 *		array('search_page_module_entity_id' => 7,
	 *		'search_page_module_value_type' => 2)),
	 * 	3 => array (17));
	 *
	 * $Search->GoSearch($words, Core_Type_Conversion::toStr($GLOBALS['LA']['xsl']), $property);
	 * </code>
	 *
	 * @see SearchWords()
	 * @return string
	 */
	public function GoSearch($query, $xslname, $param = array(), $external_propertys = array())
	{
		$current_page = Core_Type_Conversion::toInt($param['current_page']);

		$items_on_page = isset($param['items_on_page']) && $param['items_on_page'] > 0
			? intval($param['items_on_page'])
			: 10;

		$param['GenXml_type'] = isset($param['GenXml_type'])
			? intval($param['GenXml_type'])
			: 0;

		$param['len'] = isset($param['len'])
			? intval($param['len'])
			: 200;

		$lenght = mb_strlen($query) > $param['len']
			? $param['len']
			: mb_strlen($query);

		$query = mb_substr($query, 0, $lenght);

		// Пользователь сайтов
		if (class_exists('SiteUsers'))
		{
			$SiteUsers = & singleton('SiteUsers');

			if (!isset($param['site_user_id']))
			{
				$param['site_user_id'] = $SiteUsers->GetCurrentSiteUser();
			}
		}
		elseif (!isset($param['site_user_id']))
		{
			$param['site_user_id'] = 0;
		}

		if (class_exists('Cache'))
		{
			$kernel = & singleton('kernel');
			$cache_element = 'Search_'.$query.'_'.$xslname.'_'.$kernel->implode_array($param).'_'.$kernel->implode_array($external_propertys);

			$cache = & singleton('Cache');

			$cache_name = 'SEARCH_HTML';
			if (($in_cache = $cache->GetCacheContent($cache_element, $cache_name)) && $in_cache)
			{
				echo $in_cache['value'];
				return true;
			}
		}

		$result = $this->SearchWords($query, $param);

		// Если Boolean -> сделаем пустой массив
		$result = Core_Type_Conversion::toArray($result);

		$query = trim(strval($query));
		//$query = urldecode($query);

		$current_page = Core_Type_Conversion::toInt($current_page);

		if ($current_page < 1)
		{
			$current_page = 1;
		}

		$temp_str = '';

		$XMLcode = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$XMLcode .= '<document>'."\n";

		/* Вносим в XML дополнительные теги из массива дополнительных параметров */
		$ExternalXml = new ExternalXml;
		$XMLcode .= $ExternalXml->GenXml($external_propertys, $param['GenXml_type']);
		unset($ExternalXml);

		$XMLcode .= '<search_query>'.str_for_xml($query)."</search_query>"."\n";

		// Перед преобразованием rawurlencode запрос нужно привести к клиентской кодировке
		if (mb_strtoupper(SITE_CODING) != 'UTF-8')
		{
			$query_iconv = @iconv('UTF-8', SITE_CODING . "//IGNORE//TRANSLIT", $query);
		}
		else
		{
			$query_iconv = $query;
		}

		$XMLcode .= '<search_query_url>'.str_for_xml(rawurlencode($query_iconv))."</search_query_url>"."\n";

		if (isset($result['all_count']))
		{
			$count = count($result)-1;
			$all_count = intval($result['all_count']);
		}
		else
		{
			$count = count($result);
			$all_count = $count;
		}

		$XMLcode .= '<count_items>'.$all_count.'</count_items>'."\n";
		$XMLcode .= '<current_page>'.$current_page.'</current_page>'."\n"; // Как вводить текущую страницу
		$XMLcode .= '<items_on_page>'.$items_on_page.'</items_on_page>'."\n"; // Как вводить текущую страницу

		for ($i = 0; $i < $count; $i++)
		{
			$XMLcode .= '<item>'."\n";
			$XMLcode .= '<words_id>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_words_id'])).'</words_id>'."\n";
			$XMLcode .= '<words_word>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_words_word'])).'</words_word>'."\n";
			$XMLcode .= '<words_page_id>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_words_page_id'])).'</words_page_id>'."\n";
			$XMLcode .= '<words_weight>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_words_weight'])).'</words_weight>'."\n";
			$XMLcode .= '<page_id>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_page_id'])).'</page_id>'."\n";
			$XMLcode .= '<page_name>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_page_name'])).'</page_name>'."\n";
			$XMLcode .= '<page_address>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_page_address'])).'</page_address>'."\n";
			$XMLcode .= '<page_date>'.str_for_xml((isset($result[$i]['search_page_date']))
			? Core_Date::sql2date($result[$i]['search_page_date']):
			$result[$i]['search_page_date'] = '').'</page_date>'."\n";

			$date = explode(' ', $result[$i]['search_page_date']);

			// Добавляем в XML время
			if (isset($date[1]))
			{
				$XMLcode .= '<page_time>'.str_for_xml($date[1]).'</page_time>'."\n";
			}

			$XMLcode .= '<page_datetime>'.str_for_xml((isset($result[$i]['search_page_date']))
			? Core_Date::sql2datetime($result[$i]['search_page_date'])
			: '').'</page_datetime>'."\n";

			$XMLcode .= '<page_size>'.str_for_xml(sprintf('%.2f', Core_Type_Conversion::toStr($result[$i]['search_page_size']) / 1024)).'</page_size>'."\n";

			$XMLcode .= '<page_is_inner>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_page_is_inner'])).'</page_is_inner>'."\n";
			$XMLcode .= '<page_module>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_page_module'])).'</page_module>'."\n";
			$XMLcode .= '<page_module_entity_id>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_page_module_entity_id'])).'</page_module_entity_id>'."\n";
			$XMLcode .= '<page_module_value_type>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_page_module_value_type'])).'</page_module_value_type>'."\n";
			$XMLcode .= '<page_module_value_id>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_page_module_value_id'])).'</page_module_value_id>'."\n";
			$XMLcode .= '<site_id>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['site_id'])).'</site_id>'."\n";
			$XMLcode .= '<users_group_list_id>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['search_users_group_list_id'])).'</users_group_list_id>'."\n";
			$XMLcode .= '<site_users_group_id>'.str_for_xml(Core_Type_Conversion::toStr($result[$i]['site_users_group_id'])).'</site_users_group_id>'."\n";

			// если модуль есть в списке модулей для вызова callback-функции
			if (isset($this->ModuleClass[$result[$i]['search_page_module']]))
			{
				if (class_exists($this->ModuleClass[$result[$i]['search_page_module']]))
				{
					$objectModule = new $this->ModuleClass[$result[$i]['search_page_module']]();

					if (method_exists($objectModule, '_CallbackSearch'))
					{
						// Вызываем метод при отключении модуль
						$XMLcode .= $objectModule->_CallbackSearch($result[$i]);
					}
				}
			}

			$XMLcode .= '</item>'."\n";
		}

		$XMLcode .= '</document>'."\n";


		$xsl = & singleton('xsl');
		$result = $xsl->build($XMLcode, $xslname);

		/* Запись в файловый кэш*/
		if (class_exists('Cache'))
		{
			$cache->Insert($cache_element, $result, $cache_name);
		}

		echo $result;
	}

	function StemRus($word)
	{
		return Search_Stemmer::instance('ru')->stem($word);
	}

	function StemEng($word)
	{
		return Search_Stemmer::instance('en')->stem($word);
	}

	/**
	 * Удаление поисковой информации сайта
	 *
	 * @param int $site_id идентификатор сайта
	 */
	public function DeleteSearchInfomationForSite($site_id)
	{
		$site_id = intval($site_id);

		$aSearch_Pages = Core_Entity::factory('Site', $site_id)->Search_Pages->findAll();

		foreach ($aSearch_Pages as $aSearch_Page)
		{
			$aSearch_Page->delete();
		}

		$aSearch_Logs = Core_Entity::factory('Site', $site_id)->Search_Logs->findAll();

		foreach ($aSearch_Logs as $aSearch_Log)
		{
			$aSearch_Log->delete();
		}

		return TRUE;
	}

	/**
	 * Исправление ошибки в имени метода
	 *
	 * @param array $param
	 * @return mixed
	 * @access private
	 */
	public function InsertSearchInquiry($param)
	{
		return $this->InsertSearchInQuery($param);
	}

	/**
	 * Метод для вставки запроса в журнал поисковых запросов
	 *
	 * @param array $param массив с доп. параметрами
	 * - int $param['search_log_text'] текст поискового запроса
	 * - int $param['site_users_id'] идентификатор пользователя сайта, сделавшего запрос, если не установлен - берется текущий пользователь
	 * - int $param['site_id'] идентификатор сайта, с которого произвели запрос, если не установлен - берется текущий сайт
	 * - int $param['search_log_datetime'] дата/время вставки запроса
	 * - int $param['search_log_ip'] IP-адрес с которого сделали запрос
	 * @return mixed идентификатор вставленного запроса либо FALSE
	 */
	public function InsertSearchInQuery($param)
	{
		$search_log_text = mb_substr(trim(Core_Type_Conversion::toStr($param['search_log_text'])), 0, 255);

		if (empty($search_log_text))
		{
			return FALSE;
		}

		$ip = isset($param['search_log_ip'])
			? strval($param['search_log_ip'])
			: $_SERVER['REMOTE_ADDR'];

		$hash = Core::crc32($search_log_text);
		$oSearch_Log = Core_Entity::factory('Search_Log')->getByHashAndIp($ip, $hash, date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'));

		if (!is_null($oSearch_Log))
		{
			return FALSE;
		}

		$oSearch_Log = Core_Entity::factory('Search_Log');

		if (isset($param['site_users_id']))
		{
			$oSearch_Log->siteuser_id = intval($param['site_users_id']);
		}
		else
		{
			if (class_exists('Siteuser_Model'))
			{
				$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
				if ($oSiteuser)
				{
					$oSearch_Log->siteuser_id = $oSiteuser->id;
				}
			}
		}

		if (isset($param['site_users_id']))
		{
			$oSearch_Log->site_id = intval($param['site_id']);
		}

		$oSearch_Log->query = $search_log_text;
		$oSearch_Log->ip = $ip;
		$oSearch_Log->hash = $hash;

		$oSearch_Log->save();

		return $oSearch_Log->id;
	}
}

if ((~827242327) & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
{
	die();
}