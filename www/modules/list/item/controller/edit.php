<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * List_Item Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage List
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class List_Item_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this->addSkipColumn('ico');

		if (!$object->id)
		{
			$object->list_id = intval(Core_Array::getGet('list_id'));
			$object->parent_id = intval(Core_Array::getGet('parent_id', 0));
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oList = $object->List;

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		if (!$this->_object->id)
		{
			// Удаляем стандартный <input>
			$oMainTab->delete($this->getField('value'));

			$oTextarea_ListItemName = Admin_Form_Entity::factory('Textarea')
				->cols(140)
				->rows(5)
				->caption(Core::_('List_Item.add_list_item_name'))
				->divAttr(array('class' => 'form-group col-xs-12'))
				->name('value');

			$oMainRow1->add($oTextarea_ListItemName);
		}
		else
		{
			$oMainTab
				->move($this->getField('value')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);
		}

		$oMainTab->move($this->getField('path')->divAttr(array('class' => 'form-group col-xs-12 col-md-6')), $oMainRow3);

		// Удаляем стандартный <input>
		$oAdditionalTab->delete($this->getField('parent_id'));

		$oList_Items = Core_Entity::factory('List_Item');
		$oList_Items->queryBuilder()
			->where('list_id', '=', $this->_object->list_id);

		$iCountListItems = $oList_Items->getCount();

		if ($iCountListItems < Core::$mainConfig['switchSelectToAutocomplete'])
		{
			// Селектор с родительским макетом
			$oSelect_ListItems = Admin_Form_Entity::factory('Select');
			$oSelect_ListItems
				->options(
					array(' … ') + $this->fillListItemParent(0, $this->_object->id)
				)
				->name('parent_id')
				->value($this->_object->parent_id)
				->caption(Core::_('List_Item.parent_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

			$oMainRow3->add($oSelect_ListItems);
		}
		else
		{
			$oListItemInput = Admin_Form_Entity::factory('Input')
				->caption(Core::_('List_Item.parent_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-md-6'))
				->name('parent_name');

			if ($this->_object->parent_id)
			{
				$oList_Item = Core_Entity::factory('List_Item', $this->_object->parent_id);
				$oListItemInput->value($oList_Item->value . ' [' . $oList_Item->id . ']');
			}

			$oListItemInputHidden = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->name('parent_id')
				->value($this->_object->parent_id)
				->type('hidden');

			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$oCore_Html_Entity_Script_ListItem = Core::factory('Core_Html_Entity_Script')
			->value("
				$('#{$windowId} [name = parent_name]').autocomplete({
					source: function(request, response) {
						$.ajax({
							url: '/admin/list/item/index.php?autocomplete=1&show_parents=1&list_id={$this->_object->list_id}',
							dataType: 'json',
							data: {
								queryString: request.term
							},
							success: function(data) {
								response(data);
							}
						});
					},
					minLength: 1,
					create: function() {
						$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
							return $('<li></li>')
								.data('item.autocomplete', item)
								.append($('<a>').text(item.label))
								.appendTo(ul);
						}
						$(this).prev('.ui-helper-hidden-accessible').remove();
					},
					select: function(event, ui) {
						$('#{$windowId} [name = parent_id]').val(ui.item.id);
					},
					open: function() {
						$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
					},
					close: function() {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					}
				});
			");

			$oMainRow3
				->add($oListItemInput)
				->add($oListItemInputHidden)
				->add($oCore_Html_Entity_Script_ListItem);
		}

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '';

		$this->getField('color')
			->colorpicker(TRUE)
			->value($sColorValue);

		$oMainTab
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2)
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow4)
			->move($this->getField('icon')->divAttr(array('class' => 'form-group col-xs-12 col-md-3')), $oMainRow4)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-md-3')), $oMainRow4)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-md-3 margin-top-21')), $oMainRow4);

		$title = $this->_object->id
			? Core::_('List_Item.edit_title', $oList->name)
			: Core::_('List_Item.add_title', $oList->name);

		$this->title($title);

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iListItemParentId parent list item ID
	 * @param boolean $bExclude exclude list item ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillListItemParent($iListItemParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iListItemParentId = intval($iListItemParentId);
		$iLevel = intval($iLevel);

		$oList_Item_Parent = Core_Entity::factory('List_Item', $iListItemParentId);

		$aReturn = array();

		// Дочерние элементы
		$childrenListItems = $oList_Item_Parent->List_Items;
		$childrenListItems->queryBuilder()
			->where('list_id', '=', Core_Array::getGet('list_id', 0));

		$childrenListItems = $childrenListItems->findAll();

		if (count($childrenListItems))
		{
			foreach ($childrenListItems as $childrenListItem)
			{
				if ($bExclude != $childrenListItem->id)
				{
					$aReturn[$childrenListItem->id] = str_repeat('  ', $iLevel) . $childrenListItem->value;
					$aReturn += $this->fillListItemParent($childrenListItem->id, $bExclude, $iLevel + 1);
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$sValue = trim(Core_Array::getPost('value'));

			// Массив значений списка
			$aList_Items = explode("\n", $sValue);
			$sValue = trim(array_shift($aList_Items));

			$list_id = Core_Array::getPost('list_id');
			$id = Core_Array::getPost('id');
			$parent_id = Core_Array::getPost('parent_id');

			$oSameList_Items = Core_Entity::factory('List', $list_id)->List_Items;
			$oSameList_Items
				->queryBuilder()
				->where('list_items.parent_id', '=', $parent_id);

			$oSameList_Item = $oSameList_Items->getByValue($sValue);

			if (!is_null($oSameList_Item) && $oSameList_Item->id != $id)
			{
				$this->addMessage(
					Core_Message::get(Core::_('List_Item.add_lists_items_error'), 'error')
				);
				return TRUE;
			}
		}

		return parent::execute($operation);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event List_Item_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$id = $this->_object->id;

		$description = strval(Core_Array::getPost('description'));
		$icon = strval(Core_Array::getPost('icon'));

		if (!$id)
		{
			$sValue = trim(Core_Array::getPost('value'));

			// Массив значений списка
			$aList_Items = explode("\n", $sValue);

			$firstItem = trim(array_shift($aList_Items));

			$aRow = explode(';', $firstItem);

			// Sets value for first list_item
			if (isset($aRow[0]) && strlen($aRow[0]))
			{
				$this->_formValues['value'] = strval($aRow[0]);
				$this->_formValues['description'] = isset($aRow[1]) ? strval($aRow[1]) : $description;
				$this->_formValues['icon'] = isset($aRow[2]) ? strval($aRow[2]) : $icon;
			}
		}

		parent::_applyObjectProperty();

		if (!$id)
		{
			foreach ($aList_Items as $sValue)
			{
				$sValue = trim($sValue);

				$aRow = explode(';', $sValue);

				if (isset($aRow[0]) && strlen($aRow[0]))
				{
					$oSameList_Items = $this->_object->List->List_Items;
					$oSameList_Items
						->queryBuilder()
						->where('list_items.parent_id', '=', $this->_object->parent_id);

					$oSameList_Item = $oSameList_Items->getByValue($aRow[0], FALSE);

					if (is_null($oSameList_Item))
					{
						$oNewListItem = clone $this->_object;
						$oNewListItem->value = strval($aRow[0]);
						$oNewListItem->description = isset($aRow[1]) && strlen($aRow[1]) ? strval($aRow[1]) : $description;
						$oNewListItem->icon = isset($aRow[2]) && strlen($aRow[2]) ? strval($aRow[2]) : $icon;
						$oNewListItem->path = '';
						$oNewListItem->save();
					}
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}