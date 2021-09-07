<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Search Module.
 *
 * @package HostCMS
 * @subpackage Search
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Search_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.9';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2021-08-23';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'search';

	/**
	 * List of Schedule Actions
	 * @var array
	 */
	protected $_scheduleActions = array(
		0 => 'reindex'
	);
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if (-827242328 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
		{
			throw new Core_Exception(base64_decode('TW9kdWxlIFNlYXJjaCBpcyBmb3JiaWRkZW4u'), array(), 0, FALSE, 0, FALSE);
		}
	}
	
	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 130,
				'block' => 1,
				'ico' => 'fa fa-search',
				'name' => Core::_('Search.menu'),
				'href' => "/admin/search/index.php",
				'onclick' => "$.adminLoad({path: '/admin/search/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
	
	/**
	 * Notify module on the action on schedule
	 * @param int $action action number
	 * @param int $entityId entity ID
	 * @return array
	 */
	public function callSchedule($action, $entityId)
	{
		switch ($action)
		{
			// Recount informationsystem
			case 0:
				@set_time_limit(9000);

				$Search_Controller = Search_Controller::instance();
				$Search_Controller->truncate();

				// Цикл по модулям
				$oModules = Core_Entity::factory('Module');
				$oModules->queryBuilder()
					->where('modules.active', '=', 1)
					->where('modules.indexing', '=', 1);

				$aModules = $oModules->findAll(FALSE);

				Core_Session::start();

				$step = 500;
				foreach ($aModules as $oModule)
				{
					//echo "\nModule ", $oModule->path;
					
					$oModule->loadModule();
					
					if (!is_null($oModule->Core_Module))
					{
						if (method_exists($oModule->Core_Module, 'indexing'))
						{
							$offset
								= $_SESSION['search_block']
								= $_SESSION['previous_step']
								= $_SESSION['last_limit'] = 0;

							do {
								$previousSearchBlock = Core_Array::get($_SESSION, 'search_block');

								$result = $oModule->Core_Module->indexing($offset, $step);
								$count = $result ? count($result) : 0;

								$count && $Search_Controller->indexingSearchPages($result);

								// Больше, т.к. некоторые модули могут возвращать больше проиндексированных элементов, чем запрошено, например, форумы
								if ($count >= $step)
								{
									// Если предыдущая индексация шла в несколько этапов, лимит сбрасывается для нового шага
									if (Core_Array::get($_SESSION, 'search_block') != $previousSearchBlock)
									{
										$offset = 0;
									}

									$offset += $_SESSION['last_limit'] > 0
										? $_SESSION['last_limit']
										: $step;
								}

								Core_ObjectWatcher::clear();
								Search_Stemmer::instance('ru')->clearCache();

								//$offset += $step;
							} while ($result && $count >= $step);
						}
					}
				}
			break;
		}
	}
}