<?php
/**
 * Search.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'search');

$sAdminFormAction = '/admin/search/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Search.title'))
	//->pageTitle(Core::_('Search.title'))
	;

ob_start();

$Search_Controller = Search_Controller::instance();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('Search.title'))
	->addMessage(
		Core_Message::get(
			Core::_('Search.search_index_items', $Search_Controller->getPageCount(CURRENT_SITE))
		)
	);

// Меню формы
$sSearchLogPath = '/admin/search/log/index.php';

$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus')
	->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Search_Log.title'))
			->icon('fa fa-list-ul')
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($sSearchLogPath, NULL, NULL, '')
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($sSearchLogPath, NULL, NULL, '')
			)
	)
	//->execute()
	;

?>
<div class="table-toolbar">
	<?php $oAdmin_Form_Entity_Menus->execute()?>
	<div class="clear"></div>
</div>
<?php

$bIndexingCompleted = TRUE;

if ($oAdmin_Form_Controller->getAction() == 'process')
{
	Core_Session::start();

	try
	{
		// Текущий пользователь
		$oUser = Core_Auth::getCurrentUser();

		// Read Only режим
		if (defined('READ_ONLY') && READ_ONLY || $oUser->read_only)
		{
			throw new Core_Exception(
				Core::_('User.demo_mode'), array(), 0, FALSE
			);
		}

		$topic = intval(Core_Array::getRequest('topic', 0));
		$timeout = intval(Core_Array::getRequest('timeout', 0));
		$limit = intval(Core_Array::getRequest('limit', 0));
		$step = intval(Core_Array::getRequest('step', 10));

		$bIndexingCompleted = FALSE;

		if ($topic == 0 && $limit == 0)
		{
			// Remove all indexed data
			$Search_Controller->truncate();

			//$_SESSION['search_block'] = 0;
			$_SESSION['previous_step'] = 0;
			$_SESSION['last_limit'] = 0;
		}

		$result = array();

		$count = 0;

		// Цикл по модулям
		$oModules = Core_Entity::factory('Module');
		$oModules->queryBuilder()
			->where('modules.active', '=', 1)
			->where('modules.indexing', '=', 1);

		$aModules = $oModules->findAll();

		if ($topic < count($aModules))
		{
			$previousSearchBlock = Core_Array::get($_SESSION, 'search_block');

			$oModule = $aModules[$topic]->loadModule();
			if (!is_null($oModule->Core_Module))
			{
				if (method_exists($oModule->Core_Module, 'indexing'))
				{
					$result = $oModule->Core_Module->indexing($limit, $step);
					$result && $count = count($result);
				}
			}

			$count != 0 && $Search_Controller->indexingSearchPages($result);

			// Больше, т.к. некоторые модули могут возвращать больше проиндексированных элементов, чем запрошено, например, форумы
			if ($count >= $step)
			{
				// Если предыдущая индексация шла в несколько этапов, лимит сбрасывается для нового шага
				if (Core_Array::get($_SESSION, 'search_block') != $previousSearchBlock)
				{
					$limit = 0;
				}

				$limit += $_SESSION['last_limit'] > 0
					? $_SESSION['last_limit']
					: $step;
			}
			else
			{
				$topic++;
				$limit = 0;
				$_SESSION['search_block'] = $_SESSION['previous_step'] = $_SESSION['last_limit'] = 0;
			}

			// Организуем редиректы для перехода от блока к блоку
			?>
			<p><?php echo Core::_('Search.search_indexed_all_sites', $oModule->name, $count, $timeout)?>
			<br />
			<?php
			$sAdditionalParams = "indexation=1&topic={$topic}&limit={$limit}&step={$step}&timeout={$timeout}";

			echo Core::_('Search.search_indexed_automatic_redirection_message',
				$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'process', NULL, 0, 0, $sAdditionalParams),
				$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'process', NULL, 0, 0, $sAdditionalParams)
			)?>
			</p>

			<script type="text/javascript">
			function set_location()
			{
				<?php echo $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'process', NULL, 0, 0, $sAdditionalParams)?>
			}
			setTimeout ('set_location()', <?php echo $timeout * 1000?>);
			</script>
			<?php
		}
		else
		{
			$bIndexingCompleted = TRUE;
		}
	}
	catch (Exception $e)
	{
		$sText = NULL;
		$oAdmin_View->addMessage(
			Core_Message::get($e->getMessage(), 'error')
		);
	}

	$bIndexingCompleted && $_SESSION['search_block'] = $_SESSION['previous_step'] = 0;
}

if ($bIndexingCompleted)
{
	// Clear Cache
	if (Core::moduleIsActive('cache'))
	{
		Core_Cache::instance(Core::$mainConfig['defaultCache'])->deleteAll('search');
	}

	Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->action($sAdminFormAction)
		->add(
			Admin_Form_Entity::factory('Select')
				->name('step')
				->caption(Core::_('Search.step'))
				->options(array(
					10 => 10,
					30 => 30,
					50 => 50,
					100 => 100,
					500 => 500,
					1000 => 1000
				))
				->style('width: 160px')
				->value(Core_Array::getPost('step', 100))
		)->add(
			Admin_Form_Entity::factory('Input')
				->name('timeout')
				->caption(Core::_('Search.timeout'))
				->style('width: 160px')
				->value(Core_Array::getPost('timeout', 0))
		)->add(
			Admin_Form_Entity::factory('Button')
				->name('process')
				->type('submit')
				->value(Core::_('Search.button'))
				->class('applyButton btn btn-blue')
				->onclick(
					$oAdmin_Form_Controller->getAdminSendForm('process', NULL, '')
				)
		)
		->execute();
}

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->message($oAdmin_View->message)
	->title(Core::_('Search.title'))
	->module($sModule)
	->execute();
