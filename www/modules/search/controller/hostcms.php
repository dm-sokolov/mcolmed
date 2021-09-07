<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Search.
 *
 * @package HostCMS
 * @subpackage Search
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Search_Controller_Hostcms extends Search_Controller_Driver
{
	/**
	 * Remove all indexed data
	 * return self
	 */
	public function truncate()
	{
		Core_QueryBuilder::truncate('search_words')->execute();
		Core_QueryBuilder::truncate('search_pages')->execute();
		Core_QueryBuilder::truncate('search_page_siteuser_groups')->execute();
	}

	/**
	 * Indexing search pages
	 * @param array $aPages list of search pages
	 * @return boolean
	 */
	public function indexingSearchPages(array $aPages)
	{
		if (count($aPages))
		{
			$queryBuilder = Core_QueryBuilder::insert('search_words')
				->columns('hash', 'search_page_id', 'weight');

			$count = 0;

			foreach ($aPages as $oPage)
			{
				if (!is_null($oPage))
				{
					// Create Search_Page
					$oSearch_Page = Core_Entity::factory('Search_Page');
					$oSearch_Page->url = $oPage->url;
					$oSearch_Page->title = $oPage->title;
					$oSearch_Page->size = $oPage->size;
					$oSearch_Page->site_id = $oPage->site_id;
					$oSearch_Page->datetime = $oPage->datetime;
					$oSearch_Page->module = $oPage->module;
					$oSearch_Page->module_id = $oPage->module_id;
					$oSearch_Page->inner = $oPage->inner;
					$oSearch_Page->module_value_type = $oPage->module_value_type;
					$oSearch_Page->module_value_id = $oPage->module_value_id;
					$oSearch_Page->save();

					// Delete previous search_page_siteuser_groups
					Core_QueryBuilder::delete('search_page_siteuser_groups')
						->where('search_page_id', '=', $oSearch_Page->id)
						->execute();

					if (is_array($oPage->siteuser_groups))
					{
						foreach ($oPage->siteuser_groups as $siteuser_group_id)
						{
							$oSearch_Page_Siteuser_Group = Core_Entity::factory('Search_Page_Siteuser_Group');
							$oSearch_Page_Siteuser_Group->siteuser_group_id = $siteuser_group_id;
							$oSearch_Page->add($oSearch_Page_Siteuser_Group);
						}
					}

					Core_QueryBuilder::delete('search_words')
						->where('search_page_id', '=', $oSearch_Page->id)
						->execute();

					$array = array_merge(
						Search_Controller::getHashes($oPage->text, array('hash_function' => 'crc32')),
						Search_Controller::getHashes($oPage->title, array('hash_function' => 'crc32'))
					);

					$oPage->text = $oPage->title = '';

					$iCountArray = count($array);
					$aWeights = array();
					foreach ($array as $word)
					{
						if (!isset($aWeights[$word]))
						{
							$aWeights[$word] = 1;
						}
						else
						{
							$aWeights[$word] += 1 / $iCountArray;
						}
					}

					// Insert words for page
					$aWords = array_unique($array);
					$array = array();
					foreach ($aWords as $word)
					{
						$queryBuilder->values($word, $oSearch_Page->id, $aWeights[$word]);

						if ($count * 30 / 1024 > 1)
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

			if ($count)
			{
				$queryBuilder->execute();
				$queryBuilder->clearValues();
			}

			return TRUE;
		}
	}

	/**
	 * Get pages count
	 * @param int $site_id site ID
	 * @return string count of pages
	 */
	public function getPageCount($site_id)
	{
		$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('COUNT(*)', 'count'))
			->from('search_pages');

		$site_id && $oCore_QueryBuilder_Select->where('site_id', '=', intval($site_id));

		$row = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

		return $row['count'];
	}

	/**
	 * Find
	 * @param string $query Search query
	 * @return array Array of Search_Page_Model
	 * @hostcms-event Search_Controller_Hostcms.onBeforeExecuteFind
	 */
	public function find($query)
	{
		$oSite = $this->site;

		$aSearch_Pages = array();

		if (strlen($query))
		{
			$aSiteuserGroups = array(0, -1);
			if (Core::moduleIsActive('siteuser'))
			{
				$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

				if ($oSiteuser)
				{
					$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
					foreach ($aSiteuser_Groups as $oSiteuser_Group)
					{
						$aSiteuserGroups[] = intval($oSiteuser_Group->id);
					}
				}
			}

			$oSearch_Pages = Core_QueryBuilder::select('search_pages.*')
				->straightJoin()
				->sqlCalcFoundRows()
				// Нулевые элементы - XSL-шаблоны, ТДС
				->where('search_pages.site_id', 'IN', array($oSite->id, 0))
				->where('search_page_siteuser_groups.siteuser_group_id', 'IN', $aSiteuserGroups)
				// По умолчанию сортировка по весу слов
				//->orderBy('datetime', 'DESC')
				;

			$subQuery = Core_QueryBuilder::select('search_page_id', array('SUM(weight)', 'sum_weight'))
				->from('search_words');

			switch ($this->orderField)
			{
				case 'weight':
					$subQuery->orderBy('sum_weight', $this->orderDirection);
				break;
				default:
					$oSearch_Pages->orderBy($this->orderField, $this->orderDirection);
			}

			$words = Search_Controller::getHashes($query, array('hash_function' => 'crc32'));

			// Удаляем из результата повторения слов
			$words = array_unique($words);

			if (count($words))
			{
				// Поиск в подзапросе всегда идет по весу
				// SUM() in select cause user can use 4.1
				$subQuery ->where('hash', 'IN', $words)
					->groupBy('search_page_id')
					->having('COUNT(id)', '=', count($words));

				if (is_array(($this->modules)) && count($this->modules))
				{
					$oSearch_Pages->setAnd()->open();

					foreach ($this->modules as $module => $module_entity_array)
					{
						$module = intval($module);

						$oSearch_Pages->open();

						if (is_array($module_entity_array))
						{
							$entity_array = array();

							foreach ($module_entity_array as $key => $value)
							{
								if (is_array($value))
								{
									// при передаче массива module_id обязателен
									if (isset($value['module_id']))
									{
										$module_id = intval($value['module_id']);

										if (isset($value['module_value_type']))
										{
											if (is_array($value['module_value_type']) && count($value['module_value_type']))
											{
												$aValueType = array();

												foreach ($value['module_value_type'] as $value_type)
												{
													$aValueType[] = intval($value);
												}

												$oSearch_Pages->where('module_value_type', 'IN', $aValueType);
											}
											else
											{
												$value['module_value_type'] = intval($value['module_value_type']);

												$oSearch_Pages->where('module_value_type', '=', $value['module_value_type']);
											}
										}

										if (isset($value['module_value_id']))
										{
											if (is_array($value['module_value_id']) && count($value['module_value_id']))
											{
												$aValueId = array();

												foreach ($value['module_value_id'] as $value_id)
												{
													$aValueId[] = intval($value_id);
												}

												$oSearch_Pages->where('module_value_id', 'IN', $aValueId);
											}
											else
											{
												$value['module_value_id'] = intval($value['module_value_id']);
												$oSearch_Pages->where('module_value_id', '=', $value['module_value_id']);
											}
										}

										$oSearch_Pages
											->setAnd()
											->where('module', '=', $module)
											->where('module_id', '=', $module_id)
											->setOr();
									}
								}
								else
								{
									$entity_array[$key] = intval($value);
								}
							}

							if (count($entity_array))
							{
								$oSearch_Pages
									->where('module', '=', $module)
									->setAnd()
									->where('module_id', 'IN', $entity_array)
									->setOr();
							}
						}
						else // Если не массив, то ограничиваем только по модулю
						{
							$oSearch_Pages
								->where('module', '=', $module)
								->setOr();
						}

						$oSearch_Pages->close()->setOr();
					}
					$oSearch_Pages->close()->setAnd();
				}

				$oSearch_Pages
					->from(array($subQuery, 'tmp'))
					->join('search_pages', 'search_pages.id', '=', 'tmp.search_page_id')
					->join('search_page_siteuser_groups', 'search_pages.id', '=', 'search_page_siteuser_groups.search_page_id')
					//->groupBy('search_pages.id') // есть в верхнем запросе
					->limit($this->limit)
					->offset($this->offset);

				if ($this->inner !== 'all')
				{
					$oSearch_Pages->where('search_pages.inner', '=', $this->inner);
				}

				Core_Event::notify(get_class($this) . '.onBeforeExecuteFind', $this, array($query, $oSearch_Pages));

				// Load model columns BEFORE FOUND_ROWS()
				// SHOW FULL COLUMNS FROM
				Core_Entity::factory('Search_Page')->getTableColumns();

				// Load user BEFORE FOUND_ROWS()
				$oUserCurrent = Core_Auth::getCurrentUser();

				$aSearch_Pages = $oSearch_Pages
					->execute()
					->asObject($this->_asObject)
					->result();

				// Определим количество элементов
				$this->_foundRows();
			}
		}

		return $aSearch_Pages;
	}

	/**
	 * Set found rows
	 * @return self
	 */
	protected function _foundRows()
	{
		$queryBuilder = Core_QueryBuilder::select(array('FOUND_ROWS()', 'count'));
		$row = $queryBuilder->execute()->asAssoc()->current(FALSE);

		$this->total = $row['count'];

		return $this;
	}

	/**
	 * Delete search page
	 *
	 * @param int $module module's number, 0-15
	 * @param int $module_value_type value type, 0-15
	 * @param int $module_value_id entity id, 0-16777216
	 * @return self
	 */
	public function deleteSearchPage($module, $module_value_type, $module_value_id)
	{
		$oSearch_Page = Core_Entity::factory('Search_Page');
		$oSearch_Page->queryBuilder()
			->where('module', '=', $module)
			->where('module_value_type', '=', $module_value_type)
			->where('module_value_id', '=', $module_value_id)
			->limit(1);

		$oSearch_Page->deleteAll(FALSE);

		return $this;
	}
}