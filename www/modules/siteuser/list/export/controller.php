<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Siteuser.
 *
 * @package HostCMS
 * @subpackage Siteuser
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Siteuser_List_Export_Controller
{
	/**
	 * Site object
	 * @var Site_Model
	 */
	private $_site = NULL;

	/**
	 * Additional properties of siteusers
	 * Дополнительные свойства пользователей
	 * @var array
	 */
	private $_aSiteuser_Properties = array();

	/**
	 * Siteuser properties count
	 * Требуется хранить количество свойств отдельно, т.к. количество полей файла CSV для свойств не равно количеству свойств (из-за файлов)
	 * @var int
	 */
	private $_iSiteuser_Properties_Count;

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

		$this->_iSiteuser_Properties_Count = 0;

		// Заполняем дополнительные свойства пользователей
		$this->_aSiteuser_Properties = Core_Entity::factory('Siteuser_Property_List', $this->_site->id)->Properties->findAll(FALSE);

		$iCurrentDataPosition = 0;

		$this->_aCurrentData[$iCurrentDataPosition] = array(
			// 3
			'"' . Core::_('Siteuser_List_Export.id') . '"',
			'"' . Core::_('Siteuser_List_Export.login') . '"',
			'"' . Core::_('Siteuser_List_Export.email') . '"',

			// 16. Siteuser_People
			'"' . Core::_('Siteuser_List_Export.name') . '"',
			'"' . Core::_('Siteuser_List_Export.surname') . '"',
			'"' . Core::_('Siteuser_List_Export.patronymic') . '"',
			'"' . Core::_('Siteuser_List_Export.post') . '"',
			'"' . Core::_('Siteuser_List_Export.birthday') . '"',
			'"' . Core::_('Siteuser_List_Export.sex') . '"',
			'"' . Core::_('Siteuser_List_Export.photo') . '"',
			'"' . Core::_('Siteuser_List_Export.country') . '"',
			'"' . Core::_('Siteuser_List_Export.postcode') . '"',
			'"' . Core::_('Siteuser_List_Export.city') . '"',
			'"' . Core::_('Siteuser_List_Export.address') . '"',
			'"' . Core::_('Siteuser_List_Export.phones') . '"',
			'"' . Core::_('Siteuser_List_Export.emails') . '"',
			'"' . Core::_('Siteuser_List_Export.socials') . '"',
			'"' . Core::_('Siteuser_List_Export.messengers') . '"',
			'"' . Core::_('Siteuser_List_Export.websites') . '"',

			// 14. Siteuser_Company
			'"' . Core::_('Siteuser_List_Export.company_name') . '"',
			'"' . Core::_('Siteuser_List_Export.description') . '"',
			'"' . Core::_('Siteuser_List_Export.logo') . '"',
			'"' . Core::_('Siteuser_List_Export.headcount') . '"',
			'"' . Core::_('Siteuser_List_Export.annual_turnover') . '"',
			'"' . Core::_('Siteuser_List_Export.business_area') . '"',
			'"' . Core::_('Siteuser_List_Export.tin') . '"',
			'"' . Core::_('Siteuser_List_Export.bank_account') . '"',
			'"' . Core::_('Siteuser_List_Export.addresses') . '"',
			'"' . Core::_('Siteuser_List_Export.phones') . '"',
			'"' . Core::_('Siteuser_List_Export.emails') . '"',
			'"' . Core::_('Siteuser_List_Export.socials') . '"',
			'"' . Core::_('Siteuser_List_Export.messengers') . '"',
			'"' . Core::_('Siteuser_List_Export.websites') . '"', // 33
		);

		// Добавляем в заголовок информацию о свойствах
		foreach ($this->_aSiteuser_Properties as $oProperty)
		{
			$this->_aCurrentData[$iCurrentDataPosition][] = sprintf('"%s"', $this->_prepareString($oProperty->name));
			$this->_iSiteuser_Properties_Count++;

			if ($oProperty->type == 2)
			{
				$this->_aCurrentData[$iCurrentDataPosition][] = 'Small ' . $this->_prepareString($oProperty->name);
				$this->_iSiteuser_Properties_Count++;
			}
		}
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

	protected function _person($oSiteuser_Person)
	{
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->name));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->surname));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->patronymic));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->post));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->birthday != '0000-00-00' ? Core_Date::sql2date($oSiteuser_Person->birthday) : ''));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->sex == 0 ? Core::_('Admin_Form.male') : Core::_('Admin_Form.female')));
		$this->_aData[] = $oSiteuser_Person->image == '' ? '' : sprintf('"%s"', $oSiteuser_Person->getImageFileHref());
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->country));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->postcode));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->city));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Person->address));

		$aDataPhones = $aDataEmails = $aDataSocials = $aDataMessengers = $aDataWebsites = array();

		// Directory_Phones
		$aDirectory_Phones = $oSiteuser_Person->Directory_Phones->findAll();
		foreach ($aDirectory_Phones as $oDirectory_Phone)
		{
			$aDataPhones[] = $oDirectory_Phone->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataPhones)));

		// Directory_Emails
		$aDirectory_Emails = $oSiteuser_Person->Directory_Emails->findAll();
		foreach ($aDirectory_Emails as $oDirectory_Email)
		{
			$aDataEmails[] = $oDirectory_Email->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataEmails)));

		// Directory_Socials
		$aDirectory_Socials = $oSiteuser_Person->Directory_Socials->findAll();
		foreach ($aDirectory_Socials as $oDirectory_Social)
		{
			$aDataSocials[] = $oDirectory_Social->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataSocials)));

		// Directory_Messengers
		$aDirectory_Messengers = $oSiteuser_Person->Directory_Messengers->findAll();
		foreach ($aDirectory_Messengers as $oDirectory_Messenger)
		{
			$aDataMessengers[] = $oDirectory_Messenger->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataMessengers)));

		// Directory_Websites
		$aDirectory_Websites = $oSiteuser_Person->Directory_Websites->findAll();
		foreach ($aDirectory_Websites as $oDirectory_Website)
		{
			$aDataWebsites[] = $oDirectory_Website->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataWebsites)));
	}

	protected function _company($oSiteuser_Company)
	{
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Company->name));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Company->description));
		$this->_aData[] = $oSiteuser_Company->image == '' ? '' : sprintf('"%s"', $oSiteuser_Company->getImageFileHref());
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Company->headcount));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Company->annual_turnover));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Company->business_area));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Company->tin));
		$this->_aData[] = sprintf('"%s"', $this->_prepareString($oSiteuser_Company->bank_account));

		$aDataAddresses = $aDataPhones = $aDataEmails = $aDataSocials = $aDataMessengers = $aDataWebsites = array();

		// Directory_Addresses
		$aDirectory_Addresses = $oSiteuser_Company->Directory_Addresses->findAll();
		foreach ($aDirectory_Addresses as $oDirectory_Address)
		{
			$aDataAddresses[] = $oDirectory_Address->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataAddresses)));

		// Directory_Phones
		$aDirectory_Phones = $oSiteuser_Company->Directory_Phones->findAll();
		foreach ($aDirectory_Phones as $oDirectory_Phone)
		{
			$aDataPhones[] = $oDirectory_Phone->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataPhones)));

		// Directory_Emails
		$aDirectory_Emails = $oSiteuser_Company->Directory_Emails->findAll();
		foreach ($aDirectory_Emails as $oDirectory_Email)
		{
			$aDataEmails[] = $oDirectory_Email->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataEmails)));

		// Directory_Socials
		$aDirectory_Socials = $oSiteuser_Company->Directory_Socials->findAll();
		foreach ($aDirectory_Socials as $oDirectory_Social)
		{
			$aDataSocials[] = $oDirectory_Social->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataSocials)));

		// Directory_Messengers
		$aDirectory_Messengers = $oSiteuser_Company->Directory_Messengers->findAll();
		foreach ($aDirectory_Messengers as $oDirectory_Messenger)
		{
			$aDataMessengers[] = $oDirectory_Messenger->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataMessengers)));

		// Directory_Websites
		$aDirectory_Websites = $oSiteuser_Company->Directory_Websites->findAll();
		foreach ($aDirectory_Websites as $oDirectory_Website)
		{
			$aDataWebsites[] = $oDirectory_Website->value;
		}
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(implode(', ', $aDataWebsites)));
	}

	protected function _property($oSiteuser, $oProperty, $oProperty_Value)
	{
		$this->_aData[] = sprintf('"%s"', $this->_prepareString(!is_null($oProperty_Value)
			? ($oProperty->type != 2
				? ($oProperty->type == 3 && $oProperty_Value->value != 0 && Core::moduleIsActive('list')
					? $oProperty_Value->List_Item->value
					: ($oProperty->type == 8
						? Core_Date::sql2date($oProperty_Value->value)
						: ($oProperty->type == 9
							? Core_Date::sql2datetime($oProperty_Value->value)
							: ($oProperty->type == 5
								? Core_Entity::factory('Informationsystem_Item', $oProperty_Value->value)->name
								: ($oProperty->type == 12
									? Core_Entity::factory('Shop_Item', $oProperty_Value->value)->name
									: $oProperty_Value->value)))))
							: ($oProperty_Value->file == '' ? '' : $oProperty_Value->setHref($oSiteuser->getDirHref())->getLargeFileHref())
				)
			: ''));

		if ($oProperty->type == 2)
		{
			$this->_aData[] = !is_null($oProperty_Value)
				? ($oProperty_Value->file_small == '' ? '' : sprintf('"%s"', $oProperty_Value->getSmallFileHref()))
				: '';
		}
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
		header("Content-Disposition: attachment; filename = " . 'siteusers_' . date("Y_m_d_H_i_s") . '.csv' . ";");
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
			$oSiteusers = $this->_site->Siteusers;
			$oSiteusers->queryBuilder()
				->clearOrderBy()
				->orderBy('siteusers.id')
				->offset($offset)
				->limit($limit);

			$aSiteusers = $oSiteusers->findAll(FALSE);

			foreach ($aSiteusers as $oSiteuser)
			{
				$this->_aData = array(
					sprintf('"%s"', $this->_prepareString($oSiteuser->id)),
					sprintf('"%s"', $this->_prepareString($oSiteuser->login)),
					sprintf('"%s"', $this->_prepareString($oSiteuser->email)),
				);

				// People
				$aSiteuser_People = $oSiteuser->Siteuser_People->findAll(FALSE);
				count($aSiteuser_People)
					? $this->_person(array_shift($aSiteuser_People))
					: $this->_aData = array_pad($this->_aData, 19, '""'); // 3 + 16

				// Company
				$aSiteuser_Companies = $oSiteuser->Siteuser_Companies->findAll(FALSE);
				count($aSiteuser_Companies)
					? $this->_company(array_shift($aSiteuser_Companies))
					: $this->_aData = array_pad($this->_aData, 33, '""'); // 3 + 16 + 14

				// Properties
				$aTotal_Property_Values = array();
				foreach ($this->_aSiteuser_Properties as $oProperty)
				{
					$aTotal_Property_Values[$oProperty->id] = $oProperty->getValues($oSiteuser->id, FALSE);

					$oProperty_Value = array_shift($aTotal_Property_Values[$oProperty->id]);

					$this->_property($oSiteuser, $oProperty, $oProperty_Value);
				}

				$this->_printRow($this->_aData);

				// Additional people and companies
				$max = max(count($aSiteuser_People), count($aSiteuser_Companies));
				for ($i = 0; $i < $max; $i++)
				{
					$this->_aData = array('""', '""', '""');

					isset($aSiteuser_People[$i])
						? $this->_person($aSiteuser_People[$i])
						: array_pad($this->_aData, 19, '""'); // 3 + 16

					isset($aSiteuser_Companies[$i])
						? $this->_company($aSiteuser_Companies[$i])
						: array_pad($this->_aData, 33, '""'); // 3 + 16 + 14

					$this->_printRow($this->_aData);

					/*foreach ($this->_aSiteuser_Properties as $oProperty)
					{
						$oProperty_Value = array_shift($aTotal_Property_Values[$oProperty->id]);

						$this->_property($oSiteuser, $oProperty, $oProperty_Value);
					}*/
				}
			}

			$offset += $limit;
		}
		while (count($aSiteusers));

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