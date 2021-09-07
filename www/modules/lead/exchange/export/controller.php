<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead.
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Exchange_Export_Controller
{
	/**
	 * Site object
	 * @var Site_Model
	 */
	private $_site = NULL;

	/**
	 * CSV data
	 * @var array
	 */
	private $_aCurrentData;

	protected $_aData = array();

	/**
	 * Constructor.
	 */
	public function __construct(Site_Model $oSite)
	{
		$this->_site = $oSite;

		$iCurrentDataPosition = 0;

		$this->_aCurrentData[$iCurrentDataPosition] = array(
			'"' . Core::_('Lead_Exchange.id') . '"',
			'"' . Core::_('Lead_Exchange.surname') . '"',
			'"' . Core::_('Lead_Exchange.name') . '"',
			'"' . Core::_('Lead_Exchange.patronymic') . '"',
			'"' . Core::_('Lead_Exchange.company') . '"',
			'"' . Core::_('Lead_Exchange.post') . '"',
			'"' . Core::_('Lead_Exchange.amount') . '"',
			'"' . Core::_('Lead_Exchange.birthday') . '"',
			'"' . Core::_('Lead_Exchange.need') . '"',
			'"' . Core::_('Lead_Exchange.maturity') . '"',
			'"' . Core::_('Lead_Exchange.source') . '"',
			'"' . Core::_('Lead_Exchange.shop') . '"',
			'"' . Core::_('Lead_Exchange.status') . '"',
			'"' . Core::_('Lead_Exchange.comment') . '"',
			'"' . Core::_('Lead_Exchange.address') . '"',
			'"' . Core::_('Lead_Exchange.phone') . '"',
			'"' . Core::_('Lead_Exchange.email') . '"',
			'"' . Core::_('Lead_Exchange.website') . '"'
		);
	}

	/**
	 * Prepare string
	 * @param string $string
	 * @return string
	 */
	protected function _prepareString($string)
	{
		return str_replace('"', '""', trim($string));
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$oUser = Core_Auth::getCurrentUser();
		if ($oUser->only_access_my_own)
		{
			return FALSE;
		}

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename = " . 'leads_' . date("Y_m_d_H_i_s") . '.csv' . ";");
		header("Content-Transfer-Encoding: binary");

		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			@set_time_limit(1200);
			ini_set('max_execution_time', '1200');
		}

		foreach ($this->_aCurrentData as $aData)
		{
			$this->_printRow($aData);
		}

		$offset = 0;
		$limit = 100;

		do {
			$oLeads = $this->_site->Leads;
			$oLeads->queryBuilder()
				->clearOrderBy()
				->orderBy('leads.id')
				->offset($offset)
				->limit($limit);

			$aLeads = $oLeads->findAll(FALSE);

			foreach ($aLeads as $oLead)
			{
				$this->_aData = array(
					sprintf('"%s"', $this->_prepareString($oLead->id)),
					sprintf('"%s"', $this->_prepareString($oLead->surname)),
					sprintf('"%s"', $this->_prepareString($oLead->name)),
					sprintf('"%s"', $this->_prepareString($oLead->patronymic)),
					sprintf('"%s"', $this->_prepareString($oLead->company)),
					sprintf('"%s"', $this->_prepareString($oLead->post)),
					sprintf('"%s"', $this->_prepareString($oLead->amount)),
					sprintf('"%s"', $this->_prepareString($oLead->birthday != '0000-00-00' ? Core_Date::sql2date($oLead->birthday) : '')),
					sprintf('"%s"', $this->_prepareString($oLead->Lead_Need->name)),
					sprintf('"%s"', $this->_prepareString($oLead->Lead_Maturity->name)),
					sprintf('"%s"', $this->_prepareString($oLead->Crm_Source->name)),
					sprintf('"%s"', $this->_prepareString($oLead->Shop->name)),
					sprintf('"%s"', $this->_prepareString($oLead->Lead_Status->name)),
					sprintf('"%s"', $this->_prepareString($oLead->comment))
				);

				$aDataAddresses = $aDataPhones = $aDataEmails = $aDataWebsites = array();

				// Directory_Addresses
				$aDirectory_Addresses = $oLead->Directory_Addresses->findAll();
				foreach ($aDirectory_Addresses as $oDirectory_Address)
				{
					$aDataAddresses[] = $oDirectory_Address->value;
				}
				$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode('| ', $aDataAddresses)));

				// Directory_Phones
				$aDirectory_Phones = $oLead->Directory_Phones->findAll();
				foreach ($aDirectory_Phones as $oDirectory_Phone)
				{
					$aDataPhones[] = $oDirectory_Phone->value;
				}
				$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataPhones)));

				// Directory_Emails
				$aDirectory_Emails = $oLead->Directory_Emails->findAll();
				foreach ($aDirectory_Emails as $oDirectory_Email)
				{
					$aDataEmails[] = $oDirectory_Email->value;
				}
				$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataEmails)));

				// Directory_Websites
				$aDirectory_Websites = $oLead->Directory_Websites->findAll();
				foreach ($aDirectory_Websites as $oDirectory_Website)
				{
					$aDataWebsites[] = $oDirectory_Website->value;
				}
				$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataWebsites)));

				$this->_printRow($this->_aData);
			}

			$offset += $limit;
		}
		while (count($aLeads));

		exit();
	}

	/**
	 * Print array
	 * @param array $aData
	 * @return self
	 */
	protected function _printRow($aData)
	{
		echo Core_Str::iconv('UTF-8', 'Windows-1251', implode(';', $aData) . "\n");
		return $this;
	}
}