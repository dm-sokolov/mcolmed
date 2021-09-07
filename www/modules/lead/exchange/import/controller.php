<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead import CSV controller
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Exchange_Import_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		// Кодировка импорта
		'encoding',
		// Файл импорта
		'file',
		// Позиция в файле импорта
		'seek',
		// Ограничение импорта по времени
		'time',
		// Ограничение импорта по количеству
		'step',
		// Настройка CSV: разделитель
		'separator',
		// Настройка CSV: ограничитель
		'limiter',
		// Настройка CSV: первая строка - название полей
		'firstlineheader',
		// Настройка CSV: массив соответствий полей CSV сущностям системы HostCMS
		'csv_fields',
		// Путь к картинкам
		'imagesPath',
		// Действие с существующими лидами:
		// 1 - обновить существующие элементы
		// 2 - не обновлять существующие элементы
		'importAction'
	);

	/**
	 * Current site
	 * @var Site_Model
	 */
	protected $_oSite;

	/**
	 * Current lead
	 * @var Lead_Model
	 */
	protected $_oLead;

	/**
	* Set $this->_oLead
	* @param Lead_Model $oLead
	* @return self
	*/
	public function setLead(Lead_Model $oLead)
	{
		$this->_oLead = $oLead;
		return $this;
	}

	/**
	 * Initialization
	 * @return self
	 */
	protected function init()
	{
		// Инициализация текущего элемента
		$this->_oLead = Core_Entity::factory('Lead');
		$this->_oLead->site_id = intval($this->_oSite->id);

		return $this;
	}

	/**
	 * CSV config
	 * @var array
	 */
	protected $_aConfig = NULL;

	/**
	 * Count of inserted leads
	 * @var int
	 */
	protected $_InsertedLeadsCount;

	/**
	 * Count of updated leads
	 * @var int
	 */
	protected $_UpdatedLeadsCount;

	/**
	 * Get inserted leads count
	 * @return int
	 */
	public function getInsertedLeadsCount()
	{
		return $this->_InsertedLeadsCount;
	}

	/**
	 * Get updated leads count
	 * @return int
	 */
	public function getUpdatedLeadsCount()
	{
		return $this->_UpdatedLeadsCount;
	}

	/**
	 * Array of inserted leads
	 * @var array
	 */
	protected $_aInsertedLeadIDs = array();

	/**
	 * Array of updated items
	 * @var array
	 */
	protected $_aUpdatedLeadIDs = array();

	/**
	 * Increment inserted leads
	 * @param int $iLeadId lead ID
	 * @return self
	 */
	protected function _incInsertedLeads($iLeadId)
	{
		if (!in_array($iLeadId, $this->_aInsertedLeadIDs))
		{
			$this->_aInsertedLeadIDs[] = $iLeadId;
			$this->_InsertedLeadsCount++;
		}

		return $this;
	}

	/**
	 * Increment updated leads
	 * @param int $iLeadId item ID
	 * @return self
	 */
	protected function _incUpdatedLeads($iLeadId)
	{
		if (!in_array($iLeadId, $this->_aUpdatedLeadIDs))
		{
			$this->_aUpdatedLeadIDs[] = $iLeadId;
			$this->_UpdatedLeadsCount++;
		}
		return $this;
	}

	/**
	 * List of phones
	 * @var array
	 */
	//protected $_aLeadPhones = array();

	/**
	 * Constructor.
	 * @param int $iCurrentInformationsystemId Informationsystem ID
	 * @param int $iCurrentGroupId current group ID
	 */
	public function __construct(Site_Model $oSite)
	{
		parent::__construct();

		$this->_aConfig = Core_Config::instance()->get('lead_exchange_csv', array()) + array(
			'maxTime' => 20,
			'maxCount' => 100
		);

		$this->_oSite = $oSite;

		$this->time = $this->_aConfig['maxTime'];
		$this->step = $this->_aConfig['maxCount'];

		$this->init();

		// Единожды в конструкторе, чтобы после __wakeup() не обнулялось
		$this->_InsertedLeadsCount = 0;
		$this->_UpdatedLeadsCount = 0;
	}

	/**
	* Импорт CSV
	* @hostcms-event Lead_Exchange_Import_Controller.onBeforeImport
	* @hostcms-event Lead_Exchange_Import_Controller.onAfterImport
	*/
	public function import()
	{
		Core_Event::notify('Lead_Exchange_Import_Controller.onBeforeImport', $this, array($this->_oSite));

		$fInputFile = fopen($this->file, 'rb');

		if ($fInputFile === FALSE)
		{
			throw new Core_Exception("");
		}

		// Remove first BOM
		if ($this->seek == 0)
		{
			$BOM = fgets($fInputFile, 4); // length - 1 байт

			if ($BOM === "\xEF\xBB\xBF")
			{
				$this->seek = 3;
			}
			else
			{
				fseek($fInputFile, 0);
			}
		}
		else
		{
			fseek($fInputFile, $this->seek);
		}

		$iCounter = 0;

		$timeout = Core::getmicrotime();

		$aCsvLine = array();

		while ((Core::getmicrotime() - $timeout + 3 < $this->time)
			&& $iCounter < $this->step
			&& ($aCsvLine = $this->getCSVLine($fInputFile)))
		{
			if (count($aCsvLine) == 1
			&& (is_null($aCsvLine[0]) || $aCsvLine[0] == ''))
			{
				continue;
			}

			$aData = array();

			foreach ($aCsvLine as $iKey => $sData)
			{
				if (!isset($this->csv_fields[$iKey]))
				{
					continue;
				}

				if ($sData != '')
				{
					switch ($this->csv_fields[$iKey])
					{
						case 'lead_id':
							$aData['id'] = $sData;
						break;
						case 'lead_surname':
							$aData['surname'] = $sData;
						break;
						case 'lead_name':
							$aData['name'] = $sData;
						break;
						case 'lead_patronymic':
							$aData['patronymic'] = $sData;
						break;
						case 'lead_company':
							$aData['company'] = $sData;
						break;
						case 'lead_post':
							$aData['post'] = $sData;
						break;
						case 'lead_amount':
							$aData['amount'] = Shop_Controller::instance()->convertPrice($sData);
						break;
						case 'lead_birthday':
							$aData['birthday'] = Core_Date::date2sql($sData);
						break;
						case 'lead_need':
							$oLead_Need = $this->_oSite->Lead_Needs->getByName($sData);

							!is_null($oLead_Need)
								&& $aData['lead_need_id'] = $oLead_Need->id;
						break;
						case 'lead_maturity':
							$oLead_Maturity = $this->_oSite->Lead_Maturities->getByName($sData);

							!is_null($oLead_Maturity)
								&& $aData['lead_maturity_id'] = $oLead_Maturity->id;
						break;
						case 'lead_source':
							$oCrm_Source = Core_Entity::factory('Crm_Source')->getByName($sData);

							!is_null($oCrm_Source)
								&& $aData['crm_source_id'] = $oCrm_Source->id;
						break;
						case 'lead_shop':
							$oShop = $this->_oSite->Shops->getByName($sData);

							!is_null($oShop)
								&& $aData['shop_id'] = $oShop->id;
						break;
						case 'lead_status':
							$oLead_Status = $this->_oSite->Lead_Statuses->getByName($sData);

							!is_null($oLead_Status)
								&& $aData['lead_status_id'] = $oLead_Status->id;
						break;
						case 'lead_comment':
							$aData['comment'] = $sData;
						break;
						case 'lead_address':
							$aData['addresses'] = explode('|', $sData);
							$aData['addresses'] = array_map('trim', $aData['addresses']);
						break;
						case 'lead_phone':
							$aData['phones'] = explode(',', $sData);
							$aData['phones'] = array_map('trim', $aData['phones']);
						break;
						case 'lead_email':
							$aData['emails'] = explode(',', $sData);
							$aData['emails'] = array_map('trim', $aData['emails']);
						break;
						case 'lead_website':
							$aData['websites'] = explode(',', $sData);
							$aData['websites'] = array_map('trim', $aData['websites']);
						break;
					}
				}
			}

			$this->_oLead = NULL;

			// By ID
			if (isset($aData['id']))
			{
				$oTmpObject = Core_Entity::factory('Lead')->getById($aData['id']);
				if (!is_null($oTmpObject))
				{
					$this->_oLead = $oTmpObject;

					$this->_incUpdatedLeads($this->_oLead->id);
				}
			}

			// By Phone
			if (is_null($this->_oLead) && isset($aData['phones']) && count($aData['phones']))
			{
				$oTmp = Core_Entity::factory('Lead');
				$oTmp->queryBuilder()
					->select('leads.*')
					->join('lead_directory_phones', 'leads.id', '=', 'lead_directory_phones.lead_id')
					->join('directory_phones', 'lead_directory_phones.directory_phone_id', '=', 'directory_phones.id')
					->where('leads.site_id', '=', $this->_oSite->id)
					->where('directory_phones.value', 'IN', $aData['phones'])
					->limit(1);

				$aLeads = $oTmp->findAll(FALSE);

				if (isset($aLeads[0]))
				{
					$this->_oLead = $aLeads[0];
					$this->_incUpdatedLeads($this->_oLead->id);
				}
			}

			// By Email
			if (is_null($this->_oLead) && isset($aData['emails']) && count($aData['emails']))
			{
				$oTmp = Core_Entity::factory('Lead');
				$oTmp->queryBuilder()
					->select('leads.*')
					->join('lead_directory_emails', 'leads.id', '=', 'lead_directory_emails.lead_id')
					->join('directory_emails', 'lead_directory_emails.directory_email_id', '=', 'directory_emails.id')
					->where('leads.site_id', '=', $this->_oSite->id)
					->where('directory_emails.value', 'IN', $aData['emails'])
					->limit(1);

				$aLeads = $oTmp->findAll(FALSE);

				if (isset($aLeads[0]))
				{
					$this->_oLead = $aLeads[0];
					$this->_incUpdatedLeads($this->_oLead->id);
				}
			}

			if (is_null($this->_oLead))
			{
				$this->_oLead = Core_Entity::factory('Lead');
				$this->_oLead->site_id = $this->_oSite->id;
				$this->_oLead->save();

				$this->_incInsertedLeads($this->_oLead->id);
			}
			elseif ($this->importAction == 2)
			{
				// если сказано - оставить без изменений
				continue;
			}

			foreach ($aData as $key => $value)
			{
				if (!is_array($value) && $key != 'id')
				{
					$this->_oLead->$key = $value;
				}
			}

			$this->_oLead->save();

			if (isset($aData['phones']))
			{
				$aPhones = array();
				$aLead_Directory_Phones = $this->_oLead->Lead_Directory_Phones->findAll(FALSE);
				foreach ($aLead_Directory_Phones as $oLead_Directory_Phone)
				{
					$oDirectory_Phone = $oLead_Directory_Phone->Directory_Phone;

					$aPhones[] = trim($oDirectory_Phone->value);
				}

				foreach ($aData['phones'] as $sPhone)
				{
					if (!in_array($sPhone, $aPhones))
					{
						$oDirectory_Phone_Type = Core_Entity::factory('Directory_Phone_Type')->getFirst();

						$oDirectory_Phone = Core_Entity::factory('Directory_Phone')
							->directory_phone_type_id($oDirectory_Phone_Type->id)
							->public(0)
							->value(Core_Str::sanitizePhoneNumber($sPhone))
							->save();

						$this->_oLead->add($oDirectory_Phone);
					}
				}
			}

			if (isset($aData['emails']))
			{
				$aEmails = array();
				$aLead_Directory_Emails = $this->_oLead->Lead_Directory_Emails->findAll(FALSE);
				foreach ($aLead_Directory_Emails as $oLead_Directory_Email)
				{
					$oDirectory_Email = $oLead_Directory_Email->Directory_Email;

					$aEmails[] = trim($oDirectory_Email->value);
				}

				foreach ($aData['emails'] as $sEmail)
				{
					if (!in_array($sEmail, $aEmails))
					{
						$oDirectory_Email_Type = Core_Entity::factory('Directory_Email_Type')->getFirst();

						$oDirectory_Email = Core_Entity::factory('Directory_Email')
							->directory_email_type_id($oDirectory_Email_Type->id)
							->public(0)
							->value($sEmail)
							->save();

						$this->_oLead->add($oDirectory_Email);
					}
				}
			}

			if (isset($aData['websites']))
			{
				$aWebsites = array();
				$aLead_Directory_Websites = $this->_oLead->Lead_Directory_Websites->findAll(FALSE);
				foreach ($aLead_Directory_Websites as $oLead_Directory_Website)
				{
					$oDirectory_Website = $oLead_Directory_Website->Directory_Website;

					$aWebsites[] = trim($oDirectory_Website->value);
				}

				foreach ($aData['websites'] as $sWebsite)
				{
					if (!in_array($sWebsite, $aWebsites))
					{
						$aUrl = @parse_url($sWebsite);

						// Если не был указан протокол, или
						// указанный протокол некорректен для url
						!array_key_exists('scheme', $aUrl)
							&& $sWebsite = 'http://' . $sWebsite;

						$oDirectory_Website = Core_Entity::factory('Directory_Website')
							->public(0)
							->value($sWebsite);

						$this->_oLead->add($oDirectory_Website);
					}
				}
			}

			if (isset($aData['addresses']))
			{
				$aAddresses = array();
				$aLead_Directory_Addresses = $this->_oLead->Lead_Directory_Addresses->findAll(FALSE);
				foreach ($aLead_Directory_Addresses as $oLead_Directory_Address)
				{
					$oDirectory_Adress = $oLead_Directory_Address->Directory_Address;

					$aAddresses[] = trim($oDirectory_Adress->value);
				}

				foreach ($aData['addresses'] as $sAddress)
				{
					$oDirectory_Address_Type = Core_Entity::factory('Directory_Address_Type')->getFirst();

					$oDirectory_Address = Core_Entity::factory('Directory_Address')
						->directory_address_type_id($oDirectory_Address_Type->id)
						->public(0)
						->value($sAddress)
						->save();

					$this->_oLead->add($oDirectory_Address);
				}
			}

			$iCounter++;
		} // end line

		$iCurrentSeekPosition = !$aCsvLine ? $aCsvLine : ftell($fInputFile);

		fclose($fInputFile);

		Core_Event::notify('Lead_Exchange_Import_Controller.onAfterImport', $this, array($this->_oSite, $iCurrentSeekPosition));

		return $iCurrentSeekPosition;
	}

	/**
	 * Convert object to string
	 * @return string
	 */
	public function __toString()
	{
		$aReturn = array();

		foreach ($this->_allowedProperties as $propertyName)
		{
			$aReturn[] = $propertyName . '=' . $this->$propertyName;
		}

		return implode(', ', $aReturn) . "<br/>";
	}

	/**
	 * Get CSV line from file
	 * @param handler file descriptor
	 * @return array
	 */
	public function getCSVLine($fileDescriptor)
	{
		if (strtoupper($this->encoding) != 'UTF-8' && defined('ALT_SITE_LOCALE'))
		{
			setlocale(LC_ALL, ALT_SITE_LOCALE);
		}

		$aCsvLine = @fgetcsv($fileDescriptor, 0, $this->separator, $this->limiter);

		if ($aCsvLine === FALSE)
		{
			return $aCsvLine;
		}

		setlocale(LC_ALL, SITE_LOCAL);
		setlocale(LC_NUMERIC, 'POSIX');

		return Core_Str::iconv($this->encoding, 'UTF-8', $aCsvLine);
	}

	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		$this->_oLead = NULL;

		return $this;
	}

	/**
	 * Execute some routine before serialization
	 * @return array
	 */
	public function __sleep()
	{
		$this->clear();

		return array_keys(
			get_object_vars($this)
		);
	}

	/**
	 * Reestablish any database connections that may have been lost during serialization and perform other reinitialization tasks
	 * @return self
	 */
	public function __wakeup()
	{
		date_default_timezone_set(Core::$mainConfig['timezone']);

		$this->init();

		// Инициализация текущего элемента
		$this->_oLead = Core_Entity::factory('Lead');
		$this->_oLead->site_id = intval($this->_oSite->id);

		return $this;
	}
}