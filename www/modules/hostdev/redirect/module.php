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

class HostDev_Redirect_Module extends Core_Module
{

	/**
	 * Module version
	 * @var string
	 */
	public $version = '1.35';
	
	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'hostdev_redirect';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2018-04-26';

	public function install() {

		$oModules = Core_Entity::factory('module')->getBypath($this->_moduleName);
		if (!is_null($oModules)) {
			$sqlFileUpdate = dirname(__FILE__) . DIRECTORY_SEPARATOR ."update-{$this->version}.sql";
			if (file_exists($sqlFileUpdate)) {
				$sqlUpdate = Core_File::read($sqlFileUpdate);
				Sql_Controller::instance()->execute($sqlUpdate);
			}
			return;
		}

		$oAdmin_Form = Core_Entity::factory('Admin_Form')->getByGuid('B67A8F0B-A90C-A1EF-89F2-F3EE1D62FF16');

		if (is_null($oAdmin_Form))
		{
			/**
			 * Создаем значения Admin_Word_Value
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Редиректы: список редиректов';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Redirects: Redirects list';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			/**
			 * Создаем форму
			 */
			$oAdmin_Form = Core_Entity::factory('Admin_Form');
			$oAdmin_Form->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form->on_page = 20;
			$oAdmin_Form->key_field = 'id';
			$oAdmin_Form->show_operations = 1;
			$oAdmin_Form->show_group_operations = 1;
			$oAdmin_Form->default_order_field = 'id';
			$oAdmin_Form->default_order_direction = 1;
			$oAdmin_Form->guid = 'B67A8F0B-A90C-A1EF-89F2-F3EE1D62FF16';
			$oAdmin_Form->save();

			/**
			 * Создаем поле формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Код';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'ID';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field');
			$oAdmin_Form_Field->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Field->name = 'id';
			$oAdmin_Form_Field->sorting = 10;
			$oAdmin_Form_Field->ico = '';
			$oAdmin_Form_Field->type = 1;
			$oAdmin_Form_Field->format = '';
			$oAdmin_Form_Field->allow_sorting = 1;
			$oAdmin_Form_Field->allow_filter = 1;
			$oAdmin_Form_Field->editable = 0;
			$oAdmin_Form_Field->filter_type = 0;
			$oAdmin_Form_Field->class = '';
			$oAdmin_Form_Field->width = '40px';
			$oAdmin_Form_Field->image = '';
			$oAdmin_Form_Field->link = '';
			$oAdmin_Form_Field->onclick = '';
			$oAdmin_Form_Field->list = '';
			$oAdmin_Form->add($oAdmin_Form_Field);

			/**
			 * Создаем поле формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = '';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = '';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field');
			$oAdmin_Form_Field->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Field->name = 'img';
			$oAdmin_Form_Field->sorting = 20;
			$oAdmin_Form_Field->ico = '';
			$oAdmin_Form_Field->type = 7;
			$oAdmin_Form_Field->format = '';
			$oAdmin_Form_Field->allow_sorting = 0;
			$oAdmin_Form_Field->allow_filter = 0;
			$oAdmin_Form_Field->editable = 0;
			$oAdmin_Form_Field->filter_type = 0;
			$oAdmin_Form_Field->class = 'hidden-xxs text-center';
			$oAdmin_Form_Field->width = '25px';
			$oAdmin_Form_Field->image = '0=/admin/images/page_shortcut.gif==fa fa-link';
			$oAdmin_Form_Field->link = '';
			$oAdmin_Form_Field->onclick = '';
			$oAdmin_Form_Field->list = '';
			$oAdmin_Form->add($oAdmin_Form_Field);

			/**
			 * Создаем поле формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Старый URL';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Old URL';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field');
			$oAdmin_Form_Field->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Field->name = 'old_url';
			$oAdmin_Form_Field->sorting = 30;
			$oAdmin_Form_Field->ico = '';
			$oAdmin_Form_Field->type = 10;
			$oAdmin_Form_Field->format = '';
			$oAdmin_Form_Field->allow_sorting = 0;
			$oAdmin_Form_Field->allow_filter = 0;
			$oAdmin_Form_Field->editable = 0;
			$oAdmin_Form_Field->filter_type = 0;
			$oAdmin_Form_Field->class = '';
			$oAdmin_Form_Field->width = '';
			$oAdmin_Form_Field->image = '';
			$oAdmin_Form_Field->link = '';
			$oAdmin_Form_Field->onclick = '';
			$oAdmin_Form_Field->list = '';
			$oAdmin_Form->add($oAdmin_Form_Field);

			/**
			 * Создаем поле формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Новый URL';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'New URL';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field');
			$oAdmin_Form_Field->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Field->name = 'new_url';
			$oAdmin_Form_Field->sorting = 31;
			$oAdmin_Form_Field->ico = '';
			$oAdmin_Form_Field->type = 10;
			$oAdmin_Form_Field->format = '';
			$oAdmin_Form_Field->allow_sorting = 0;
			$oAdmin_Form_Field->allow_filter = 0;
			$oAdmin_Form_Field->editable = 0;
			$oAdmin_Form_Field->filter_type = 0;
			$oAdmin_Form_Field->class = '';
			$oAdmin_Form_Field->width = '';
			$oAdmin_Form_Field->image = '';
			$oAdmin_Form_Field->link = '';
			$oAdmin_Form_Field->onclick = '';
			$oAdmin_Form_Field->list = '';
			$oAdmin_Form->add($oAdmin_Form_Field);

			/**
			 * Создаем поле формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Реферер';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Referer';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field');
			$oAdmin_Form_Field->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Field->name = 'referer';
			$oAdmin_Form_Field->sorting = 32;
			$oAdmin_Form_Field->ico = '';
			$oAdmin_Form_Field->type = 10;
			$oAdmin_Form_Field->format = '';
			$oAdmin_Form_Field->allow_sorting = 0;
			$oAdmin_Form_Field->allow_filter = 0;
			$oAdmin_Form_Field->editable = 0;
			$oAdmin_Form_Field->filter_type = 0;
			$oAdmin_Form_Field->class = '';
			$oAdmin_Form_Field->width = '';
			$oAdmin_Form_Field->image = '';
			$oAdmin_Form_Field->link = '';
			$oAdmin_Form_Field->onclick = '';
			$oAdmin_Form_Field->list = '';
			$oAdmin_Form->add($oAdmin_Form_Field);

			/**
			 * Создаем поле формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = '';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = '';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field');
			$oAdmin_Form_Field->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Field->name = 'active';
			$oAdmin_Form_Field->sorting = 35;
			$oAdmin_Form_Field->ico = 'fa fa-lightbulb-o';
			$oAdmin_Form_Field->type = 7;
			$oAdmin_Form_Field->format = '';
			$oAdmin_Form_Field->allow_sorting = 0;
			$oAdmin_Form_Field->allow_filter = 0;
			$oAdmin_Form_Field->editable = 0;
			$oAdmin_Form_Field->filter_type = 0;
			$oAdmin_Form_Field->class = 'hidden-xxs text-center';
			$oAdmin_Form_Field->width = '25px';
			$oAdmin_Form_Field->image = '0 = /admin/images/not_check.gif=Disabled=fa fa-lightbulb-o fa-inactive
		1 = /admin/images/check.gif=Enabled=fa fa-lightbulb-o fa-active';
			$oAdmin_Form_Field->link = '/admin/hostdev/redirect/index.php?hostcms[action]=changeActive&hostcms[checked][0][{id}]=1';
			$oAdmin_Form_Field->onclick = '$.adminLoad({path: \'/admin/hostdev/redirect/index.php\',additionalParams: \'hostcms[checked][0][{id}]=1\', action: \'changeActive\', windowId: \'{windowId}\'}); return false';
			$oAdmin_Form_Field->list = '';
			$oAdmin_Form->add($oAdmin_Form_Field);

			/**
			 * Создаем действие формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Активность';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Activity';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action');
			$oAdmin_Form_Action->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Action->name = 'changeActive';
			$oAdmin_Form_Action->picture = '';
			$oAdmin_Form_Action->icon = '';
			$oAdmin_Form_Action->color = '';
			$oAdmin_Form_Action->single = '0';
			$oAdmin_Form_Action->group = '0';
			$oAdmin_Form_Action->sorting = '0';
			$oAdmin_Form_Action->dataset = '-1';
			$oAdmin_Form_Action->confirm = '0';
			$oAdmin_Form->add($oAdmin_Form_Action);

			/**
			 * Создаем действие формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Редактировать URL';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Edit URL';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action');
			$oAdmin_Form_Action->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Action->name = 'edit';
			$oAdmin_Form_Action->picture = '/admin/images/edit.gif';
			$oAdmin_Form_Action->icon = 'fa fa-pencil';
			$oAdmin_Form_Action->color = 'palegreen';
			$oAdmin_Form_Action->single = '1';
			$oAdmin_Form_Action->group = '0';
			$oAdmin_Form_Action->sorting = '10';
			$oAdmin_Form_Action->dataset = '-1';
			$oAdmin_Form_Action->confirm = '0';
			$oAdmin_Form->add($oAdmin_Form_Action);

			/**
			 * Создаем действие формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Удалить URL';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Delete URL';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action');
			$oAdmin_Form_Action->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Action->name = 'markDeleted';
			$oAdmin_Form_Action->picture = '/admin/images/delete.gif';
			$oAdmin_Form_Action->icon = 'fa fa-trash-o';
			$oAdmin_Form_Action->color = 'darkorange';
			$oAdmin_Form_Action->single = '1';
			$oAdmin_Form_Action->group = '1';
			$oAdmin_Form_Action->sorting = '30';
			$oAdmin_Form_Action->dataset = '-1';
			$oAdmin_Form_Action->confirm = '1';
			$oAdmin_Form->add($oAdmin_Form_Action);

			/**
			 * Создаем действие формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Активность';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Activity';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action');
			$oAdmin_Form_Action->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Action->name = 'changeActive';
			$oAdmin_Form_Action->picture = '';
			$oAdmin_Form_Action->icon = '';
			$oAdmin_Form_Action->color = '';
			$oAdmin_Form_Action->single = '0';
			$oAdmin_Form_Action->group = '0';
			$oAdmin_Form_Action->sorting = '100';
			$oAdmin_Form_Action->dataset = '-1';
			$oAdmin_Form_Action->confirm = '0';
			$oAdmin_Form->add($oAdmin_Form_Action);

			/**
			 * Создаем действие формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Загрузка информационных групп';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Loading information groups';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action');
			$oAdmin_Form_Action->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Action->name = 'loadGroupsList';
			$oAdmin_Form_Action->picture = '';
			$oAdmin_Form_Action->icon = '';
			$oAdmin_Form_Action->color = '';
			$oAdmin_Form_Action->single = '0';
			$oAdmin_Form_Action->group = '0';
			$oAdmin_Form_Action->sorting = '100';
			$oAdmin_Form_Action->dataset = '-1';
			$oAdmin_Form_Action->confirm = '0';
			$oAdmin_Form->add($oAdmin_Form_Action);

			/**
			 * Создаем действие формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Загрузка групп интернет-магазина';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Loading of groups online store';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action');
			$oAdmin_Form_Action->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Action->name = 'loadShopGroupsList';
			$oAdmin_Form_Action->picture = '';
			$oAdmin_Form_Action->icon = '';
			$oAdmin_Form_Action->color = '';
			$oAdmin_Form_Action->single = '0';
			$oAdmin_Form_Action->group = '0';
			$oAdmin_Form_Action->sorting = '110';
			$oAdmin_Form_Action->dataset = '-1';
			$oAdmin_Form_Action->confirm = '0';
			$oAdmin_Form->add($oAdmin_Form_Action);

			/**
			 * Создаем действие формы
			 */
			$oAdmin_Word = Core_Entity::factory('Admin_Word')->save();

			$oAdmin_Word_Value_RU = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_RU->admin_language_id = 1;
			$oAdmin_Word_Value_RU->name = 'Загрузка товаров интернет-магазина';
			$oAdmin_Word->add($oAdmin_Word_Value_RU);

			$oAdmin_Word_Value_EN = Core_Entity::factory('Admin_Word_Value');
			$oAdmin_Word_Value_EN->admin_language_id = 2;
			$oAdmin_Word_Value_EN->name = 'Loading of goods online store';
			$oAdmin_Word->add($oAdmin_Word_Value_EN);

			$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action');
			$oAdmin_Form_Action->admin_word_id = $oAdmin_Word->id;
			$oAdmin_Form_Action->name = 'loadShopItemList';
			$oAdmin_Form_Action->picture = '';
			$oAdmin_Form_Action->icon = '';
			$oAdmin_Form_Action->color = '';
			$oAdmin_Form_Action->single = '0';
			$oAdmin_Form_Action->group = '0';
			$oAdmin_Form_Action->sorting = '120';
			$oAdmin_Form_Action->dataset = '-1';
			$oAdmin_Form_Action->confirm = '0';
			$oAdmin_Form->add($oAdmin_Form_Action);
			
			$dumpfile = Core_File::read(dirname(__FILE__) . DIRECTORY_SEPARATOR .'install.sql');
			Sql_Controller::instance()->execute($dumpfile);
		}
	}
	
	public function uninstall() {
		//Sql_Controller::instance()->execute('DROP TABLE `redirects`');
	}
	
	
    static public function onLoadSkinConfig($object, $args)
    {
        // Load config
        $aConfig = $object->getConfig();

		// Add module into 'content' section, see config.php
		if (!isset($aConfig['adminMenu']['hostdev'])) {
			$aConfig['adminMenu']['hostdev'] = array(
				'ico' => 'fa fa-code',
				'caption' => 'HostDev.pw',
				'modules' => array('hostdev_redirect')
			);
		} else {
			$aConfig['adminMenu']['hostdev']['modules'][] = 'hostdev_redirect';
		}
        // Set new config
        $object->setConfig($aConfig);
    }
	
	static public function _setEvents() {

		if (Core::moduleIsActive('hostdev_redirect')) {
			Core_Event::attach('Skin_Bootstrap.onLoadSkinConfig', array('Hostdev_Redirect_Module', 'onLoadSkinConfig'));
			Core_Event::attach('Core_Command_Controller_Default.onBeforeShowAction', array('HostDev_Redirect_Controller_Launch', 'onBeforeShowAction'));
	
		//	Core_Event::attach('Core_Response.onBeforeShowBody', array('HostDev_Redirect_Controller_Launch', 'onBeforeshowBody'));
		//	Core_Event::attach('Core_Response.onAfterShowBody', array('HostDev_Redirect_Controller_Launch', 'onAftershowBody'));
		}
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		Core_Event::attach('Core.onAfterInitConstants', array('Hostdev_Redirect_Module','_setEvents'));

		$this->menu = array(
			array(
				'sorting'=>30,
				'block'=>0,
				'ico' => 'fa fa-refresh',
				'name'=>Core::_('hostdev_redirect.menu'),
				'href'=>"/admin/hostdev/redirect/index.php",
				'onclick'=>"$.adminLoad({path: '/admin/hostdev/redirect/index.php'}); return false"
			)
		);
	}



}