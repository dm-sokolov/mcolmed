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

class HostDev_Redirect_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'hostdev_redirect';
	
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'hostdev_redirects';

	/**
	 * Backend property
	 * @var mixed
	 */
	public $img = 0;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $comment_field = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array();

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array('site' => array());

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array('deleted','site_id');

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'informationsystem_id' => 0,
		'informationsystem_item_id' => 0,
		'informationsystem_group_id' => 0,
		'shop_id' => 0,
		'shop_group_id' => 0,
		'shop_item_id' => 0
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'hostdev_redirects.id' => 'ASC',
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);
	}
	
	public function getName() {
		$this->old_url();
		echo ' => ';
		$this->new_url();
	}
	
	public function old_url() {
		$oRedirect = $this;
		$href = $oRedirect->old_url;
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')->value(
			htmlspecialchars($href)
		);
		$oCore_Html_Entity_Div
				->add(
					Core::factory('Core_Html_Entity_A')
						->href($href)
						->target('_blank')
						->add(
							Core::factory('Core_Html_Entity_Img')
							->src('/admin/images/new_window.gif')
							->class('img_line')
						)
				);
		$oCore_Html_Entity_Div->execute();
	}
	
	public function referer() {
		if ($this->referer != '') {
			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')->value(
				htmlspecialchars($this->referer)
			);
			$oCore_Html_Entity_Div
					->add(
						Core::factory('Core_Html_Entity_A')
							->href($this->referer)
							->target('_blank')
							->add(
								Core::factory('Core_Html_Entity_Img')
								->src('/admin/images/new_window.gif')
								->class('img_line')
							)
					);
			$oCore_Html_Entity_Div->execute();
			return;
			
		}
		echo '<div style="color: #CEC3A3; text-align: center">—</div>';
	}
	
	public function new_url() {
		$oRedirect = $this;
		$oAlias = $oRedirect->site->getCurrentAlias();

		$href = HostDev_Redirect_Controller_Launch::getNewUrl($oRedirect);
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')->value(
			htmlspecialchars($href)
		);
		$oCore_Html_Entity_Div
				->add(
					Core::factory('Core_Html_Entity_A')
						->href($href)
						->target('_blank')
						->add(
							Core::factory('Core_Html_Entity_Img')
							->src('/admin/images/new_window.gif')
							->class('img_line')
						)
				);
		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		return parent::delete($primaryKey);
	}


	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->save();

		return $newObject;
	}
	
	/**
	 * Save object.
	 *
	 * @return self
	 */
	public function save()
	{
		parent::save();

		return $this;
	}

	/**
	 * Change item status
	 * @return self
	 */
	public function changeActive()
	{
		$this->active = 1 - $this->active;
		$this->save();

		return $this;
	}

	/**
	 * Change indexation mode
	 *	@return self
	 */
	public function changeIndexation()
	{
		$this->indexing = 1 - $this->indexing;
		return $this->save();
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event hostdev_redirect_model.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		return parent::getXml();
	}

	/**
	 * Create item
	 * @return self
	 */
	public function create()
	{
		$return = parent::create();

		return $return;
	}

}