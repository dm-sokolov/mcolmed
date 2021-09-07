<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Hostcms_Replace_Controller
 *
 * @package HostCMS 6\Hostcms_Replace
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Hostcms_Replace_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'text',
		'replace',
		'mode',
	);

	/**
	 * Main config array
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Return data array
	 * @var array
	 */
	protected $_return = array();

	/**
	 * Site
	 * @var Site_Model
	 */
	protected $_oSite = NULL;

	/**
	 * Constructor.
	 * @param Site_Model $oSite Site object
	 */
	public function __construct(Site_Model $oSite)
	{
		parent::__construct();

		$this->_oSite = $oSite;

		$this->_config = Core_Config::instance()->get('hostcms_replace_config', array());

		$this->mode = 0;

		if (!isset($this->_config[$this->_oSite->id]))
		{
			throw new Core_Exception("Config for current site doesn`t exist!");
		}
	}

	/**
	 * Get data by model name.
	 * @param string $sModelName model name
	 * @return array
	 */
	protected function _getData($sModelName, $offset, $limit)
	{
		switch ($sModelName)
		{
			case 'document':
				$oDocuments = Core_Entity::factory('Document');
				$oDocuments->queryBuilder()
					->where('documents.site_id', '=', $this->_oSite->id)
					->open()
						->where('documents.name', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('documents.text', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
					->close()
					->clearOrderBy()
					->orderBy('documents.id', 'ASC')
					->offset($offset)
					->limit($limit);

				$aEntities = $oDocuments->findAll(FALSE);
			break;
			case 'informationsystem_group':
				$oInformationsystem_Groups = Core_Entity::factory('Informationsystem_Group');
				$oInformationsystem_Groups->queryBuilder()
					->leftJoin('informationsystems', 'informationsystem_groups.informationsystem_id', '=', 'informationsystems.id')
					->where('informationsystems.site_id', '=', $this->_oSite->id)
					->open()
						->where('informationsystem_groups.name', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_groups.description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_groups.seo_title', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_groups.seo_description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_groups.seo_keywords', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
					->close()
					->clearOrderBy()
					->orderBy('informationsystem_groups.id', 'ASC')
					->offset($offset)
					->limit($limit);

				$aEntities = $oInformationsystem_Groups->findAll(FALSE);
			break;
			case 'informationsystem_item':
				$oInformationsystem_Items = Core_Entity::factory('Informationsystem_Item');
				$oInformationsystem_Items->queryBuilder()
					->leftJoin('informationsystems', 'informationsystem_items.informationsystem_id', '=', 'informationsystems.id')
					->where('informationsystems.site_id', '=', $this->_oSite->id)
					->open()
						->where('informationsystem_items.text', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_items.description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_items.name', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_items.seo_title', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_items.seo_description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('informationsystem_items.seo_keywords', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
					->close()
					->clearOrderBy()
					->orderBy('informationsystem_items.id', 'ASC')
					->offset($offset)
					->limit($limit);

				$aEntities = $oInformationsystem_Items->findAll(FALSE);
			break;
			case 'shop_group':
				$oShop_Groups = Core_Entity::factory('Shop_Group');
				$oShop_Groups->queryBuilder()
					->leftJoin('shops', 'shop_groups.shop_id', '=', 'shops.id')
					->where('shops.site_id', '=', $this->_oSite->id)
					->open()
						->where('shop_groups.name', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_groups.description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_groups.seo_title', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_groups.seo_description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_groups.seo_keywords', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
					->close()
					->clearOrderBy()
					->orderBy('shop_groups.id', 'ASC')
					->offset($offset)
					->limit($limit);

				$aEntities = $oShop_Groups->findAll(FALSE);
			break;
			case 'shop_item':
				$oShop_Items = Core_Entity::factory('Shop_Item');
				$oShop_Items->queryBuilder()
					->leftJoin('shops', 'shop_items.shop_id', '=', 'shops.id')
					->where('shops.site_id', '=', $this->_oSite->id)
					->open()
						->where('shop_items.text', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_items.description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_items.name', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_items.seo_title', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_items.seo_description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('shop_items.seo_keywords', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
					->close()
					->clearOrderBy()
					->orderBy('shop_items.id', 'ASC')
					->offset($offset)
					->limit($limit);

				$aEntities = $oShop_Items->findAll(FALSE);
			break;
			case 'structure':
				$oDocuments = Core_Entity::factory('Structure');
				$oDocuments->queryBuilder()
					->where('structures.site_id', '=', $this->_oSite->id)
					->open()
						->where('structures.name', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('structures.seo_title', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('structures.seo_description', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
						->setOr()
						->where('structures.seo_keywords', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($this->text) . '%')
					->close()
					->clearOrderBy()
					->orderBy('structures.id', 'ASC')
					->offset($offset)
					->limit($limit);

				$aEntities = $oDocuments->findAll(FALSE);
			break;
			default:
				throw new Core_Exception("getData: Wrong '{$sModelName}' model");
			break;
		}

		return $aEntities;
	}

	/**
	 * Get action link for entity.
	 * @param string $sModelName model name
	 * @param object $oEntity entity object
	 * @return string
	 */
	protected function _getAction($sModelName, $oEntity)
	{
		switch ($sModelName)
		{
			case 'document':
				$sAction = "/admin/document/index.php?hostcms[action]=edit&document_dir_id={$oEntity->document_dir_id}&hostcms[checked][1][{$oEntity->id}]=1";
			break;
			case 'informationsystem_group':
				$sAction = "/admin/informationsystem/item/index.php?hostcms[action]=edit&informationsystem_id={$oEntity->informationsystem_id}&informationsystem_group_id={$oEntity->parent_id}&hostcms[checked][0][{$oEntity->id}]=1";
			break;
			case 'informationsystem_item':
				$sAction = "/admin/informationsystem/item/index.php?hostcms[action]=edit&informationsystem_id={$oEntity->informationsystem_id}&informationsystem_group_id={$oEntity->informationsystem_group_id}&hostcms[checked][1][{$oEntity->id}]=1";
			break;
			case 'shop_group':
				$sAction = "/admin/shop/item/index.php?hostcms[action]=edit&shop_id={$oEntity->shop_id}&shop_group_id={$oEntity->parent_id}&hostcms[checked][0][{$oEntity->id}]=1";
			break;
			case 'shop_item':
				$sAction = "/admin/shop/item/index.php?hostcms[action]=edit&shop_id={$oEntity->shop_id}&shop_group_id={$oEntity->shop_group_id}&hostcms[checked][1][{$oEntity->id}]=1";
			break;
			case 'structure':
				$sAction = "/admin/structure/index.php?hostcms[action]=edit&hostcms[operation]=&parent_id={$oEntity->parent_id}&hostcms[checked][0][{$oEntity->id}]=1";
			break;
			default:
				throw new Core_Exception("getAction: Wrong '{$sModelName}' model");
			break;
		}

		return $sAction;
	}

	/**
	 * Executes the business logic.
	 * @return array
	 */
	public function execute()
	{
		if (isset($this->_config[$this->_oSite->id]))
		{
			foreach ($this->_config[$this->_oSite->id] as $sModelName => $value)
			{
				if ($value)
				{
					$offset = 0;
					$limit = 500;

					do {
						$aEntities = $this->_getData($sModelName, $offset, $limit);

						foreach ($aEntities as $oEntity)
						{
							if ($this->mode)
							{
								switch ($sModelName)
								{
									case 'document':
										$sName = str_replace($this->text, $this->replace, $oEntity->name, $count1);
										$sText = str_replace($this->text, $this->replace, $oEntity->text, $count2);

										if ($count1 || $count2)
										{
											// Create revision
											Core::moduleIsActive('revision') && $oEntity->backupRevision();

											$oEntity->name = $sName;
											$oEntity->text = $sText;
											$oEntity->save();
										}
									break;
									case 'informationsystem_group':
									case 'shop_group':
										$sName = str_replace($this->text, $this->replace, $oEntity->name, $count1);
										$sDescription = str_replace($this->text, $this->replace, $oEntity->description, $count2);
										$sSeoTitle = str_replace($this->text, $this->replace, $oEntity->seo_title, $count3);
										$sSeoDescription = str_replace($this->text, $this->replace, $oEntity->seo_description, $count4);
										$sSeoKeywords = str_replace($this->text, $this->replace, $oEntity->seo_keywords, $count5);

										if ($count1 || $count2 || $count3 || $count4 || $count5)
										{
											// Create revision
											Core::moduleIsActive('revision') && $oEntity->backupRevision();

											$oEntity->name = $sName;
											$oEntity->description = $sDescription;

											$oEntity->seo_title = $sSeoTitle;
											$oEntity->seo_description = $sSeoDescription;
											$oEntity->seo_keywords = $sSeoKeywords;

											$oEntity->save();
										}
									break;
									case 'informationsystem_item':
									case 'shop_item':
										$sName = str_replace($this->text, $this->replace, $oEntity->name, $count1);
										$sText = str_replace($this->text, $this->replace, $oEntity->text, $count2);
										$sDescription = str_replace($this->text, $this->replace, $oEntity->description, $count3);
										$sSeoTitle = str_replace($this->text, $this->replace, $oEntity->seo_title, $count4);
										$sSeoDescription = str_replace($this->text, $this->replace, $oEntity->seo_description, $count5);
										$sSeoKeywords = str_replace($this->text, $this->replace, $oEntity->seo_keywords, $count6);

										if ($count1 || $count2 || $count3 || $count4 || $count5 || $count6)
										{
											// Create revision
											Core::moduleIsActive('revision') && $oEntity->backupRevision();

											$oEntity->name = $sName;
											$oEntity->text = $sText;
											$oEntity->description = $sDescription;

											$oEntity->seo_title = $sSeoTitle;
											$oEntity->seo_description = $sSeoDescription;
											$oEntity->seo_keywords = $sSeoKeywords;

											$oEntity->save();
										}
									break;
									case 'structure':
										$sName = str_replace($this->text, $this->replace, $oEntity->name, $count1);
										$sSeoTitle = str_replace($this->text, $this->replace, $oEntity->seo_title, $count2);
										$sSeoDescription = str_replace($this->text, $this->replace, $oEntity->seo_description, $count3);
										$sSeoKeywords = str_replace($this->text, $this->replace, $oEntity->seo_keywords, $count4);

										if ($count1 || $count2 || $count3 || $count4)
										{
											// Create revision
											Core::moduleIsActive('revision') && $oEntity->backupRevision();

											$oEntity->name = $sName;

											$oEntity->seo_title = $sSeoTitle;
											$oEntity->seo_description = $sSeoDescription;
											$oEntity->seo_keywords = $sSeoKeywords;

											$oEntity->save();
										}
									break;
									default:
										throw new Core_Exception("replace: Wrong '{$sModelName}' model");
									break;
								}
							}

							$sAction = $this->_getAction($sModelName, $oEntity);

							$this->_return[] = array(
								'id' => $oEntity->id,
								'name' => $oEntity->name,
								'type' => Core::_('Hostcms_Replace.type_' . $sModelName),
								'link' => "<a href='{$sAction}' target='_blank'><i class='fa fa-external-link'></i></a>"
							);
						}

						$offset += $limit;

					} while(count($aEntities));
				}
			}

			$this->mode && Core_Message::show(Core::_('Hostcms_Replace.replace_success'));
		}

		return $this->_return;
	}
}