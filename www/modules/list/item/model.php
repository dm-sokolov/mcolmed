<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * List_Item_Model
 *
 * @package HostCMS
 * @subpackage List
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class List_Item_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'value';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'list' => array(),
		'list_item' => array('foreign_key' => 'parent_id'),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'list_item' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'list_items.sorting' => 'ASC',
		'list_items.value' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'active' => 1,
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Change active mode
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function valueBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (strlen($this->color))
		{
			?><span class="list-item-color-badge" style="background-color: <?php echo htmlspecialchars($this->color)?>; box-shadow: 0 0 0 1px <?php echo Core_Str::hex2darker($this->color, 0.1)?>"></span><?php
		}

		$count = $this->List_Items->getCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square margin-left-5')
			->value($count)
			->execute();
	}

	/**
	 * Get parent list item
	 * @return List_Item|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('List_Item', $this->parent_id)
			: NULL;
	}

	/**
	 * Merge list item with another one
	 * @param List_Item_Model $oList_Item list item object
	 * @return self
	 */
	public function merge(List_Item_Model $oList_Item)
	{
		$this->sorting == 0
			&& $oList_Item->sorting > 0
			&& $this->sorting = $oList_Item->sorting;

		$this->description == ''
			&& $oList_Item->description != ''
			&& $this->description = $oList_Item->description;

		$this->icon == ''
			&& $oList_Item->icon != ''
			&& $this->icon = $oList_Item->icon;

		$this->save();

		$oProperties = Core_Entity::factory('Property');
		$oProperties->queryBuilder()
			->where('type', '=', 3)
			->where('list_id', '=', $oList_Item->list_id);

		$aProperties = $oProperties->findAll(FALSE);
		foreach ($aProperties as $oProperty)
		{
			Core_QueryBuilder::update('property_value_ints')
				->columns(array('value' => $this->id))
				->where('property_id', '=', $oProperty->id)
				->where('value', '=', $oList_Item->id)
				->execute();
		}

		$oList_Item->markDeleted();

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event list_item.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}
		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->List_Items->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Make url path
	 * @return self
	 * @hostcms-event list_item.onAfterMakePath
	 */
	public function makePath()
	{
		switch ($this->List->url_type)
		{
			case 0:
			default:
				// nothing to do
			break;
			case 1:
				try {
					Core::$mainConfig['translate'] && $sTranslated = Core_Str::translate($this->value);

					$this->path = Core::$mainConfig['translate'] && strlen($sTranslated)
						? $sTranslated
						: $this->value;

					$this->path = Core_Str::transliteration($this->path);

				} catch (Exception $e) {
					$this->path = Core_Str::transliteration($this->value);
				}
			break;
			case 2:
				if ($this->id)
				{
					$this->path = $this->id;
				}
			break;
		}

		Core_Event::notify($this->_modelName . '.onAfterMakePath', $this);

		return $this;
	}

	/**
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		if (is_null($this->path))
		{
			$this->makePath();
		}

		parent::save();

		if ($this->path == '' && !$this->deleted && $this->makePath())
		{
			$this->path != '' && $this->save();
		}

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event list_item.onBeforeGetRelatedSite
	 * @hostcms-event list_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->List->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}