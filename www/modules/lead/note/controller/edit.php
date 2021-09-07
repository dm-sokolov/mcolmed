<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead_Note_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Note_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('datetime')
			->addSkipColumn('user_id')
			->addSkipColumn('lead');

		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Lead_Note.edit_title')
			: Core::_('Lead_Note.add_title'));

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$iLeadId = intval(Core_Array::getGet('leadId', 0));

		$oMainTab->move($this->getField('text'), $oMainRow1);

		$oMainRow2->add(
			Admin_Form_Entity::factory('Code')
				->html('<input type="hidden" name="lead_id" value="' . $iLeadId .'" />')
			);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Event_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->_object->datetime = Core_Date::timestamp2sql(time());

		$iLeadId = intval(Core_Array::getPost('lead_id'));

		// При добавлении комментария передаем идентификатор автора
		if (is_null($this->_object->id))
		{
			$oCurrentUser = Core_Auth::getCurrentUser();

			$this->_object->user_id = $oCurrentUser->id;

			$this->_object->lead_id = $iLeadId;
		}

		parent::_applyObjectProperty();
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		$iLeadId = intval(Core_Array::getGet('lead_id'));

		$sJsRefresh = '<script>
			// Refresh lead notes list
			if ($("#lead-notes").length)
			{
				$.adminLoad({ path: \'/admin/lead/note/index.php\', additionalParams: \'lead_id=' . $iLeadId . '\', windowId: \'lead-notes\' });
			}
		</script>';

		switch ($operation)
		{
			case 'saveModal':
				$this->addMessage($sJsRefresh);
			break;
			case 'applyModal':
				$this->addContent($sJsRefresh);
			break;
		}

		return parent::execute($operation);
	}
}