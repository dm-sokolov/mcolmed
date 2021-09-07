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

class Hostdev_Redirect_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	
	private $_shops = array();
	
	private $_infsys = array();
	public function getStructures($current_structure = 0) {
		
		$oStructure_Controller_Edit = new Structure_Controller_Edit($this->_Admin_Form_Action);
		// Выбор родительского раздела
		$oSelect_Parent_Id = Admin_Form_Entity::factory('Select')
			->options($oStructure_Controller_Edit->fillStructureList($this->_object->site_id, 0))
			->name('structure_id')
			->value($current_structure)
			->caption(Core::_('hostdev_redirect.structure'))
			->divAttr(array('id' => 'structure_id','class' => 'form-group col-sm-12 col-md-6 col-lg-6'))
			->style('width: 320px');
		return $oSelect_Parent_Id;
	}
	
	public function fillInformationsystems($iSiteId) {
		$iSiteId = intval($iSiteId);

		$aReturn = array();

		$aObjects = Core_Entity::factory('Site', $iSiteId)->informationsystems->findAll();

		foreach ($aObjects as $oObject)
		{
			$aReturn[$oObject->id] = $oObject->name;
		}
		$this->_infsys = $aReturn;
	}


	public function getInformationSystems($informationsystem_id = 0) {
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$oInformationsystem_Controller_Edit = new Informationsystem_Controller_Edit($this->_Admin_Form_Action);
		// Селектор с группой
		$oSelect_Informationsystems = Admin_Form_Entity::factory('Select')
			->options($this->_infsys)
			->name('informationsystem_id')
			->id('selinformationsystem_id')
			->value($informationsystem_id)
			->caption(Core::_('Property.informationsystem_id'))
			->style('width: 320px')
			->divAttr(array('id' => 'informationsystem_id','class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
			->onchange("getInformationSystems('{$windowId}', this.value);");
		return $oSelect_Informationsystems;
	}
	
	public function getInformationSystemGroups($informationsystem_id, $informationsystem_group_id = 0) {
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$oSelect_Group = Admin_Form_Entity::factory('Select')
			->name('informationsystem_group_id')
			->id('selinformationsystem_group_id')
			->caption(Core::_('Informationsystem_Item.informationsystem_group_id'))
			->options(
				array(' … ') + Informationsystem_Item_Controller_Edit::fillInformationsystemGroup($informationsystem_id, 0)
			)
			->value($informationsystem_group_id)
			->divAttr(array('id' => 'informationsystem_group_id','class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
			->style('width:320px;')
			->onchange("getInformationItems('{$windowId}', this.value);");
		return $oSelect_Group;
	}
	
	public function getInformationsystemItems($informationsystem_id, $informationsystem_item_id, $informationsystem_group_id = 0) {
		
		$oAdmin_Form_Entity_InfItems = Admin_Form_Entity::factory('Select')
			->style('width: 340px')
			->name("informationsystem_item_id")
			->id("selinformationsystem_item_id")
			->value($informationsystem_item_id)
			->caption(Core::_('Informationsystem_Item.information_system_top_menu_items'))
			->divAttr(array('id' => 'informationsystem_item_id','class' => 'form-group col-sm-12 col-md-4 col-lg-4'));
		$oInformationsystem = Core_Entity::factory('Informationsystem',$informationsystem_id);
		$oInformationsystem_Items = $oInformationsystem->Informationsystem_Items;

		switch ($oInformationsystem->items_sorting_direction)
		{
			case 1:
				$items_sorting_direction = 'DESC';
			break;
			case 0:
			default:
				$items_sorting_direction = 'ASC';
		}

		$oInformationsystem_Items
			->queryBuilder()
			->clearOrderBy();

		// Определяем поле сортировки информационных элементов
		switch ($oInformationsystem->items_sorting_field)
		{
			case 1:
				$oInformationsystem_Items
					->queryBuilder()
					->orderBy('informationsystem_items.name', $items_sorting_direction)
					->orderBy('informationsystem_items.sorting', $items_sorting_direction);
				break;
			case 2:
				$oInformationsystem_Items
					->queryBuilder()
					->orderBy('informationsystem_items.sorting', $items_sorting_direction)
					->orderBy('informationsystem_items.name', $items_sorting_direction);
				break;
			case 0:
			default:
				$oInformationsystem_Items
					->queryBuilder()
					->orderBy('informationsystem_items.datetime', $items_sorting_direction)
					->orderBy('informationsystem_items.sorting', $items_sorting_direction);
		}

		// Items
		$aInformationsystem_Items = $oInformationsystem_Items->getAllByinformationsystem_group_id($informationsystem_group_id);

		$aOptions = array(' … ');
		foreach ($aInformationsystem_Items as $oInformationsystem_Item)
		{
			$aOptions[$oInformationsystem_Item->id] = !$oInformationsystem_Item->shortcut_id
				? $oInformationsystem_Item->name
				: $oInformationsystem_Item->Informationsystem_Item->name;
		}
		$oAdmin_Form_Entity_InfItems->options($aOptions);
		return $oAdmin_Form_Entity_InfItems;
	}
	
	public function fillShops($iSiteId) {
		$iSiteId = intval($iSiteId);

		$aReturn = array();

		$aObjects = Core_Entity::factory('Site', $iSiteId)->shops->findAll();

		foreach ($aObjects as $oObject)
		{
			$aReturn[$oObject->id] = $oObject->name;
		}
		$this->_shops = $aReturn;

		return $aReturn;
	}
	
	public function getShops($shop_id = 0) {
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		// Селектор с группой
		$oSelect_Shops = Admin_Form_Entity::factory('Select')
			->options($this->_shops)
			->name('shop_id')
			->id('selshop_id')
			->value($shop_id)
			->caption(Core::_('hostdev_redirect.shop_id'))
			->style('width: 320px')
			->divAttr(array('id' => 'shop_id','class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
			->onchange("getShops('{$windowId}', this.value);");
		return $oSelect_Shops;
	}
	
	public function getShopGroups($shop_id, $shop_group_id = 0) {
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$oSelect_Group = Admin_Form_Entity::factory('Select')
			->name('shop_group_id')
			->id('selshop_group_id')
			->caption(Core::_('hostdev_redirect.shop_group_id'))
			->options(
				array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($shop_id)
			)
			->value($shop_group_id)
			->divAttr(array('id' => 'shop_group_id','class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
			->style('width:320px;')
			->onchange("getShopItems('{$windowId}', this.value);");
		return $oSelect_Group;
	}
	
	public function getShopItems($shop_id, $shop_item_id, $shop_group_id = 0) {
		
		$oAdmin_Form_Entity_InfItems = Admin_Form_Entity::factory('Select')
			->style('width: 340px')
			->name("shop_item_id")
			->id("selshop_item_id")
			->value($shop_item_id)
			->caption(Core::_('hostdev_redirect.shop_item_id'))
			->divAttr(array('id' => 'shop_item_id','class' => 'form-group col-sm-12 col-md-4 col-lg-4'));
		$oShop = Core_Entity::factory('Shop',$shop_id);
		$oShop_Items = $oShop->Shop_Items;

		switch ($oShop->items_sorting_direction)
		{
			case 1:
				$items_sorting_direction = 'DESC';
			break;
			case 0:
			default:
				$items_sorting_direction = 'ASC';
		}

		$oShop_Items
			->queryBuilder()
			->clearOrderBy();

		// Определяем поле сортировки информационных элементов
		switch ($oShop->items_sorting_field)
		{
			case 1:
				$oShop_Items
					->queryBuilder()
					->orderBy('shop_items.name', $items_sorting_direction)
					->orderBy('shop_items.sorting', $items_sorting_direction);
				break;
			case 2:
				$oShop_Items
					->queryBuilder()
					->orderBy('shop_items.sorting', $items_sorting_direction)
					->orderBy('shop_items.name', $items_sorting_direction);
				break;
			case 0:
			default:
				$oShop_Items
					->queryBuilder()
					->orderBy('shop_items.datetime', $items_sorting_direction)
					->orderBy('shop_items.sorting', $items_sorting_direction);
		}

		// Items
		$aShop_Items = $oShop_Items->getAllByshop_group_id($shop_group_id);

		$aOptions = array(' … ');
		foreach ($aShop_Items as $oShop_Item)
		{
			$aOptions[$oShop_Item->id] = !$oShop_Item->shortcut_id
				? $oShop_Item->name
				: $oShop_Item->Shop_Item->name;
		}
		$oAdmin_Form_Entity_InfItems->options($aOptions);
		return $oAdmin_Form_Entity_InfItems;
	}
	
	/**
	 * Set object
	 * @param $object object
	 * @return self
	 */
	public function setObject($object)
	{
		
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		
		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab->delete($this->getField('type'));
		//$oMainTab->delete($this->getField('site_id'));
		$oMainTab->delete($this->getField('old_url'));
		
		$oMainTab->delete($this->getField('new_url'));
		
		$oMainTab->delete($this->getField('referer'));
		$oMainTab->delete($this->getField('active'));
		$oMainTab->delete($this->getField('informationsystem_id'));
		$oMainTab->delete($this->getField('informationsystem_item_id'));
		$oMainTab->delete($this->getField('informationsystem_group_id'));
		$oMainTab->delete($this->getField('shop_id'));
		$oMainTab->delete($this->getField('shop_group_id'));
		$oMainTab->delete($this->getField('shop_item_id'));
		
		if (Core::moduleIsActive('shop')) {
			$this->fillShops(CURRENT_SITE);
		}
		if (Core::moduleIsActive('informationsystem')) {
			$this->fillInformationsystems(CURRENT_SITE);
		}
		
		$oRedirectsType = array('0' => 'URL','1' => Core::_('hostdev_redirect.structure'),);
		
		if (!empty($this->_shops)) {
			$oRedirectsType[4] = Core::_('hostdev_redirect.shop_group_id');
			$oRedirectsType[5] = Core::_('hostdev_redirect.shop_item_id');
		}
		
		if (!empty($this->_infsys)) {
			$oRedirectsType[2] = Core::_('hostdev_redirect.infgroup');
			$oRedirectsType[3] = Core::_('hostdev_redirect.infitem');
		}

		$Select_Type_Redirect = Admin_Form_Entity::factory('Select')
			->name('type')
			->id('type_id')
			->caption(Core::_('hostdev_redirect.type'))
			->style('width: 320px')
			->divAttr(array('id' => 'type','class' => 'form-group col-sm-12 col-md-6 col-lg-6'))
			->options($oRedirectsType)
			->value($this->_object->type) //
			->onchange( "ShowRedirectRows('{$windowId}', this.options[this.selectedIndex].value)");

		$oScript = Core::factory('Admin_Form_Entity_Code');
		
		$oScript->html('<script src="/admin/hostdev/redirect/redirect.js"></script>');
		$site_id = $this->getField('site_id');
		if (!$site_id->value) {
			$this->_object->site_id = CURRENT_SITE;
			$site_id->value(CURRENT_SITE);
		}
		$site_id->type('hidden');
		
		$oMainTab->delete($this->getField('append'));
		
		$this->getField('old_url')->divAttr(array('id' => 'old_url','class' => 'form-group col-sm-12 col-md-6 col-lg-6'))->class('form-control');
		$this->getField('referer')->divAttr(array('id' => 'referer','class' => 'form-group col-sm-12 col-md-6 col-lg-6'));
		
		$this->getField('active')->divAttr(array('id' => 'active','class' => 'form-group col-sm-12 col-md-6 col-lg-6'));
		$append = $this->getField('append')->divAttr(array('id' => 'append','class' => 'form-group col-sm-12 col-md-6 col-lg-6'));

		$old_url = $this->getField('old_url')->caption(Core::_('hostdev_redirect.old_url'));
		
		$new_url = $this->getField('new_url')->caption(Core::_('hostdev_redirect.new_url'))->divAttr(array('id' => 'default_value','class' => 'form-group col-sm-12 col-md-6 col-lg-6'));
		
		$oMainRow1->add($old_url);
		
		$referer = $this->getField('referer')->caption(Core::_('hostdev_redirect.referer'))->divAttr(array('id' => 'referer','class' => 'form-group col-sm-12 col-md-6 col-lg-6'));
		$oMainRow1->add($referer);
		
		$oMainTab->add($oScript);
		
		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
		
		$version = HOSTCMS_UPDATE_NUMBER;
		$oAdmin_Form_Entity_Code->html(
			"<script>window.hostcms = '{$version}'; ShowRedirectRows('{$windowId}', " . intval($this->_object->type) . ")</script>"
		);

		$oMainTab->add($oAdmin_Form_Entity_Code);
		
		$oMainRow2->add($Select_Type_Redirect);
		$oMainRow2->add( $this->getStructures($this->_object->new_url) );
		$oMainRow2->add($new_url);
		if (!empty($this->_infsys) && Core::moduleIsActive('informationsystem')) {
			$oMainRow5->add( $this->getInformationSystems($this->_object->informationsystem_id) );
			$oInfGroups = $this->getInformationSystemGroups($this->_object->informationsystem_id,$this->_object->informationsystem_group_id);
			if (!$this->_object->informationsystem_id) {
				$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
				$oAdmin_Form_Entity_Code->html(
					"<script>getInformationSystems('{$windowId}', $('#selinformationsystem_id').val())</script>"
				);
				$oMainTab->add($oAdmin_Form_Entity_Code);
			}
			$oMainRow5->add($oInfGroups);
			$oInfItems = $this->getInformationsystemItems($this->_object->informationsystem_id,
			$this->_object->informationsystem_item_id, $this->_object->informationsystem_group_id);
			$oMainRow5->add($oInfItems);
		}
		
		if (!empty($this->_shops) && Core::moduleIsActive('shop')) {
			$oMainRow8->add($this->getShops($this->_object->shop_id));
			$oMainRow8->add($this->getShopGroups($this->_object->shop_id, $this->_object->shop_group_id));
			$oMainRow8->add($this->getShopItems($this->_object->shop_id, $this->_object->shop_item_id, $this->_object->shop_group_id));
			
			if ($this->_object->shop_id < 1) {
				$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
				$oAdmin_Form_Entity_Code->html(
					"<script>getShops('{$windowId}', $('#selshop_id').val())</script>"
				);
				$oMainTab->add($oAdmin_Form_Entity_Code);
			}
		}

		$oMainRow9->add($this->getField('active'));
		
		$oMainRow9->add($append);

		$oAdditionalTab = $this->getTab('additional');
		
		$oAdditionalTab->active(0);

		$oAdditionalTab->add($site_id);
		
		$this->title(
			$this->_object->id
				? Core::_('hostdev_redirect.edit_title')
				: Core::_('hostdev_redirect.add_title'));

		return $this;
	}
	
	/**
	 * Processing of the form. Apply object fields.
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();
		$type = $this->_object->type;
		switch($type) {
			case 0:
			case 1:
			case 2:
			case 3: {
				if ($type >= 2) {
					$this->_object->new_url = '';
					$informationsystem_group_id = Core_Array::getPost('informationsystem_group_id', 0);
					if ($informationsystem_group_id) {
						$this->_object->informationsystem_group_id = $informationsystem_group_id;
					} else {
						$this->_object->informationsystem_group_id = 0;
						$this->_object->informationsystem_id = Core_Array::getPost('informationsystem_id', 0);
					}
				} elseif ($type <= 1) {
					if ($type == 1 ) {
						$structure_id = Core_Array::getPost('structure_id', 0);
						$this->_object->new_url = $structure_id;
					}
					$this->_object->informationsystem_group_id = 0;
					$this->_object->informationsystem_id = 0;
				}
				$this->_object->shop_id = 0;
				$this->_object->shop_group_id = 0;
				$this->_object->shop_item_id = 0;
				break;
			}
			case 4:
			case 5: {
				$this->_object->informationsystem_group_id = 0;
				$this->_object->informationsystem_id = 0;
				$this->_object->new_url = '';
				break;
			}
			default: {
				$this->_object->informationsystem_group_id = 0;
			}
		}

		$this->_object->save();

	}
	
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL) {
		return parent::execute($operation);
	}
}