<?php
/**
 * Leads.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'lead');

// Код формы
$iAdmin_Form_Id = 268;
$sAdminFormAction = '/admin/lead/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Lead.title'))
	->pageTitle(Core::_('Lead.title'))
	->addView('kanban', 'Lead_Controller_Kanban')
	// ->view('kanban')
	;

if (Core_Array::getPost('id') && (Core_Array::getPost('target_id') || Core_Array::getPost('sender_id')))
{
	$aJSON = array(
		'status' => '',
		'last_step' => 0,
		'type' => 0
	);

	$lead_id = Core_Array::getPost('lead_id')
		? intval(Core_Array::getPost('lead_id'))
		: intval(Core_Array::getPost('id'));

	$oLead = Core_Entity::factory('Lead')->getById($lead_id);

	if (!is_null($oLead))
	{
		$lead_status_id = Core_Array::getPost('lead_status_id')
			? intval(Core_Array::getPost('lead_status_id'))
			: intval(Core_Array::getPost('target_id'));

		$oLead_Status = Core_Entity::factory('Lead_Status')->getById($lead_status_id);

		if (!is_null($oLead_Status))
		{
			if ($oLead_Status->type != 1)
			{
				$previousStatusId = $oLead->lead_status_id;

				$oLead->lead_status_id = $lead_status_id;
				$oLead->save();

				if ($previousStatusId != $oLead->lead_status_id)
				{
					$oLead->notifyBotsChangeStatus();
				}

				$sNewLeadStepDatetime = Core_Date::timestamp2sql(time());

				$oCurrentUser = Core_Auth::getCurrentUser();

				Core_Entity::factory('Lead_Step')
					->lead_id($oLead->id)
					->lead_status_id($oLead->lead_status_id)
					->user_id($oCurrentUser->id)
					->datetime($sNewLeadStepDatetime)
					->save();

				$aJSON['type'] = $oLead_Status->type;
			}
			else
			{
				// Если тип "Успешный"
				if ($oLead_Status->type == 1)
				{
					$aJSON['last_step'] = 1;
					$aJSON['lead_status_id'] = $oLead_Status->id;
				}
			}

			$aJSON['status'] = 'success';
			$aJSON['lead_id'] = $lead_id;
			$aJSON['window_id'] = $oAdmin_Form_Controller->getWindowId();
			
			
		}
		else
		{
			$aJSON['status'] = 'errorLeadStatus';
		}
	}
	else
	{
		$aJSON['status'] = 'errorLead';
	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->img('/admin/images/add.gif')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Lead.menu_directory'))
		->icon('fa fa-book')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Lead.menu_need'))
				->icon('fa fa-puzzle-piece')
				->img('/admin/images/add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sNeedFormPath = '/admin/lead/need/index.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sNeedFormPath, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Lead.menu_maturity'))
				->icon('fa fa-circle')
				->img('/admin/images/add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sMaturityFormPath = '/admin/lead/maturity/index.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sMaturityFormPath, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Lead.menu_statuses'))
				->icon('fa fa-flag')
				->img('/admin/images/add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sStatusesFormPath = '/admin/lead/status/index.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sStatusesFormPath, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Crm_Source.siteuser_sources_title'))
				->icon('fa fa-user-plus')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/crm/source/index.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/crm/source/index.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Lead.menu_exchange'))
		->icon('fa fa-exchange')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Lead.import'))
				->icon('fa fa-download')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/lead/import/index.php', NULL, NULL)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/lead/import/index.php', NULL, NULL)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Lead.export'))
				->icon('fa fa-upload')
				->target('_blank')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportLeads', NULL, 0, 0)
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Глобальный поиск
$additionalParams = '';

$sGlobalSearch = trim(strval(Core_Array::getGet('globalSearch')));

$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html('
			<div class="row search-field margin-bottom-20">
				<div class="col-xs-12">
					<form action="' . $oAdmin_Form_Controller->getPath() . '" method="GET">
						<input type="text" name="globalSearch" class="form-control" placeholder="' . Core::_('Admin.placeholderGlobalSearch') . '" value="' . htmlspecialchars($sGlobalSearch) . '" />
						<i class="fa fa-times-circle no-margin" onclick="' . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), '', '', $additionalParams) . '"></i>
						<button type="submit" class="btn btn-default global-search-button" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('', '', $additionalParams) . '"><i class="fa fa-search fa-fw"></i></button>
					</form>
				</div>
			</div>
		')
);

$sGlobalSearch = Core_DataBase::instance()->escapeLike($sGlobalSearch);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Lead.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oLead_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Lead_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$oLead_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLead_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Изменить потребность"
$oAdminFormActionChangeNeed = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('changeNeed');

if ($oAdminFormActionChangeNeed && $oAdmin_Form_Controller->getAction() == 'changeNeed')
{
	$oLeadControllerNeed = Admin_Form_Action_Controller::factory(
		'Lead_Controller_Need', $oAdminFormActionChangeNeed
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLeadControllerNeed);
}

// Действие "Изменить зрелость"
$oAdminFormActionChangeMaturity = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('changeMaturity');

if ($oAdminFormActionChangeMaturity && $oAdmin_Form_Controller->getAction() == 'changeMaturity')
{
	$oLeadControllerMaturity = Admin_Form_Action_Controller::factory(
		'Lead_Controller_Maturity', $oAdminFormActionChangeMaturity
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLeadControllerMaturity);
}

// Действие экспорта
$oAdminFormActionExport = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('exportLeads');

if ($oAdminFormActionExport && $oAdmin_Form_Controller->getAction() == 'exportLeads')
{
	$oSite = Core_Entity::factory('Site', CURRENT_SITE);
	$Lead_Exchange_Export_Controller = new Lead_Exchange_Export_Controller($oSite);
	$Lead_Exchange_Export_Controller->execute();
}

// Действие "Изменить потребность"
$oAdminFormActionMorphLead = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('morphLead');

if ($oAdminFormActionMorphLead && $oAdmin_Form_Controller->getAction() == 'morphLead')
{
	$oLeadControllerMorph = Admin_Form_Action_Controller::factory(
		'Lead_Controller_Morph', $oAdminFormActionMorphLead
	);

	$oLeadControllerMorph
		->title(Core::_('Lead.morph_lead'))
		->buttonName(Core::_('Lead.morph'));

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLeadControllerMorph);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Lead')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('leads.*',
			array(Core_QueryBuilder::expression('CONCAT(COALESCE(leads.surname, \'\'), \' \', COALESCE(leads.name, \'\'), \' \', COALESCE(leads.patronymic, \'\'))'), 'contact')
			)
		)
	);

$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(
			array(
				'select' => array(
					'leads.*'
				)
			)
		)
		->addCondition(
			array('leftJoin' => array('lead_directory_phones', 'leads.id', '=', 'lead_directory_phones.lead_id'))
		)
		->addCondition(
			array('leftJoin' => array('directory_phones', 'lead_directory_phones.directory_phone_id', '=', 'directory_phones.id'))
		)
		->addCondition(
			array('leftJoin' => array('lead_directory_emails', 'leads.id', '=', 'lead_directory_emails.lead_id'))
		)
		->addCondition(
			array('leftJoin' => array('directory_emails', 'lead_directory_emails.directory_email_id', '=', 'directory_emails.id'))
		)
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('leads.name', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('leads.surname', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('leads.patronymic', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('directory_phones.value', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('directory_emails.value', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()))
		->addCondition(
			array('groupBy' => array('leads.id'))
		);
}

// Список значений для фильтра и поля
$aLead_Needs = Core_Entity::factory('Lead_Need')->getAllBySite_id(CURRENT_SITE);
$sList = "0=…\n";
foreach ($aLead_Needs as $oLead_Need)
{
	$sList .= "{$oLead_Need->id}={$oLead_Need->name}\n";
}

$oAdmin_Form_Dataset
	->changeField('lead_need_id', 'list', trim($sList));

// Список значений для фильтра и поля
$aLead_Maturities = Core_Entity::factory('Lead_Maturity')->getAllBySite_id(CURRENT_SITE);
$sList = "0=…\n";
foreach ($aLead_Maturities as $oLead_Maturity)
{
	$sList .= "{$oLead_Maturity->id}={$oLead_Maturity->name}\n";
}

$oAdmin_Form_Dataset
	->changeField('lead_maturity_id', 'list', trim($sList));

// Список значений для фильтра и поля
$aSources = Core_Entity::factory('Crm_Source')->findAll();
$sList = "0=…\n";
foreach ($aSources as $oSource)
{
	$sList .= "{$oSource->id}={$oSource->name}\n";
}

$oAdmin_Form_Dataset
	->changeField('crm_source_id', 'list', trim($sList));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();