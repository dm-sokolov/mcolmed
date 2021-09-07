<?php
/**
 * REPLACE.
 *
 * @package HostCMS 6\Replace
 * @version 6.x
 * @author art studio Morozov&Pimnev
 * @copyright © 2020 ООО Арт-студио "Морозов и Пимнев" (Morozov&Pimnev LLC), http://www.morozovpimnev.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'replace');

$sAdminFormAction = '/admin/replace/index.php';
$sPageTitle = Core::_('Replace.title');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();

// 6.2.2
if(HOSTCMS_UPDATE_NUMBER >= 136)
{
	$oAdmin_Form_Controller->module(Core_Module::factory($sModule));
}

$oAdmin_Form_Controller
	->setUp()
	->path($sAdminFormAction)
	->title($sPageTitle);

$aConfig = Core_Config::instance()->get('replace_config');

$iMode = Core_Array::getPost('mode', 0);
$sText = Core_Array::getPost('text', '');
$sTextReplace = Core_Array::getPost('text_replace', '');
$otherExt = Core_Array::getPost('other_ext', '');
$dir = rtrim(Core_Array::getPost('dir', ''), DIRECTORY_SEPARATOR);
$iCaseSensitive = !is_null(Core_Array::getPost('case_sensitive')) ? 1 : 0;
$iRegExp = !is_null(Core_Array::getPost('regexp')) ? 1 : 0;
$iSorting = Core_Array::getPost('sorting', 0);

ob_start();

// 6.5.0
if(HOSTCMS_UPDATE_NUMBER >= 140)
{
	$oAdmin_View = Admin_View::create();
	$oAdmin_View
		->module(Core_Module::factory($sModule))
		->pageTitle($sPageTitle);
}

$windowId = $oAdmin_Form_Controller->getWindowId();

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

// 6.5.0
if(HOSTCMS_UPDATE_NUMBER >= 140)
{
	// Добавляем все хлебные крошки контроллеру
	$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);
}
else
{
	// Добавляем все меню контроллеру
	$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
}

$aGetExt = array();

if (strlen($sText) > 0)
{
	try
	{
		$path = rtrim(CMS_FOLDER, DIRECTORY_SEPARATOR);
		$dirPath = $path . $dir;
		//$text = stripslashes($sText);
		//$text_replace = stripslashes($sTextReplace);
		$text = $sText;
		$text_replace = $sTextReplace;
		$replace = FALSE;

		$iMode == 1 && $replace = $text_replace;

		foreach($aConfig['file_type'] as $ext => $value)
		{
			!is_null(Core_Array::getPost("ext_".$ext)) && $aGetExt[] = $ext;
		}

		strlen(trim($otherExt)) > 0 && $aGetExt = array_merge($aGetExt, explode(',', trim($otherExt)));

		$start_time = microtime(true);

		$rExecute = Replace_Controller::instance()->execute($sText, $iCaseSensitive, $iRegExp, $dirPath, $aGetExt, $replace);

		if(count($rExecute))
		{
			$oTable = Core::factory('Core_Html_Entity_Table');

			// 6.2.4
			if(HOSTCMS_UPDATE_NUMBER >= 138)
			{
				$oTable->class('admin-table table table-bordered table-hover table-striped sql-table');
			}
			else
			{
				$oTable->class('admin_table sql_explain');
			}

			$oDiv = Core::factory('Core_Html_Entity_Div');
			$oDiv->add($oTable);

			$objects = array();

			foreach ($rExecute as $key => $value)
			{
				$ext = Core_File::getExtension($value);
				$stat = stat($value);

				$ico = isset(Core::$mainConfig['fileIcons'][$ext])
					? '<img src="/admin/images/icons/' . Core::$mainConfig['fileIcons'][$ext] . '" />'
					: '<i class="fa fa-file-text-o"></i>';

				$obj = new stdClass();
				$obj->id = $key + 1;
				$obj->ico = $ico;
				$obj->name = str_replace($path, '', $value);
				$obj->datetime = Core_Date::timestamp2datetime($stat[9]);

				$objects[] = $obj;
			}

			if($iSorting)
			{
				uasort($objects, array('Replace_Controller', $iSorting == 1 ? '_sortDesc' : '_sortAsc'));
			}

			foreach ($objects as $object)
			{
				$oTr = Core::factory('Core_Html_Entity_Tr');
				$oTr->add(
					Core::factory('Core_Html_Entity_Td')
						->width("50px")
						->value($object->id)
				)->add(
					Core::factory('Core_Html_Entity_Td')
						->width("40px")
						->class("text-center")
						->value($object->ico)
				)->add(
					Core::factory('Core_Html_Entity_Td')
						->style('text-align:left;')
						->value($object->name)
				)->add(
					Core::factory('Core_Html_Entity_Td')
						->style('text-align:left;')
						->value($object->datetime)
				);
				$oTable->add($oTr);
			}

			$oDiv->execute();
		}

		$exec_time = microtime(true) - $start_time;

		Core::factory('Core_Html_Entity_P')
			->value($replace
				? Core::_('Replace.runtime_replace', $exec_time, count($rExecute))
				: Core::_('Replace.runtime_search', $exec_time, count($rExecute))
			)
			->execute();

		//Core_Message::show(Core::_('Replace.error_message'), 'error');
	}
	catch (Exception $e)
	{
		Core_Message::show($e->getMessage(), 'error');
	}
}

// 6.5.0
if(HOSTCMS_UPDATE_NUMBER >= 140)
{
	$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

	$oMainTab
		->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
		->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
		->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
		->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
		->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
		->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
	;

	$oModeRadiogroup = Admin_Form_Entity::factory('Radiogroup')
		->radio(
			array(Core::_('Replace.search'), Core::_('Replace.replace'))
		)
		->divAttr(array('id' => 'type_selector_div', 'class' => 'form-group col-xs-12'))
		->id('type_selector')
		->name('mode')
		->value($iMode)
		->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1])")
		->buttonset(TRUE)
		->ico(
			array(
				0 => 'fa fa-search',
				1 => 'fa fa-refresh',
			)
		);

	$oMainRow1->add($oModeRadiogroup);

	// 6.7.2
	if(HOSTCMS_UPDATE_NUMBER < 162)
	{
		$oMainRow1->add(
			Admin_Form_Entity::factory('Code')
			->html("<script>function radiogroupOnChange(windowId, value, values)
			{
			var values = values || [0, 1];
			for (var x in values) {
			if (value != values[x])
			{
			$('#'+windowId+' .hidden-'+values[x]).show();
			$('#'+windowId+' .shown-'+values[x]).hide();
			}
			}
			$('#'+windowId+' .hidden-'+value).hide();
			$('#'+windowId+' .shown-'+value).show();
			}</script>")
		);
	}
	
	$oMainRow2->add(
		Admin_Form_Entity::factory('Select')
			->value($iSorting)
			->caption(Core::_('Replace.sorting'))
			->name('sorting')
			->options(
				array(
					0 => Core::_('Replace.sorting0'),
					1 => Core::_('Replace.sorting1'),
					2 => Core::_('Replace.sorting2')
				)
			)
			->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
	)->add(
		Admin_Form_Entity::factory('Checkbox')
			->value($iCaseSensitive)
			->caption(Core::_('Replace.case_sensitive'))
			->name('case_sensitive')
			->divAttr(array('class' => 'form-group col-xs-12 col-md-3 margin-top-21'))
	)->add(
		Admin_Form_Entity::factory('Checkbox')
			->value($iRegExp)
			->caption(Core::_('Replace.regexp'))
			->name('regexp')
			->divAttr(array('class' => 'form-group col-xs-12 col-md-3 margin-top-21'))
	);

	$oMainRow2->add(
		Admin_Form_Entity::factory('Textarea')
			->name('text')
			->caption(Core::_('Replace.text'))
			->rows(4)
			->divAttr(array('class' => 'form-group col-xs-12'))
			->value(
				(mb_strlen($sText) < 10240)
					? $sText
					: NULL
			)
	);

	$oMainRow3->add(
		Admin_Form_Entity::factory('Textarea')
			->name('text_replace')
			->id("text_replace")
			->caption(Core::_('Replace.text_replace'))
			->rows(4)
			->divAttr(array('class' => 'form-group col-xs-12 hidden-0'))
			->value(
			(mb_strlen($sTextReplace) < 10240)
				? $sTextReplace
				: NULL
			)
	);

	$oMainRow4->add(
			Admin_Form_Entity::factory('Div')->class('form-group col-xs-12')
				->value(Core::_('Replace.search_files'))
	);

	foreach($aConfig['file_type'] as $ext => $value)
	{
		$oMainRow4->add(
			Admin_Form_Entity::factory('Checkbox')
				->value(count($aGetExt) ? (in_array($ext, $aGetExt) ? 1 : 0) : $value)
				->caption($ext)
				->name("ext_".$ext)
				->divAttr(array('class' => 'form-group col-xs-1'))
		);
	}

	$oMainRow5->add(
		Admin_Form_Entity::factory('Input')
			->name('other_ext')
			->caption(Core::_('Replace.other_file_type'))
			->value($otherExt)
			->divAttr(array('class' => 'form-group col-xs-12'))
	);

	$oMainRow6->add(
		Admin_Form_Entity::factory('Input')
			->name('dir')
			->caption(Core::_('Replace.dir'))
			->value($dir)
			->divAttr(array('class' => 'form-group col-xs-12'))
	)->add(Admin_Form_Entity::factory('Code')
		->html("<script>radiogroupOnChange('{$windowId}', '{$iMode}', [0,1])</script>")
	);

	Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->action($sAdminFormAction)
		->add($oMainTab)
		->add(Admin_Form_Entity::factory('Button')
			->name('button')
			->type('submit')
			->value(Core::_('Replace.apply'))
			->class('applyButton btn btn-darkorange')
			->onclick("if($(\"#{$windowId} input[name=mode]:checked\").val() == 1) {
					res = confirm('" . Core::_('Replace.warningButton') . "'); if (res){ " . $oAdmin_Form_Controller->getAdminSendForm('exec') . "}
					return false;
				}else{".$oAdmin_Form_Controller->getAdminSendForm('exec')."}")
		)
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
}
else
{
	Admin_Form_Entity::factory('Title')
		->name($sPageTitle)
		->execute();

	$oAdmin_Form_Controller->showChildren();

	$oAdmin_Form_Entity_Form = new Admin_Form_Entity_Form($oAdmin_Form_Controller);

	$oAdmin_Form_Entity_Form
		->action($sAdminFormAction)
		->add(
			Admin_Form_Entity::factory('Radiogroup')
				->radio(
					array(Core::_('Replace.search'), Core::_('Replace.replace'))
				)
				->id('type_selector')
				->name('mode')
				->value($iMode)
				->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1])")
				->divAttr(array('id' => 'type_selector_div'))
		)
		->add(Admin_Form_Entity::factory('Code')
			->html("<script>$(function() {
			$('#{$windowId} #type_selector_div').buttonset();
			});
			function radiogroupOnChange(windowId, value, values)
			{
			var values = values || [0, 1];
			for (var x in values) {
			if (value != values[x])
			{
			$('#'+windowId+' .hidden-'+values[x]).show();
			$('#'+windowId+' .shown-'+values[x]).hide();
			}
			}
			$('#'+windowId+' .hidden-'+value).hide();
			$('#'+windowId+' .shown-'+value).show();
			}</script>")
		)
		->add(
			Admin_Form_Entity::factory('Checkbox')
				->value($iCaseSensitive)
				->caption(Core::_('Replace.case_sensitive'))
				->name('case_sensitive')
		)
		->add(
			Admin_Form_Entity::factory('Textarea')
				->name('text')
				->caption(Core::_('Replace.text'))
				->rows(4)
				->value(
					(mb_strlen($sText) < 10240)
						? $sText
						: NULL
				)
		)
		->add(
			Admin_Form_Entity::factory('Textarea')
				->name('text_replace')
				->id("text_replace")
				->caption(Core::_('Replace.text_replace'))
				->rows(4)
				->divAttr(array('class' => 'hidden-0'))
				->value(
				(mb_strlen($sTextReplace) < 10240)
					? $sTextReplace
					: NULL
				)
		)
		->add(
			Admin_Form_Entity::factory('Div')->value(Core::_('Replace.search_files'))
		)
	;

	foreach($aConfig['file_type'] as $ext => $value)
	{
		$oAdmin_Form_Entity_Form->add(
			Admin_Form_Entity::factory('Checkbox')
				->value(count($aGetExt) ? (in_array($ext, $aGetExt) ? 1 : 0) : $value)
				->caption($ext)
				->name("ext_".$ext)
				->divAttr(array('style' => 'width: 8.33333333%;float: left;'))
		);
	}

	$oAdmin_Form_Entity_Form
		->add(
			Admin_Form_Entity::factory('Div')->class('clear')
		)
		->add(
			Admin_Form_Entity::factory('Input')
				->name('other_ext')
				->caption(Core::_('Replace.other_file_type'))
				->value($otherExt)
		)
		->add(
			Admin_Form_Entity::factory('Input')
				->name('dir')
				->caption(Core::_('Replace.dir'))
				->value($dir)
		)
		->add(
			Admin_Form_Entity::factory('Code')
				->html("<script>radiogroupOnChange('{$windowId}', '{$iMode}', [0,1])</script>")
		)
		->add(
			Admin_Form_Entity::factory('Button')
				->name('button')
				->type('submit')
				->value(Core::_('Replace.apply'))
				->class('applyButton')
				->onclick("if($(\"#{$windowId} input[name=mode]:checked\").val() == 1) {
					res = confirm('" . Core::_('Replace.warningButton') . "'); if (res){ " . $oAdmin_Form_Controller->getAdminSendForm('exec') . "}
					return false;
				}else{".$oAdmin_Form_Controller->getAdminSendForm('exec')."}")
		)
		->execute();

	$oAdmin_Answer = Core_Skin::instance()->answer();

	$oAdmin_Answer
		->ajax(Core_Array::getRequest('_', FALSE))
		->content(ob_get_clean())
		->message('')
		->title($sPageTitle)
		->execute();
}