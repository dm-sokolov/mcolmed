<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead_Note_Controller_Add
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Note_Controller_Add extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!is_null(Core_Array::getRequest('text_note')) && strlen(Core_Array::getRequest('text_note')))
		{
			$iLeadId = intval(Core_Array::getGet('lead_id', 0));
			$sLeadNoteText = trim(strval(Core_Array::getRequest('text_note')));

			$oLead_Note = Core_Entity::factory('Lead_Note');
			$oLead_Note->lead_id = $iLeadId;
			$oLead_Note->text = $sLeadNoteText;
			$oLead_Note->datetime = Core_Date::timestamp2sql(time());
			$oLead_Note->save();
		}
	}
}