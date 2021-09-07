<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Добавляет вкладку для загрузки изображений для карточки товара и инфоэлемента.
 *
 * @author KAD Systems (©) 2017
 * @date 04-04-2017
 */
class Multiload_Observers_ItemTab
{
	/**
	 * Событие для карточки товара.
	 *
	 * @param  Admin_Form_Action_Controller_Type_Edit  $oController
	 * @param  array  $aArgs
	 * @return void
	 */
	/*static public function onBeforeExecute(Admin_Form_Action_Controller_Type_Edit $oController, array $aArgs)
	{
		list($operation, $oAdminFormController) = $aArgs;

		if (!is_null($operation) || get_class($oController) != 'Shop_Item_Controller_Edit')
		{
			return;
		}

		self::_attachTab($oController, $aArgs);
	}*/

	/**
	 * Событие для карточки товара.
	 *
	 * @param  Admin_Form_Action_Controller_Type_Edit  $oController
	 * @param  array  $aArgs
	 * @return void
	 */
	static public function onAfterRedeclaredPrepareFormShop(Admin_Form_Action_Controller_Type_Edit $oController, array $aArgs)
	{
		if (get_class($oController) == 'Shop_Item_Controller_Edit')
		{
			self::_attachTab($oController, $aArgs);
		}
	}
	
	/**
	 * Событие для карточки инфоэлемента.
	 *
	 * @param  Admin_Form_Action_Controller_Type_Edit  $oController
	 * @param  array  $aArgs
	 * @return void
	 */
	static public function onAfterRedeclaredPrepareFormIs(Admin_Form_Action_Controller_Type_Edit $oController, array $aArgs)
	{
		if (get_class($oController) == 'Informationsystem_Item_Controller_Edit')
		{
			self::_attachTab($oController, $aArgs);
		}
	}

	/**
	 * Добавляет вкладку модуля мультизагрузки.
	 *
	 * @param  Admin_Form_Action_Controller_Type_Edit  $oController
	 * @param  array  $aArgs
	 * @return void
	 */
	static protected function _attachTab(Admin_Form_Action_Controller_Type_Edit $oController, array $aArgs)
	{
		$oAdminFormController = $aArgs[1];
		$oObject = $oController->getObject();

	 	$oKadModuleController = new Kad_Module_Controller(2);
		$oMultiloadController = Multiload_Controller::instance();

 		switch (get_class($oObject))
 		{
 			case 'Shop_Item_Model':
 				$loadtype = 2;
 				$sItemId = 'shopitem_id';
 				//$sGroupId = 'shop_group_id';
 				$sParentEntityId = 'shop_id';
 				$aProperties = $oMultiloadController->getShopItemProperties($oObject->shop_id, ($oObject->modification_id ? $oObject->Modification->shop_group_id : $oObject->shop_group_id));
 			break;

 			case 'Informationsystem_Item_Model':
 				$loadtype = 1;
 				$sItemId = 'informationsystemitem_id';
 				//$sGroupId = 'informationsystem_group_id';
 				$sParentEntityId = 'informationsystem_id';
 				$aProperties = $oMultiloadController->getInfomationsystemItemProperties($oObject->informationsystem_id, $oObject->informationsystem_group_id);
 			break;

 			default:
 				return;
 		}

		// Добавляем вкладку мультизагрузки

		$multitabCaption = 'Мультизагрузка';

		$oMultiuploadTab = Admin_Form_Entity::factory('Tab')
			->caption($multitabCaption)
			->name('Multiupload');

		if ($oController->issetTab('additional'))
		{
			$oController->addTabBefore($oMultiuploadTab, $oController->getTab('additional'));
		}
		else
		{
			$oController->addTab($oMultiuploadTab);
		}

		// Далее идет модифицированный код из модуля мультизагрузки, а именно из файла /admin/multiload/index.php,
		// код изменен для работы в карточке товара.

		$modulePath = '/' . $oKadModuleController->get('internal_name') . '/';
		$windowId = $oAdminFormController->getWindowId();

		// Добавляем элементы на вкладку
		$oMultiuploadTab
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->add(
						Admin_Form_Entity::factory('Input')
							->name($sParentEntityId)
							->divAttr(array('style' => 'display: none;'))
							->type('hidden')
							->value($oObject->{$sParentEntityId})
					)
					/*->add(
						Admin_Form_Entity::factory('Input')
							->name($sGroupId)
							->divAttr(array('style' => 'display: none;'))
							->type('hidden')
							->value($oObject->{$sGroupId})
					)*/
					->add(
						Admin_Form_Entity::factory('Input')
							->name($sItemId)
							->divAttr(array('style' => 'display: none;'))
							->type('hidden')
							->value($oObject->id)
					)
					->add(
						$oPropertySelect = Admin_Form_Entity::factory('Select')
							->name('property_id')
							->id('shoppropselect')
							->caption('Дополнительное свойство')
							->options(
								array(0 => '..') + $aProperties
							)
					)
			);

		// Вместо <form /> используем <div />, такое решение из-за того, что карточка товара
		// целиком со всеми вкладками и так уже обернута в <form /> и при загрузке изображений
		// отправляются все данные этой формы
		$oMultiuploadForm = Admin_Form_Entity::factory('Div')
			->id('fileupload')
			->controller($oAdminFormController)
		;

		$oMultiuploadTab
			->add(
				$oMultiuploadForm
					->add(Admin_Form_Entity::factory('Code')
						->html("<script>$(function() {
							$('#{$windowId} #type_selector_div').buttonset();

							/*$('select[name = \'shop_group_id\']').on('change', function(){
								$('input[name = \'shop_group_id\']').val($(this).val());
							})*/
						});</script>")
					)
					->add(
						Admin_Form_Entity::factory('Div')
							->class('row')
							->add (
								Admin_Form_Entity::factory('Input')
									->name('loadtype')
									->id("loadtype")
									->value($loadtype)
									->type('hidden')
						)
					)
					// Прячем кнопки сохранения товара на вкладке мультизагрузки
					->add(
						Admin_Form_Entity::factory('Code')
							->html('
								<script>
									$(\'#tab\')
										.on(\'show.bs.tab\', function(event) {
											if ($(event.target).text() == \'' . $multitabCaption . '\') {
												$(\'#ControlElements\').hide();

												// console.log(\'hide\');
												// console.log($(event.target).text());
											} else {
												$(\'#ControlElements\').show();

												// console.log(\'show\');
												// console.log($(event.target).text());
											}
										});
								</script>
							')
					)
					// Код из модуля
					->add(
						Admin_Form_Entity::factory('Code')
							->html('
								<!-- Generic page styles -->
								<link rel="stylesheet" href="/admin' . $modulePath . 'jquery-upload/css/style.css">
								<!-- blueimp Gallery styles -->
								<link rel="stylesheet" href="/admin' . $modulePath . 'github/Gallery/css/blueimp-gallery.min.css">
								<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
								<link rel="stylesheet" href="/admin' . $modulePath . 'jquery-upload/css/jquery.fileupload.css">
								<link rel="stylesheet" href="/admin' . $modulePath . 'jquery-upload/css/jquery.fileupload-ui.css">
								<!-- CSS adjustments for browsers with JavaScript disabled -->
								<noscript><link rel="stylesheet" href="/admin' . $modulePath . 'jquery-upload/css/jquery.fileupload-noscript.css"></noscript>
								<noscript><link rel="stylesheet" href="/admin' . $modulePath . 'jquery-upload/css/jquery.fileupload-ui-noscript.css"></noscript>

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
								<script src="/admin' . $modulePath . 'jquery-upload/js/vendor/jquery.ui.widget.js"></script>
								<!-- The Templates plugin is included to render the upload/download listings -->
								<script src="/admin' . $modulePath . 'github/JavaScript-Templates/js/tmpl.min.js"></script>
								<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
								<script src="/admin' . $modulePath . 'github/JavaScript-Load-Image/js/load-image.all.min.js"></script>
								<!-- The Canvas to Blob plugin is included for image resizing functionality -->
								<script src="/admin' . $modulePath . 'github/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
								<!-- blueimp Gallery script -->
								<script src="/admin' . $modulePath . 'github/Gallery/js/jquery.blueimp-gallery.min.js"></script>
								<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/jquery.iframe-transport.js"></script>
								<!-- The basic File Upload plugin -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/jquery.fileupload.js"></script>
								<!-- The File Upload processing plugin -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/jquery.fileupload-process.js"></script>
								<!-- The File Upload image preview & resize plugin -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/jquery.fileupload-image.js"></script>
								<!-- The File Upload audio preview plugin -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/jquery.fileupload-audio.js"></script>
								<!-- The File Upload video preview plugin -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/jquery.fileupload-video.js"></script>
								<!-- The File Upload validation plugin -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/jquery.fileupload-validate.js"></script>
								<!-- The File Upload user interface plugin -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/jquery.fileupload-ui.js"></script>
								<!-- The main application script -->
								<script src="/admin' . $modulePath . 'jquery-upload/js/main.js"></script>
							')
					)
			);
	}
}