<?php

/**
* multiload index
* 
* @author KAD Systems (©) 2014	
* @date
*/
 
require_once('../../bootstrap.php');

$oController = new Kad_Module_Controller(2);

$module_name = $oController->get('internal_name');
$module_version = $oController->get('version');
$titles = $oController->get('client_name');

define('MODULE_SITE', 'http://artemkuts.ru/coding/hostcms/' . $module_name . '/');

$module_path = '/' . $module_name . '/';
$step = 1;
$module_link = '/admin/'.$module_name.'/index.php';
$domain = $_SERVER['HTTP_HOST'];
$sAdminFormAction = $module_path;
$fm_action = Core_Array::getGet('action', ''); 

Core_Auth::authorization($module_name);
$oMultiloadController = Multiload_Controller::instance();

$site_id = CURRENT_SITE;
	
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($module_name))
	->setUp()
	->path($module_link)
	->title($titles);
	
$sWindowId = $oAdmin_Form_Controller->getWindowId();

ob_start();

$oAdmin_View = Admin_View::create();

$oAdmin_View
	->module(Core_Module::factory($module_name))
	->pageTitle($titles);

//действие "установлено"
if ( $fm_action == 'install')
{
	Core_Message::show('Модуль "' . $titles . '" версии ' . $module_version . ' успешно установлен!', 'message');
}

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
	->id('fileupload')
	->method('POST')
	->enctype('multipart/form-data')
	->action('/admin'.$module_path.'jquery-upload/')
	->controller($oAdmin_Form_Controller);
	
	
// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');
$oAdmin_Form_Entity_Menus
->add(
	Admin_Form_Entity::factory('Menu')
		->name('Помощь')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name("О модуле..")
				->icon('fa fa-bug')
				->href(
					MODULE_SITE
				)
				->onclick(
					"window.open('".MODULE_SITE."'); return false;"
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_View->addChild($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Tabs = Admin_Form_Entity::factory('Tabs');
$oAdmin_Form_Entity_Tabs->formId(123456789);

// Вкладки
$oTabs = $oAdmin_Form_Entity_Tabs;
$oInfosystemsTab = Admin_Form_Entity::factory('Tab')	
	->name('infosystem')
	->caption("Информационная система")
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->id('type_selector')
			->add (
				Admin_Form_Entity::factory('Radiogroup')
					->id('type_selector')
					->divAttr(array('id' => 'type_selector_div', 'class' => 'form-group col-lg-12'))
					->radio(
						array(
							"Загрузка в основное фото", "Загрузка в дополнительное свойство"
						)
					)
					->buttonset(TRUE)
					->ico(
						array(
							0 => 'fa fa-image',
							1 => 'fa fa-gears',
						)
					)
			)
	)
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add (
				Admin_Form_Entity::factory('Select')
					->name('informationsystem_id')
					->id("infsysselect")
					->caption("Информационная система")
					->options(array(0 => "..") + $oMultiloadController->getInfomationsystems($site_id))
		)
	)
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add (
				Admin_Form_Entity::factory('Select')
					->name('informationsystem_group_id')
					->id("infsysgroupselect")
					->caption("Информационная группа")
			)
	)
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add (
				Admin_Form_Entity::factory('Select')
					->name('informationsystem_item_id')
					->id("infsysitemselect")
					->caption("Информационный элемент")
					->divAttr(array('class' => 'form-group col-lg-12 col-md-12 col-sm-12 col-xs-12', 'style' => 'display: none', 'id' => 'infsysitemselect_div'))
			)
	)
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add (
				Admin_Form_Entity::factory('Select')
					->name('property_id')
					->id("infsyspropselect")
					->caption("Дополнительное свойство")
					->divAttr(array('class' => 'form-group col-lg-12 col-md-12 col-sm-12 col-xs-12', 'style' => 'display: none', 'id' => 'infsyspropselect_div'))
			)
	);
				
$oTabs->add($oInfosystemsTab);

if (Core::moduleIsActive('shop'))
{
	$oShopTab = Admin_Form_Entity::factory('Tab');
	$oShopTab->name('shop')
			->caption("Интернет-магазин")
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->add (
						Admin_Form_Entity::factory('Radiogroup')
						->id('type_shop_selector')
						->divAttr(array('id' => 'type_selector_div', 'class' => 'form-group col-lg-12'))
						->radio(
							array(
								"Загрузка в дополнительное свойство"
							)
						)
						->buttonset(TRUE)
						->ico(
							array(
								0 => 'fa fa-gears',
							)
						)
					)
			)
			->add(	
				Admin_Form_Entity::factory('Div')
					->class('row')
					->add (
						Admin_Form_Entity::factory('Select')
							->name('shop_id')
							->id("shopselect")
							->caption("Интернет-магазин")
							->options(array(0 => "..") + $oMultiloadController->getShops($site_id))
					)
			)
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->add (
						Admin_Form_Entity::factory('Select')
							->name('shop_group_id')
							->id("shopgroupselect")
							->caption("Раздел")
					)
			)
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->add (
						Admin_Form_Entity::factory('Select')
							->name('shopitem_id')
							->id("shopitemselect")
							->caption("Товар")
					)		
			)
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->add (
						Admin_Form_Entity::factory('Select')
							->name('property_id')
							->id("shoppropselect")
							->caption("Дополнительное свойство")
					)
			);
	$oTabs->add($oShopTab);
}

$oAdmin_Form_Entity_Form->add($oTabs);
/// Вкладки

$oAdmin_Form_Entity_Form
	->action($sAdminFormAction)
	->add(Admin_Form_Entity::factory('Code')
		->html("<script>$(function() {
		$('#{$sWindowId} #type_selector_div').buttonset();
		});</script>")
	)
	
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add (
				Admin_Form_Entity::factory('Input')
					->name('loadtype')
					->id("loadtype")
					->value(1)
					->type('hidden')
		)
	)	
	->add(
		Admin_Form_Entity::factory('Div')
			->class('row')
			->add (
				Admin_Form_Entity::factory('Input')
					->name('itemname')
					->id("itemname")
					->value("$1")
					->caption("Названия элементов ($1 заменяется на название картинки, $2 на название без расширения)")
			)	
	)
	->add (
		Admin_Form_Entity::factory('Code')
			->html('
				<!-- Generic page styles -->
				<link rel="stylesheet" href="/admin'.$module_path.'jquery-upload/css/style.css">
				<!-- blueimp Gallery styles -->
				<link rel="stylesheet" href="/admin' . $module_path . 'github/Gallery/css/blueimp-gallery.min.css">
				<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
				<link rel="stylesheet" href="/admin'.$module_path.'jquery-upload/css/jquery.fileupload.css">
				<link rel="stylesheet" href="/admin'.$module_path.'jquery-upload/css/jquery.fileupload-ui.css">
				<!-- CSS adjustments for browsers with JavaScript disabled -->
				<noscript><link rel="stylesheet" href="/admin'.$module_path.'jquery-upload/css/jquery.fileupload-noscript.css"></noscript>
				<noscript><link rel="stylesheet" href="/admin'.$module_path.'jquery-upload/css/jquery.fileupload-ui-noscript.css"></noscript>
				
				<noscript><input type="hidden" name="redirect" value="https://blueimp.github.io/jQuery-File-Upload/"></noscript>
				<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
				<div class="row fileupload-buttonbar">
					<div class="col-lg-7">
						<!-- The fileinput-button span is used to style the file input field as button -->
						<span class="btn btn-success fileinput-button">
							<i class="glyphicon glyphicon-plus"></i>
							<span>Добавить файлы</span>
							<input type="file" name="files[]" multiple>
						</span>
						<button type="submit" id="upload-button" class="btn btn-primary start">
							<i class="glyphicon glyphicon-upload"></i>
							<span>Загрузить </span>
						</button>
						<!--<button type="reset" class="btn btn-warning cancel">
							<i class="glyphicon glyphicon-ban-circle"></i>
							<span>Cancel upload</span>
						</button>-->
						<!--<button type="button" class="btn btn-danger delete">
							<i class="glyphicon glyphicon-trash"></i>
							<span>Delete</span>
						</button>-->
						<input type="checkbox" class="toggle">
						<!-- The global file processing state -->
						<span class="fileupload-process"></span>
					</div>
					<!-- The global progress state -->
					<div class="col-lg-5 fileupload-progress fade">
						<!-- The global progress bar -->
						<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
							<div class="progress-bar progress-bar-success" style="width:0%;"></div>
						</div>
						<!-- The extended global progress state -->
						<div class="progress-extended">&nbsp;</div>
					</div>
				</div>
				<!-- The table listing the files available for upload/download -->
				<table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
				
				
				<!-- The blueimp Gallery widget -->
				<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
					<div class="slides"></div>
					<h3 class="title"></h3>
					<a class="prev">‹</a>
					<a class="next">›</a>
					<a class="close">×</a>
					<a class="play-pause"></a>
					<ol class="indicator"></ol>
				</div>
				<!-- The template to display files available for upload -->
				<script id="template-upload" type="text/x-tmpl">
				{% for (var i=0, file; file=o.files[i]; i++) { %}
					<tr class="template-upload fade">
						<td>
							<span class="preview"></span>
						</td>
						<td>
							<p class="name">{%=file.name%}</p>
							<strong class="error text-danger"></strong>
						</td>
						<td>
							<p class="size">Processing...</p>
							<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
						</td>
						<td>
							{% if (!i && !o.options.autoUpload) { %}
								<button class="btn btn-primary start" style="display:none" disabled>
									<i class="glyphicon glyphicon-upload"></i>
									<span>Start</span>
								</button>
							{% } %}
							{% if (!i) { %}
								<button class="btn btn-danger cancel"><i class="glyphicon glyphicon-remove-sign"></i></button>
							{% } %}
						</td>
					</tr>
				{% } %}
				</script>
				<!-- The template to display files available for download -->
				<script id="template-download" type="text/x-tmpl">
				{% for (var i=0, file; file=o.files[i]; i++) { %}
					<tr class="template-download fade">
						<td>
							<span class="preview">
								{% if (file.thumbnailUrl) { %}
									<!--<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>-->
									<img src="{%=file.thumbnailUrl%}">
								{% } %}
							</span>
						</td>
						<td>
							<!--<p class="name">
								{% if (file.url) { %}
									<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?\'data-gallery\':\'\'%}>{%=file.name%}</a>
								{% } else { %}
									<span>{%=file.name%}</span>
								{% } %}
							</p>-->
							{% if (file.error) { %}
								<div><span class="label label-danger">Error</span> {%=file.error%}</div>
							{% } %}
						</td>
						<td>
							<span class="size">{%=o.formatFileSize(file.size)%}</span>
						</td>
						<td>
							{% if (file.deleteUrl) { %}
								<!--<button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields=\'{"withCredentials":true}\'{% } %}>
									<i class="glyphicon glyphicon-trash"></i>
									<span>Delete</span>
								</button>-->
								<input type="checkbox" name="delete" value="1" class="toggle">
							{% } else { %}
								<button class="btn btn-warning cancel">
									<i class="glyphicon glyphicon-ban-circle"></i>
									<span>Cancel</span>
								</button>
							{% } %}
						</td>
					</tr>
				{% } %}
				</script>
				
				
				
				<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
				<script src="/admin'.$module_path.'jquery-upload/js/vendor/jquery.ui.widget.js"></script>
				<!-- The Templates plugin is included to render the upload/download listings -->
				<script src="/admin' . $module_path . 'github/JavaScript-Templates/js/tmpl.min.js"></script>
				<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
				<script src="/admin' . $module_path . 'github/JavaScript-Load-Image/js/load-image.all.min.js"></script>
				<!-- The Canvas to Blob plugin is included for image resizing functionality -->
				<script src="/admin' . $module_path . 'github/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
				<!-- blueimp Gallery script -->
				<script src="/admin' . $module_path . 'github/Gallery/js/jquery.blueimp-gallery.min.js"></script>
				<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
				<script src="/admin'.$module_path.'jquery-upload/js/jquery.iframe-transport.js"></script>
				<!-- The basic File Upload plugin -->
				<script src="/admin'.$module_path.'jquery-upload/js/jquery.fileupload.js"></script>
				<!-- The File Upload processing plugin -->
				<script src="/admin'.$module_path.'jquery-upload/js/jquery.fileupload-process.js"></script>
				<!-- The File Upload image preview & resize plugin -->
				<script src="/admin'.$module_path.'jquery-upload/js/jquery.fileupload-image.js"></script>
				<!-- The File Upload audio preview plugin -->
				<script src="/admin'.$module_path.'jquery-upload/js/jquery.fileupload-audio.js"></script>
				<!-- The File Upload video preview plugin -->
				<script src="/admin'.$module_path.'jquery-upload/js/jquery.fileupload-video.js"></script>
				<!-- The File Upload validation plugin -->
				<script src="/admin'.$module_path.'jquery-upload/js/jquery.fileupload-validate.js"></script>
				<!-- The File Upload user interface plugin -->
				<script src="/admin'.$module_path.'jquery-upload/js/jquery.fileupload-ui.js"></script>
				<!-- The main application script -->
				<script src="/admin'.$module_path.'jquery-upload/js/main.js"></script>
				
			')
	)
	;
	
$oAdmin_Form_Entity_Form
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
	->title($titles)
	->message('')
	->execute();
	