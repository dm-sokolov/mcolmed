<?php
/**
 * Site users.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

// Код формы
$iAdmin_Form_Id = 30;
$sAdminFormAction = '/admin/siteuser/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

if (Core_Auth::logged())
{
	Core_Auth::checkBackendBlockedIp();

	// Контроллер формы
	$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

	if (!is_null(Core_Array::getGet('loadPersonAvatar')) || !is_null(Core_Array::getGet('loadCompanyAvatar')))
	{
		Core_Session::close();

		if (Core_Array::getGet('loadPersonAvatar'))
		{
			$id = intval(Core_Array::getGet('loadPersonAvatar'));

			$oSiteuser_Person = Core_Entity::factory('Siteuser_Person')->getById($id);
			if ($oSiteuser_Person)
			{
				$name = $oSiteuser_Person->name . ' ' . $oSiteuser_Person->surname;
			}
			else
			{
				Core_Message::show('Wrong ID', 'error');
			}
		}
		else
		{
			$id = intval(Core_Array::getGet('loadCompanyAvatar'));

			$oSiteuser_Company = Core_Entity::factory('Siteuser_Company')->getById($id);
			if ($oSiteuser_Company)
			{
				$name = $oSiteuser_Company->name;
			}
			else
			{
				Core_Message::show('Wrong ID', 'error');
			}
		}

		// Get initials
		$initials = Core_Str::getInitials($name);

		$bgColor = Core_Str::createColor($id);

		Core_Image::avatar($initials, $bgColor, $width = 130, $height = 130);
	}

	if (!is_null(Core_Array::getGet('loadSiteusers')) && !is_null(Core_Array::getGet('term')))
	{
		Core_Auth::setCurrentSite();

		$aJSON = array();

		$aTypes = Core_Array::getGet('types', array('siteuser'));

		$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('term'))));

		if (strlen($sQuery))
		{
			if (in_array('siteuser', $aTypes))
			{
				$oSiteusers = Core_Entity::factory('Site', CURRENT_SITE)->Siteusers;
				$oSiteusers->queryBuilder()
					->open()
						->where('siteusers.login', 'LIKE', '%' . $sQuery . '%')
						->setOr()
						->where('siteusers.id', '=', $sQuery)
						->setOr()
						->where('siteusers.email', 'LIKE', '%' . $sQuery . '%')
					->close()
					->limit(Core::$mainConfig['autocompleteItems']);

				$aSiteusers = $oSiteusers->findAll(FALSE);

				foreach ($aSiteusers as $oSiteuser)
				{
					$aJSON[] = prepareSiteuserJSON($oSiteuser);
				}
			}

			if (in_array('person', $aTypes))
			{
				$oSiteuser_People = Core_Entity::factory('Siteuser_Person');
				$oSiteuser_People->queryBuilder()
					->join('siteusers', 'siteuser_people.siteuser_id', '=', 'siteusers.id')
					->open()
						->where('siteuser_people.name', 'LIKE', '%' . $sQuery . '%')
						->setOr()
						->where('siteuser_people.surname', 'LIKE', '%' . $sQuery . '%')
						->setOr()
						->where('siteuser_people.patronymic', 'LIKE', '%' . $sQuery . '%')
						->setOr()
						->where('siteusers.login', 'LIKE', '%' . $sQuery . '%')
					->close()
					->where('siteusers.site_id', '=', CURRENT_SITE)
					->where('siteusers.deleted', '=', 0)
					->limit(Core::$mainConfig['autocompleteItems']);

				$aSiteuser_People = $oSiteuser_People->findAll(FALSE);

				foreach ($aSiteuser_People as $oSiteuser_Person)
				{
					$aJSON[] = prepareSiteuserJSON($oSiteuser_Person);
				}
			}

			if (in_array('company', $aTypes))
			{
				$oSiteuser_Companies = Core_Entity::factory('Siteuser_Company');
				$oSiteuser_Companies->queryBuilder()
					->join('siteusers', 'siteuser_companies.siteuser_id', '=', 'siteusers.id')
					->open()
						->where('siteuser_companies.name', 'LIKE', '%' . $sQuery . '%')
						->setOr()
						->where('siteusers.login', 'LIKE', '%' . $sQuery . '%')
					->close()
					->where('siteusers.site_id', '=', CURRENT_SITE)
					->where('siteusers.deleted', '=', 0)
					->limit(Core::$mainConfig['autocompleteItems']);

				$aSiteuser_Companies = $oSiteuser_Companies->findAll(FALSE);

				foreach ($aSiteuser_Companies as $oSiteuser_Company)
				{
					$aJSON[] = prepareSiteuserJSON($oSiteuser_Company);
				}
			}
		}

		Core::showJson($aJSON);
	}
}

Core_Auth::authorization($sModule = 'siteuser');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Siteuser.siteusers'))
	->pageTitle(Core::_('Siteuser.siteusers'));

function prepareSiteuserJSON($object)
{
	switch (get_class($object))
	{
		case 'Siteuser_Model':
		default:
			$aReturn = array(
				'id' => $object->id,
				'text' => $object->login . ' [' . $object->id . ']',
				'login' => $object->login,
				'companies' => array(),
				'people' => array(),
				'type' =>  'siteuser',
			);

			// Добавить всех представителей и компании клиента
			$aSiteuser_Companies = $object->Siteuser_Companies->findAll(FALSE);
			foreach ($aSiteuser_Companies as $oSiteuser_Company)
			{
				$aReturn['companies'][] = prepareSiteuserJSONCompany($oSiteuser_Company);
			}

			$aSiteuser_People = $object->Siteuser_People->findAll(FALSE);
			foreach ($aSiteuser_People as $oSiteuser_Person)
			{
				$aReturn['people'][] = prepareSiteuserJSONPerson($oSiteuser_Person);
			}
		break;
		case 'Siteuser_Company_Model':
			$aReturn = prepareSiteuserJSONCompany($object);
		break;
		case 'Siteuser_Person_Model':
			$aReturn = prepareSiteuserJSONPerson($object);
		break;
	}

	return $aReturn;
}


function prepareSiteuserJSONCompany($object)
{
	$avatar = $object->getAvatar();

	$aDirectory_Phones = $object->Directory_Phones->findAll(FALSE);
	$phone = isset($aDirectory_Phones[0])
		? $aDirectory_Phones[0]->value
		: '';

	$aDirectory_Emails = $object->Directory_Emails->findAll(FALSE);
	$email = isset($aDirectory_Emails[0])
		? $aDirectory_Emails[0]->value
		: '';

	$aAddresses = array();

	$aDirectory_Addresses = $object->Directory_Addresses->findAll(FALSE);
	foreach ($aDirectory_Addresses as $oDirectory_Address)
	{
		$shop_country_id = $shop_country_location_city_id = $shop_country_location_id = 0;

		if (strlen(trim($oDirectory_Address->city)))
		{
			$oShop_Country_Location_City = Core_Entity::factory('Shop_Country_Location_City')->getByName($oDirectory_Address->city);

			if (!is_null($oShop_Country_Location_City))
			{
				$shop_country_location_city_id = $oShop_Country_Location_City->id;
				$shop_country_location_id = $oShop_Country_Location_City->shop_country_location_id;
				$shop_country_id = $oShop_Country_Location_City->Shop_Country_Location->Shop_Country->id;
			}
		}
		elseif (strlen(trim($oDirectory_Address->country)))
		{
			$oShop_Country = Core_Entity::factory('Shop_Country')->getByName($oDirectory_Address->country);

			if (!is_null($oShop_Country))
			{
				$shop_country_id = $oShop_Country->id;
			}
		}

		$aAddresses[] = array(
			'country' => intval($shop_country_id),
			'location' => intval($shop_country_location_id),
			'city' => intval($shop_country_location_city_id),
			'postcode' => $oDirectory_Address->postcode,
			'address' => $oDirectory_Address->value
		);
	}

	$aReturn = array(
		'id' => 'company_' . $object->id,
		'text' => $object->name . ' [' . $object->Siteuser->login . '] ' . '%%%' . $avatar,
		'name' =>  $object->name,
		'avatar' =>  $avatar,
		'phone' =>  $phone,
		'email' =>  $email,
		'tin' => $object->tin,
		'addresses' => $aAddresses,
		'login' => $object->Siteuser->login,
		'siteuser_id' => $object->siteuser_id,
		'type' =>  'company',
	);

	return $aReturn;
}

function prepareSiteuserJSONPerson($object)
{
	$avatar = $object->getAvatar();
	$fullName = $object->getFullName();

	$aDirectory_Phones = $object->Directory_Phones->findAll(FALSE);
	$phone = isset($aDirectory_Phones[0])
		? $aDirectory_Phones[0]->value
		: '';

	$aDirectory_Emails = $object->Directory_Emails->findAll(FALSE);
	$email = isset($aDirectory_Emails[0])
		? $aDirectory_Emails[0]->value
		: '';

	$shop_country_id = $shop_country_location_city_id = $shop_country_location_id = 0;

	if (strlen(trim($object->city)))
	{
		$oShop_Country_Location_City = Core_Entity::factory('Shop_Country_Location_City')->getByName($object->city);

		if (!is_null($oShop_Country_Location_City))
		{
			$shop_country_location_city_id = $oShop_Country_Location_City->id;
			$shop_country_location_id = $oShop_Country_Location_City->shop_country_location_id;
			$shop_country_id = $oShop_Country_Location_City->Shop_Country_Location->Shop_Country->id;
		}
	}
	elseif (strlen(trim($object->country)))
	{
		$oShop_Country = Core_Entity::factory('Shop_Country')->getByName($object->country);

		if (!is_null($oShop_Country))
		{
			$shop_country_id = $oShop_Country->id;
		}
	}

	$aReturn = array(
		'id' => 'person_' . $object->id,
		'text' => $fullName . ' [' . $object->Siteuser->login . '] ' . '%%%' . $avatar,
		'name' =>  $object->name,
		'surname' =>  $object->surname,
		'patronymic' =>  $object->patronymic,
		'avatar' =>  $avatar,
		'phone' =>  $phone,
		'email' =>  $email,
		'country' => intval($shop_country_id),
		'location' => intval($shop_country_location_id),
		'city' => intval($shop_country_location_city_id),
		'postcode' => $object->postcode,
		'address' => $object->address,
		'login' => $object->Siteuser->login,
		'siteuser_id' => $object->siteuser_id,
		'type' =>  'person',
	);

	return $aReturn;
}

/*if (!is_null(Core_Array::getGet('loadSiteuserCard')) && !is_null(Core_Array::getGet('phone')))
{
	$aJSON = array();

	$phone = strval(Core_Array::getGet('phone'));

	$oSiteuser_Companies = Core_Entity::factory('Siteuser_Company');
	$oSiteuser_Companies->queryBuilder()
		->join('siteuser_company_directory_phones', 'siteuser_companies.id', '=', 'siteuser_company_directory_phones.siteuser_company_id')
		->join('directory_phones', 'siteuser_company_directory_phones.directory_phone_id', '=', 'directory_phones.id')
		->where('directory_phones.value', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($phone) . '%')
		->groupBy('siteuser_companies.id');

	$aSiteuser_Companies = $oSiteuser_Companies->findAll();
	foreach ($aSiteuser_Companies as $oSiteuser_Company)
	{
		$aJSON[] = array(
			'id' => $oSiteuser_Company->id,
			'siteuser_id' => $oSiteuser_Company->siteuser_id,
			'type' => 'company',
			'name' => $oSiteuser_Company->name,
			'avatar' => $oSiteuser_Company->getAvatar(),
		);
	}

	$oSiteuser_People = Core_Entity::factory('Siteuser_Person');
	$oSiteuser_People->queryBuilder()
		->join('siteuser_people_directory_phones', 'siteuser_people.id', '=', 'siteuser_people_directory_phones.siteuser_person_id')
		->join('directory_phones', 'siteuser_people_directory_phones.directory_phone_id', '=', 'directory_phones.id')
		->where('directory_phones.value', 'LIKE', '%' . Core_DataBase::instance()->escapeLike($phone) . '%')
		->groupBy('siteuser_people.id');

	$aSiteuser_People = $oSiteuser_People->findAll();
	foreach ($aSiteuser_People as $oSiteuser_Person)
	{
		$aJSON[] = array(
			'id' => $oSiteuser_Person->id,
			'siteuser_id' => $oSiteuser_Person->siteuser_id,
			'type' => 'person',
			'name' => $oSiteuser_Person->getFullName(),
			'avatar' => $oSiteuser_Person->getAvatar(),
		);
	}

echo "<pre>";
var_dump($aJSON);
echo "</pre>";

	Core::showJson($aJSON);
}*/

// Загрузка данных о вновь созданном клиенте в select2
if (!is_null(Core_Array::getGet('loadSiteuserSelect2')))
{
	$iSiteuser_id = intval(Core_Array::getGet('loadSiteuserSelect2'));
	$oSiteuser = Core_Entity::factory('Siteuser')->find($iSiteuser_id);

	$aJSON = !is_null($oSiteuser->id)
		? prepareSiteuserJSON($oSiteuser)
		: array('error' => 'Siteuser not found');

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('loadSelect2Avatars')))
{
	$aJSON = array(
		'result' => 'error',
		'html' => ''
	);

	$iSiteuser_id = intval(Core_Array::getPost('siteuser_id'));
	$oSiteuser = Core_Entity::factory('Siteuser')->find($iSiteuser_id);

	if (!is_null($oSiteuser))
	{
		$aJSON = array(
			'result' => 'success',
			'html' => Siteuser_Controller_Edit::addSiteuserRepresentativeAvatars($oSiteuser)
		);
	}

	Core::showJson($aJSON);
}

// Загрузка данных о вновь созданной компании в select2
if (!is_null(Core_Array::getGet('loadSiteuserCompanySelect2')))
{
	$iSiteuser_company_id = intval(Core_Array::getGet('loadSiteuserCompanySelect2'));
	$oSiteuser_Company = Core_Entity::factory('Siteuser_Company')->find($iSiteuser_company_id);

	$aJSON = !is_null($oSiteuser_Company->id)
		? prepareSiteuserJSON($oSiteuser_Company)
		: array('error' => 'Siteuser Company not found');

	Core::showJson($aJSON);
}

// Загрузка данных о вновь созданном представителе в select2
if (!is_null(Core_Array::getGet('loadSiteuserPersonSelect2')))
{
	$iSiteuser_person_id = intval(Core_Array::getGet('loadSiteuserPersonSelect2'));
	$oSiteuser_Person = Core_Entity::factory('Siteuser_Person')->find($iSiteuser_person_id);

	$aJSON = !is_null($oSiteuser_Person->id)
		? prepareSiteuserJSON($oSiteuser_Person)
		: array('error' => 'Siteuser Person not found');

	Core::showJson($aJSON);
}

$sSiteusers = '/admin/siteuser/siteuser/index.php';
$sSiteuserProperties = '/admin/siteuser/property/index.php';
$sSiteuserTypes = '/admin/siteuser/type/index.php';
$sSiteuserStatuses = '/admin/siteuser/status/index.php';
$sSiteuserSources = '/admin/crm/source/index.php';

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Siteuser.siteuser'))
		->icon('fa fa-user')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Admin_Form.add'))
				->img('/admin/images/user_add.gif')
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Admin_Form.tabProperties'))
				->icon('fa fa-cogs')
				->img('/admin/images/user_property.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sSiteuserProperties, NULL, NULL, '')
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sSiteuserProperties, NULL, NULL, '')
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Siteuser.add_list'))
				->icon('fa fa-list')
				->img('/admin/images/user_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'addSiteusersList', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'addSiteusersList', NULL, 0, 0)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Siteuser.export_siteusers'))
				->icon('fa fa-upload')
				->img('/admin/images/export.gif')
				->target('_blank')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportSiteusersList', NULL, 0, 0)
				)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Siteuser_Group.title'))
		->icon('fa fa-folder-o')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sSiteusers, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sSiteusers, NULL, NULL, '')
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Siteuser.su_menu_directories'))
		->icon('fa fa-book')
		->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Siteuser_Type.siteuser_types_title'))
					->icon('fa fa-bars')
					->href(
						$oAdmin_Form_Controller->getAdminLoadHref($sSiteuserTypes)
					)
					->onclick(
						$oAdmin_Form_Controller->getAdminLoadAjax($sSiteuserTypes)
					)
			)
		->add(
				Admin_Form_Entity::factory('Menu')
					->name(Core::_('Siteuser_Status.siteuser_statuses_title'))
					->icon('fa fa-flag-o')
					->href(
						$oAdmin_Form_Controller->getAdminLoadHref($sSiteuserStatuses)
					)
					->onclick(
						$oAdmin_Form_Controller->getAdminLoadAjax($sSiteuserStatuses)
					)
			)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Crm_Source.siteuser_sources_title'))
				->icon('fa fa-user-plus')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sSiteuserSources)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sSiteuserSources)
				)
		)
)
;

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

// Построение хлебных крошек
$oAdminFormEntityBreadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая хлебная крошка будет всегда
$oAdminFormEntityBreadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Siteuser.siteusers'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath())
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())
		)
	);

// Хлебные крошки добавляем контроллеру
$oAdmin_Form_Controller->addEntity($oAdminFormEntityBreadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oSiteuser_Group_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Siteuser_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки доступны только форме редактирования
	$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Siteuser.siteusers'))
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
			)
	);

	$oSiteuser_Group_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSiteuser_Group_Controller_Edit);
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

// Действие редактирования
$oAdminFormActionEdit = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('addSiteusersList');

if ($oAdminFormActionEdit && $oAdmin_Form_Controller->getAction() == 'addSiteusersList')
{
	$oSiteuserListEdit = Admin_Form_Action_Controller::factory(
		'Siteuser_List_Controller_Edit', $oAdminFormActionEdit
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSiteuserListEdit);

	// Крошки при редактировании
	$oSiteuserListEdit->addEntity($oAdminFormEntityBreadcrumbs);
}

// Действие экспорта
$oAdminFormActionExport = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('exportSiteusersList');

if ($oAdminFormActionExport && $oAdmin_Form_Controller->getAction() == 'exportSiteusersList')
{
	$oSite = Core_Entity::factory('Site', CURRENT_SITE);
	$Siteuser_List_Export_Controller = new Siteuser_List_Export_Controller($oSite);
	$Siteuser_List_Export_Controller->execute();
}

// Действие "Удаление значения свойства"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deletePropertyValue');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deletePropertyValue')
{
	$oDeletePropertyValueController = Admin_Form_Action_Controller::factory(
		'Property_Controller_Delete_Value', $oAction
	);

	$oDeletePropertyValueController
		->linkedObject(array(
				Core_Entity::factory('Siteuser_Property_List', CURRENT_SITE)
			));

	$oAdmin_Form_Controller->addAction($oDeletePropertyValueController);
}

$oAdminFormActionMerge = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('merge');

if ($oAdminFormActionMerge && $oAdmin_Form_Controller->getAction() == 'merge')
{
	$oAdmin_Form_Action_Controller_Type_Merge = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Merge', $oAdminFormActionMerge
	);

	$oAdmin_Form_Controller->addAction($oAdmin_Form_Action_Controller_Type_Merge);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Siteuser')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

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
					'siteusers.*'
				)
			)
		)
		->addCondition(
			array('leftJoin' => array('siteuser_companies', 'siteusers.id', '=', 'siteuser_companies.siteuser_id', array(
					array('AND' => array('siteuser_companies.deleted', '=', 0))
				))
			)
		)
		->addCondition(
			array('leftJoin' => array('siteuser_people', 'siteusers.id', '=', 'siteuser_people.siteuser_id',
				array(
					array('AND' => array('siteuser_people.deleted', '=', 0))
				))
			)
		)
		->addCondition(
			array('leftJoin' => array('siteuser_company_directory_phones', 'siteuser_companies.id', '=', 'siteuser_company_directory_phones.siteuser_company_id'))
		)
		->addCondition(
			array('leftJoin' => array('siteuser_people_directory_phones', 'siteuser_people.id', '=', 'siteuser_people_directory_phones.siteuser_person_id'))
		)
		->addCondition(
			array('leftJoin' => array('directory_phones', 'siteuser_company_directory_phones.directory_phone_id', '=', 'directory_phones.id', array(array('OR' => array('siteuser_people_directory_phones.directory_phone_id', '=', 'directory_phones.id')))))
		)
		->addCondition(
			array('leftJoin' => array('siteuser_company_directory_emails', 'siteuser_companies.id', '=', 'siteuser_company_directory_emails.siteuser_company_id'))
		)
		->addCondition(
			array('leftJoin' => array('siteuser_people_directory_emails', 'siteuser_people.id', '=', 'siteuser_people_directory_emails.siteuser_person_id'))
		)
		->addCondition(
			array('leftJoin' => array('directory_emails', 'siteuser_company_directory_emails.directory_email_id', '=', 'directory_emails.id', array(array('OR' => array('siteuser_people_directory_emails.directory_email_id', '=', 'directory_emails.id')))))
		)
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('siteusers.login', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('siteusers.email', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('siteuser_companies.name', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('siteuser_people.name', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('siteuser_people.surname', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('siteuser_people.patronymic', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('directory_phones.value', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('directory_emails.value', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()))
		->addCondition(
			array('groupBy' => array('siteusers.id'))
		);
}

if (isset($oAdmin_Form_Controller->request['admin_form_filter_1277'])
	&& $oAdmin_Form_Controller->request['admin_form_filter_1277'] != ''
|| isset($oAdmin_Form_Controller->request['topFilter_1277'])
	&& $oAdmin_Form_Controller->request['topFilter_1277'] != '')
{
	$oAdmin_Form_Dataset->addCondition(
		array(
			'select' => array(
				'siteusers.*', array(Core_QueryBuilder::expression('CONCAT_WS(" ", GROUP_CONCAT(`siteuser_companies`.`name`), GROUP_CONCAT(CONCAT_WS(" ", `siteuser_people`.`surname`, `siteuser_people`.`name`, `siteuser_people`.`patronymic`)))'), 'counterparty'),
			)
		)
	)
	->addCondition(
		array('leftJoin' => array('siteuser_companies', 'siteusers.id', '=', 'siteuser_companies.siteuser_id', array(
				array('AND' => array('siteuser_companies.deleted', '=', 0))
			))
		)
	)
	->addCondition(
		array('leftJoin' => array('siteuser_people', 'siteusers.id', '=', 'siteuser_people.siteuser_id',
			array(
				array('AND' => array('siteuser_people.deleted', '=', 0))
			))
		)
	)
	->addCondition(
		array('groupBy' => array('siteusers.id'))
	)
	;
}


// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();