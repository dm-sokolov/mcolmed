<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead_Shop_Item_Model
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Shop_Item_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $sum = NULL;

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'rate' => 0,
		'price' => 0,
		'shop_item_id' => 0,
		'quantity' => 0
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'lead' => array(),
		'shop_item' =>array(),
		'shop_currency' => array(),
		'shop_warehouse' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'lead_shop_items.id' => 'ASC',
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'price',
		'deleted',
		'user_id',
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
	 * Get order sum with currency name
	 * @return string
	 */
	public function sum()
	{
		return htmlspecialchars(
			sprintf("%.2f %s", $this->getAmount(), $this->Shop_Currency->name)
		);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->shop_item_id)
		{
			$additionalProperties = "shop_id={$this->Shop_Item->shop_id}&shop_group_id={$this->Shop_Item->shop_group_id}";

			$prevWindowId = $oAdmin_Form_Controller->getWindowId();

			$return = sprintf(
				'<a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a>',
				htmlspecialchars($oAdmin_Form_Controller->window('id_content')->getAdminActionLoadHref('/admin/shop/item/index.php', 'edit', NULL, 1, $this->shop_item_id, $additionalProperties)),
				htmlspecialchars($this->name)
			);

			$oAdmin_Form_Controller->window($prevWindowId);

			return $return;
		}
		else
		{
			return htmlspecialchars($this->name);
		}
	}

	/**
	 * Get order's item tax
	 * @return float
	 */
	public function getTax()
	{
		return Shop_Controller::instance()->round($this->price * $this->rate / 100);
	}

	/**
	 * Get order's item price
	 * @return float
	 */
	public function getPrice()
	{
		return Shop_Controller::instance()->round($this->price + $this->getTax());
	}

	/**
	 * Get sum of order's item
	 * @return float
	 */
	public function getAmount()
	{
		return Shop_Controller::instance()->round(
			// Цена каждого товара откругляется
			Shop_Controller::instance()->round($this->price + $this->getTax()) * $this->quantity
		);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event lead_shop_item.onBeforeGetRelatedSite
	 * @hostcms-event lead_shop_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Lead->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}