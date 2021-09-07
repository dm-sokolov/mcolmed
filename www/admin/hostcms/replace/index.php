<?php
/**
 * Hostcms Replace
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'hostcms_replace');

$sAdminFormAction = '/admin/hostcms/replace/index.php';
$sPageTitle = Core::_('Hostcms_Replace.title');

$oSite = Core_Entity::factory('Site', CURRENT_SITE);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sPageTitle);

$aTypicalConfig = array(
	'informationsystem_group' => 1,
	'informationsystem_item' => 1,
	'shop_group' => 1,
	'shop_item' => 1,
	'document' => 1,
	'structure' => 1
);

$aConfig = Core_Config::instance()->get('hostcms_replace_config');

if ($oAdmin_Form_Controller->getAction() == 'exec')
{
	foreach ($aTypicalConfig as $sCheckboxName => $iValue)
	{
		$aConfig[$oSite->id][$sCheckboxName] = intval(Core_Array::getPost($sCheckboxName));
	}
	Core_Config::instance()->set('hostcms_replace_config', $aConfig);
}

$sText = Core_Array::getPost('text', '');
$sReplace = Core_Array::getPost('text_replace', '');
$iMode = Core_Array::getPost('mode', 0);

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle($sPageTitle);

$formSettings = Core_Array::getPost('hostcms', array())
	+ array(
		'action' => NULL,
		'window' => 'id_content',
	);

$windowId = Core_Str::escapeJavascriptVariable($formSettings['window']);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sPageTitle)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

$oMainTab
	->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
	// ->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
	;

$oMainRow1->add(
	Admin_Form_Entity::factory('Div')
		->class('form-group col-xs-12')
		->value(Core::_('Hostcms_Replace.search_in'))
);

!isset($aConfig[$oSite->id]) && $aConfig[$oSite->id] = array();
$aConfig[$oSite->id] = $aConfig[$oSite->id] + $aTypicalConfig;

foreach ($aConfig[$oSite->id] as $entity => $value)
{
	$oMainRow1->add(
		Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Hostcms_Replace.' . $entity))
			->value(1)
			->checked($value)
			->name($entity)
			->divAttr(array('class' => 'form-group col-xs-12 col-md-6'))
	);
}
$oMainRow2->add(
	Admin_Form_Entity::factory('Radiogroup')
		->id('type_selector')
		->name('mode')
		->value($iMode)
		->onclick("radiogroupOnChange('{$windowId}', $(this).val(), [0,1])")
		->divAttr(array(
				'id' => 'type_selector',
				'class' => 'form-group col-xs-12'
			)
		)
		->radio(
			array(Core::_('Hostcms_Replace.search'), Core::_('Hostcms_Replace.replace'))
		)
		->buttonset(TRUE)
		->ico(
			array(
				0 => 'fa fa-search',
				1 => 'fa fa-refresh',
			)
		))
	->add(
		Admin_Form_Entity::factory('Textarea')
			->name('text')
			->caption(Core::_('Hostcms_Replace.text'))
			->rows(4)
			->divAttr(array('class' => 'form-group col-xs-12'))
			->value($sText)
	);

$oMainRow3->add(
	Admin_Form_Entity::factory('Textarea')
		->name('text_replace')
		->id('text_replace')
		->caption(Core::_('Hostcms_Replace.text_replace'))
		->rows(4)
		->divAttr(array(
				'class' => 'form-group col-xs-12 hidden-0'
			)
		)
		->value($sReplace)
);

$oMainTab->add(
	Admin_Form_Entity::factory('Code')
		->html("<script>radiogroupOnChange('{$windowId}', '{$iMode}', [0,1])</script>")
);

if (strlen($sText) > 0)
{
	try
	{
		$oHostcms_Replace_Controller = new Hostcms_Replace_Controller($oSite);

		$start_time = Core::getmicrotime();

		$oHostcms_Replace_Controller->text($sText);

		$iMode == 1 && $oHostcms_Replace_Controller
			->mode(1)
			->replace($sReplace);

		$aReturn = $oHostcms_Replace_Controller->execute();

		if (count($aReturn))
		{
			$oTable = Core::factory('Core_Html_Entity_Table')
				->class('admin-table table table-bordered table-hover table-striped sql-table')
				// Top title
				->add($oTitleTr = Core::factory('Core_Html_Entity_Tr'));

			$oTitleTr->add(
				Core::factory('Core_Html_Entity_Th')
					->value(Core::_('Hostcms_Replace.id'))
			)->add(
				Core::factory('Core_Html_Entity_Th')
					->value(Core::_('Hostcms_Replace.name'))
			)->add(
				Core::factory('Core_Html_Entity_Th')
					->value(Core::_('Hostcms_Replace.type'))
			)->add(
				Core::factory('Core_Html_Entity_Th')
					->value('')
			);

			foreach ($aReturn as $aData)
			{
				if (count($aData))
				{
					$oTr = Core::factory('Core_Html_Entity_Tr');

					foreach ($aData as $key => $value)
					{
						$oTr->add(
							Core::factory('Core_Html_Entity_Td')
								->style($key == 'link'
									? 'text-align: center;'
									: 'text-align: left;'
								)
								->value($key == 'link'
									? $value
									: htmlspecialchars(Core_Str::cut($value, 100))
								)
						);
					}

					$oTable->add($oTr);
				}
			}

			$oMainRow4->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add($oTable)
			);

			$exec_time = Core::getmicrotime() - $start_time;

			$oMainRow4->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add(
					Core::factory('Core_Html_Entity_P')
						->value(Core::_('Hostcms_Replace.rows_count', count($aReturn), round($exec_time, 3)))
				)
			);
		}
	}
	catch (Exception $e)
	{
		Core_Message::show($e->getMessage(), 'error');
	}
}

$oButtonDiv = Admin_Form_Entity::factory('Div')
	->class('form-group col-xs-12 dataTables_actions')
	->add(
		Admin_Form_Entity::factory('Button')
		->name('apply')
		->type('submit')
		->value(Core::_('Hostcms_Replace.send'))
		->class('applyButton btn btn-blue')
		->onclick("if($(\"#{$windowId} input[name=mode]:checked\").val() == 1) {
				res = confirm('" . Core::_('Hostcms_Replace.warningButton') . "'); if (res){ " . $oAdmin_Form_Controller->getAdminSendForm('exec') . "}
				return false;
			}else{" . $oAdmin_Form_Controller->getAdminSendForm('exec') . "}"
		)
	);

$oMainTab->add(
	Admin_Form_Entity::factory('Div')
		->class('DTTTFooter sticky-actions')
		->add(
			Admin_Form_Entity::factory('Div')->class('row')->add($oButtonDiv)
		)
);

Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($sAdminFormAction)
	->add($oMainTab)
	->execute();

$content = ob_get_clean();

ob_start();

$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title($sPageTitle)
	->module($sModule)
	->execute();