<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Backup
 *
 * @package HostCMS
 * @subpackage Backup
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Backup_Controller_Upload extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title',
		'cloud_id'
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$oUser = Core_Auth::getCurrentUser();
		if ($oUser->only_access_my_own)
		{
			return FALSE;
		}

		if (is_null($operation))
		{
			$newWindowId = 'Backup_' . time();

			$oCore_Html_Entity_Form = Core::factory('Core_Html_Entity_Form');

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			$filename = $this->_object->hash;
			$sourcename = $this->_object->name;

			$aCloudList = array();

			$oSite = Core_Entity::factory('Site', CURRENT_SITE);
			$aClouds = $oSite->Clouds->getAllByActive(1, FALSE);

			foreach ($aClouds as $oCloud)
			{
				$aCloudList[$oCloud->id] = $oCloud->name;
			}

			$oCore_Html_Entity_Form
				->add(
					Core::factory('Admin_Form_Entity_Code')
					->html('
						<h6 style="display:none;">' . Core::_('Backup.file_upload', $sourcename) . '</h6>
						<div class="progress progress-striped active" style="display:none;">
							<div class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0">
								<span></span>
							</div>
						</div>
					')
				)
				->add(
					Admin_Form_Entity::factory('span')
						->id('cloud_span')
						->value(Core::_('Backup.chooseCloud'))
				)
				->add(
					Admin_Form_Entity::factory('select')
						->id('cloud_select')
						->options($aCloudList)
						->name('test')
						->divAttr(array('class' => 'form-group margin-top-5'))
				)
				->add(
					Admin_Form_Entity::factory('Button')
						->id('cloud_button')
						->name('apply')
						->type('submit')
						->class('applyButton btn btn-blue')
						->value(Core::_('Backup.uploadButton'))
						->onclick('
							$("#cloud_span",' . $newWindowId . ').hide();
							$("#cloud_select",' . $newWindowId . ').hide();
							$("#cloud_button",' . $newWindowId . ').hide();
							$(".progress",' . $newWindowId . ').show();
							$("h6",' . $newWindowId . ').show();
							uploadFilePart($("#cloud_select").val());
							return false;
						')
						->controller($this->_Admin_Form_Controller)
				)
				->add(Core::factory('Admin_Form_Entity_Code')->html('
					<script>
					var percent = 0;
					var stop = false;
					function uploadFilePart(cloud_id)
					{
						$.ajax({
							type: "POST",
							url: "' . $this->_Admin_Form_Controller->getPath() . '",
							data: "cloud_id=" + cloud_id + "&upload=true&file=' . $filename . '&sourcename=' . $sourcename . '",
							success: function(data){
								var parentDiv = $(".progress-bar", "#' . $newWindowId . '"),
									targetSpan = $("span", parentDiv),
									width = parentDiv.width();

								if (typeof data.error != \'undefined\')
								{
									stop = true;

									$("div#id_message").html(data.error);
								}

								if (data == 0 || stop)
								{
									// $("#' . $newWindowId . '").remove();
									bootbox.hideAll();
								}
								else
								{
									percent = data|0;
									parentDiv.width(percent + \'%\');
									targetSpan.html(percent + \'%\');
									uploadFilePart(cloud_id);
								}
							},
							dataType: "json"
						});
					}
					</script>'))
			;

			Core::factory('Core_Html_Entity_Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form)
				->execute();

			ob_start();

			$windowId = $this->_Admin_Form_Controller->getWindowId();

			Core::factory('Core_Html_Entity_Script')
				->value("$(function() {
				$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . $this->title . "', AppendTo: '#{$windowId}', width: 750, height: 140, addContentPadding: true, modal: false, Maximize: false, Minimize: false, beforeclose: function(){stop = true;} }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}

		return $this;
	}
}