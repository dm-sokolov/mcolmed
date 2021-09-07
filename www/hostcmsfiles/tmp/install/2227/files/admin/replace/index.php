<?php
/**
 * REPLACE.
 *
 * @package HostCMS 6\Replace
 * @version 6.x
 * @author art studio Morozov&Pimnev
 * @copyright © 2016 ООО Арт-студио "Морозов и Пимнев" (Morozov&Pimnev LLC), http://www.morozovpimnev.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'replace');

$sAdminFormAction = '/admin/replace/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('replace.title'));

$sWindowId = $oAdmin_Form_Controller->getWindowId();

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('replace.title'));

$rText = array();

$aFilesTypes = array(
	'swf'=>1,'gif'=>1,'jpg'=>1,'jpeg'=>1,'png'=>1,'zip'=>1,'rar'=>1,'pdf'=>1,'css'=>1,'sql'=>1,
	'csv'=>1,'txt'=>1,'dat'=>1,'xml'=>1,'htm'=>1,'html'=>0,'js'=>0,'php'=>0,'xsl'=>0
);

try
{
	$sText = Core_Array::getPost('text');
	$sTextReplace = Core_Array::getPost('text_replace');
	$otherExt = Core_Array::getPost("other_ext");
	$dir = rtrim(Core_Array::getPost('dir'), DIRECTORY_SEPARATOR);
	$aGetExt = array();

	if (!is_null($sText))
	{
		if (strlen($sText) > 0)
		{
			$path = rtrim(CMS_FOLDER, DIRECTORY_SEPARATOR);
			$dirPath = $path . $dir;
			$text = stripslashes($sText);
			$text_replace = stripslashes($sTextReplace);
			$replace = FALSE;

			$text_replace && intval(Core_Array::getPost("field_id_0")) && $replace = $text_replace;

			foreach($aFilesTypes as $key => $value)
			{
				if(!is_null(Core_Array::getPost("ext_".$key))) $aGetExt[] = $key;
			}

			if(strlen(trim($otherExt)) > 0) $aGetExt = array_merge($aGetExt, explode(',', $otherExt));

			$start_time = microtime(true);

			$rExecute = Replace_Controller::instance()->execute($sText, $dirPath, $aGetExt, $replace);

			if(count($rExecute))
			{
				$oTable = Core::factory('Core_Html_Entity_Table')
					->class('admin-table table table-bordered table-hover table-striped sql-table');

				$oDiv = Core::factory('Core_Html_Entity_Div');
				$oDiv->add($oTable);

				foreach ($rExecute as $key => $value)
				{
					$oTr = Core::factory('Core_Html_Entity_Tr');
					$oTr->add(
						Core::factory('Core_Html_Entity_Td')->value($key + 1)
					)->add(
						Core::factory('Core_Html_Entity_Td')
							->style('text-align:left;')
							->value(str_replace($path, '', $value))
					);
					$oTable->add($oTr);
				}

				$oDiv->execute();
			}

			$exec_time = microtime(true) - $start_time;

			Core::factory('Core_Html_Entity_P')
					->value($replace
						? Core::_('replace.runtime_replace', $exec_time, count($rExecute))
						: Core::_('replace.runtime_search', $exec_time, count($rExecute))
					)
					->execute();
		}
		else
		{
			Core_Message::show(Core::_('replace.error_message'), 'error');
		}
	}
}
catch (Exception $e)
{

	$sText = NULL;
	$otherExt = NULL;
	$dir = NULL;
	Core_Message::show($e->getMessage(), 'error');
}

//Core_Message::show(Core::_('replace.warning'));

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

$oMainTab
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->id('type_selector')
			->add (
				Admin_Form_Entity::factory('Radiogroup')
					->id('type_selector')
					->onclick("showReplaceBlock('{$sWindowId}', $(this).val())")
					->divAttr(array('id' => 'type_selector_div', 'class' => 'form-group col-lg-12'))
					->radio(
						array(Core::_('replace.search'), Core::_('replace.replace'))
					)
					->buttonset(TRUE)
					->ico(
						array(
							0 => 'fa fa-search',
							1 => 'fa fa-refresh',
						)
					)
			)
	)
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Textarea')
			->name('text')
			->caption(Core::_('replace.text'))
			->rows(4)
			->divAttr(array('class' => 'form-group col-lg-12 col-md-12 col-sm-12 col-xs-12'))
			->value(
			(mb_strlen($sText) < 10240)
				? $sText
				: NULL
			)
	))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Textarea')
			->name('text_replace')
			->id("text_replace")
			->caption(Core::_('replace.text_replace'))
			->rows(4)
			->divAttr(array('class' => 'form-group col-lg-12 col-md-12 col-sm-12 col-xs-12','style' => 'display: none', 'id' => 'text_replace_div'))
			->value(
			(mb_strlen($sTextReplace) < 10240)
				? $sTextReplace
				: NULL
			)
	))
	->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Div')->class('form-group col-lg-12 col-md-12 col-sm-12 col-xs-12')
			->value(Core::_('replace.do_not_search_files'))
	))
;

foreach($aFilesTypes as $key => $value)
{
	$oMainRow1->add(
		Admin_Form_Entity::factory('Checkbox')
			->value(count($aGetExt) > 0 && in_array($key, $aGetExt) ? TRUE : $value)
			->caption($key)
			->name("ext_".$key)
			->divAttr(array('class' => 'form-group col-lg-1 col-md-1 col-sm-1'))
	);
}

$oMainTab
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Input')
			->name('other_ext')
			->caption(Core::_('replace.other_file_type'))
			->value($otherExt)
			->divAttr(array('class' => 'form-group col-lg-12 col-md-12 col-sm-12 col-xs-12')))
	)
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Input')
			->name('dir')
			->caption(Core::_('replace.dir'))
			->value($dir)
			->divAttr(array('class' => 'form-group col-lg-12 col-md-12 col-sm-12 col-xs-12')))
			
	)->add(Admin_Form_Entity::factory('Code')
		->html("<script>$(function() {
			$('#{$sWindowId} #type_selector_div').buttonset();
		});

		function showReplaceBlock(windowId, index)
		{
			var windowId = $.getWindowId(windowId);

			parseInt(index) ? $('#'+windowId+' #text_replace_div').show() : $('#'+windowId+' #text_replace_div').hide();
		}
		</script>")
	)
;

Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($sAdminFormAction)
	->add($oMainTab)
	->add(Admin_Form_Entity::factory('Button')
		->name('button')
		->type('submit')
		->value(Core::_('replace.apply'))
		->class('applyButton btn btn-darkorange')
		->onclick("if($(\"#{$sWindowId} input[name=field_id_0]:checked\").val() == 1) {
				res =confirm('" . Core::_('replace.warningButton') . "'); if (res){ " . $oAdmin_Form_Controller->getAdminSendForm('exec') . "}
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
	->title(Core::_('replace.title'))
	->module($sModule)
	->execute();