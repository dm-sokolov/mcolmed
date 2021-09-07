<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Siteuser_Company_Model
 *
 * @package HostCMS
 * @subpackage Siteuser
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Siteuser_Company_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'siteuser_company';

	/**
	 * Backend property
	 * @var mixed
	 */
	public $namePersonCompany = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $phone = NULL;

	 /**
	 * Backend property
	 * @var mixed
	 */
	 public $email = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	//public $img = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(

		'siteuser_company_directory_email' => array(),
		'directory_email' => array('through' => 'siteuser_company_directory_email'),

		'siteuser_company_directory_address' => array(),
		'directory_address' => array('through' => 'siteuser_company_directory_address'),

		'siteuser_company_directory_phone' => array(),
		'directory_phone' => array('through' => 'siteuser_company_directory_phone'),

		'siteuser_company_directory_messenger' => array(),
		'directory_messenger' => array('through' => 'siteuser_company_directory_messenger'),

		'siteuser_company_directory_social' => array(),
		'directory_social' => array('through' => 'siteuser_company_directory_social'),

		'siteuser_company_directory_website' => array(),
		'directory_website' => array('through' => 'siteuser_company_directory_website'),
		'event_siteuser' => array(),
		'deal' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'siteuser' => array(),
		'user' => array(),
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Get siteuser-company href
	 * @return string
	 */
	public function getHref()
	{
		return $this->Siteuser->getDirHref() . "company_" . intval($this->id) . '/';
	}

	/**
	 * Get siteuser-company path include CMS_FOLDER
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Get image file path
	 * @return string|NULL
	 */
	public function getImageFilePath()
	{
		return $this->image != ''
			? $this->getPath() . $this->image
			: NULL;
	}

	/**
	 * Get image href or default company icon
	 * @return string
	 */
	public function getImageHref()
	{
		return $this->image
			? $this->getImageFileHref()
			: '/modules/skin/bootstrap/img/default_user.png';
	}

	/**
	 * Get company avatar
	 * @return string
	 */
	public function getAvatar()
	{
		return strlen($this->image)
			? $this->getImageHref()
			: "/admin/siteuser/index.php?loadCompanyAvatar={$this->id}";
	}

	/**
	 * Get image href
	 * @return string
	 */
	public function getImageFileHref()
	{
		return '/' . $this->getHref() . $this->image;
	}

	/**
	 * Create files directory
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

		if (!is_dir($this->getPath()))
		{
			try
			{
				Core_File::mkdir($this->getPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete image file
	 * @return self
	 */
	public function deleteImageFile()
	{
		try
		{
			is_file($this->getImageFilePath()) && Core_File::delete($this->getImageFilePath());
		} catch (Exception $e) {}

		$this->image = '';
		$this->save();

		return $this;
	}

	/**
	 * Delete company directory
	 * @return self
	 */
	public function deleteDir()
	{
		$this->deleteImageFile();

		if (is_dir($this->getPath()))
		{
			try
			{
				Core_File::deleteDir($this->getPath());
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function phoneBackend()
	{
		$aDirectoryPhones = $this->Directory_Phones->findAll();

		$sResult = '';

		if (count($aDirectoryPhones))
		{
			$sResult = '<div class="row">';

			foreach ($aDirectoryPhones as $oDirectoryPhone)
			{
				$sResult .= ' <div class="col-xs-12">
				<span class="semi-bold">' . htmlspecialchars($oDirectoryPhone->value) . '</span>
				<br />
				<small class="gray">' . htmlspecialchars($oDirectoryPhone->Directory_Phone_Type->name) . '</small>
				</div>';
			}

			$sResult .= '</div>';
		}

		return $sResult;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function emailBackend()
	{
		$aDirectoryEmails = $this->Directory_Emails->findAll();

		$sResult = '';

		if (count($aDirectoryEmails))
		{
			foreach ($aDirectoryEmails as $oDirectoryEmail)
			{
				$sResult .= '<div class="row">
					<div class="col-xs-12">
						<a href="mailto:' . htmlspecialchars($oDirectoryEmail->value) . '">' . htmlspecialchars($oDirectoryEmail->value) . '</a>
					</div>
					<div class="col-xs-12">
						<small class="gray">' . htmlspecialchars($oDirectoryEmail->Directory_Email_Type->name) . '</small>
					</div>
				</div>';
			}
		}

		return $sResult;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function imgBackend()
	{
		return '<img src="' . $this->getAvatar() . '"/>';
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event siteuser_company.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		if (is_file($this->getImageFilePath()))
		{
			try
			{
				$newObject->createDir();
				Core_File::copy($this->getImageFilePath(), $newObject->getImageFilePath());
			}
			catch (Exception $e) {}
		}

		$newObject->tin = $this->tin;
		$newObject->bank_account = $this->bank_account;
		$newObject->headcount = $this->headcount;
		$newObject->annual_turnover = $this->annual_turnover;
		$newObject->business_area = $this->business_area;
		$newObject->description = $this->description;

		$aDirectory_Addresses = $this->Directory_Addresses->findAll();
		foreach ($aDirectory_Addresses as $oDirectory_Address)
		{
			$newObject->add(clone $oDirectory_Address);
		}

		$aDirectory_Phones = $this->Directory_Phones->findAll();
		foreach ($aDirectory_Phones as $oDirectory_Phone)
		{
			$newObject->add(clone $oDirectory_Phone);
		}

		$aDirectory_Emails = $this->Directory_Emails->findAll();
		foreach ($aDirectory_Emails as $oDirectory_Email)
		{
			$newObject->add(clone $oDirectory_Email);
		}

		$aDirectory_Socials = $this->Directory_Socials->findAll();
		foreach ($aDirectory_Socials as $oDirectory_Social)
		{
			$newObject->add(clone $oDirectory_Social);
		}

		$aDirectory_Messengers = $this->Directory_Messengers->findAll();
		foreach ($aDirectory_Messengers as $oDirectory_Messenger)
		{
			$newObject->add(clone $oDirectory_Messenger);
		}

		$aDirectory_Websites = $this->Directory_Websites->findAll();
		foreach ($aDirectory_Websites as $oDirectory_Website)
		{
			$newObject->add(clone $oDirectory_Website);
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event siteuser_company.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Directory_Addresses->deleteAll(FALSE);
		$this->Directory_Emails->deleteAll(FALSE);
		$this->Directory_Messengers->deleteAll(FALSE);
		$this->Directory_Phones->deleteAll(FALSE);
		$this->Directory_Socials->deleteAll(FALSE);
		$this->Directory_Websites->deleteAll(FALSE);

		if (Core::moduleIsActive('event'))
		{
			$this->Event_Siteusers->deleteAll(FALSE);
		}

		// Удаляем директорию
		$this->deleteDir();

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event siteuser_company.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event siteuser_company.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addXmlTag('dir', '/' . $this->getHref());

		return $this;
	}

	/**
	 * Merge company with another one
	 * @param Siteuser_Company_Model $oObject siteuser company
	 * @return self
	 */
	public function merge(Siteuser_Company_Model $oObject)
	{
		$this->description == ''
			&& $this->description = $oObject->description;

		$this->tin == ''
			&& $this->tin = $oObject->tin;

		$this->bank_account == ''
			&& $this->bank_account = $oObject->bank_account;

		$this->headcount == 0
			&& $this->headcount = $oObject->headcount;

		$this->annual_turnover == 0
			&& $this->annual_turnover = $oObject->annual_turnover;

		$this->business_area == ''
			&& $this->business_area = $oObject->business_area;

		// Image
		if ($this->image == '')
		{
			try
			{
				Core_File::copy($oObject->getImageFilePath(), $this->getPath() . $oObject->image);
				$this->image = $oObject->image;
			} catch (Exception $e) {
				Core_Message::show($e->getMessage(), 'error');
			}
		}

		// Directory_Addresses
		$aTmpAddresses = array();

		$aTmp_Directory_Addresses = $this->Directory_Addresses->findAll(FALSE);
		foreach ($aTmp_Directory_Addresses as $oDirectory_Address)
		{
			$aTmpAddresses[] = trim($oDirectory_Address->value);
		}

		$aDirectory_Addresses = $oObject->Directory_Addresses->findAll(FALSE);
		foreach ($aDirectory_Addresses as $oDirectory_Address)
		{
			strlen(trim($oDirectory_Address->value)) && !in_array($oDirectory_Address->value, $aTmpAddresses)
				&& $this->add(clone $oDirectory_Address);
		}

		// Directory_Phones
		$aTmpPhones = array();

		$aTmp_Directory_Phones = $this->Directory_Phones->findAll(FALSE);
		foreach ($aTmp_Directory_Phones as $oDirectory_Phone)
		{
			$aTmpPhones[] = Core_Str::sanitizePhoneNumber($oDirectory_Phone->value);
		}

		$aDirectory_Phones = $oObject->Directory_Phones->findAll(FALSE);
		foreach ($aDirectory_Phones as $oDirectory_Phone)
		{
			!in_array(Core_Str::sanitizePhoneNumber($oDirectory_Phone->value), $aTmpPhones)
				&& $this->add(clone $oDirectory_Phone);
		}

		// Directory_Emails
		$aTmpEmails = array();

		$aTmp_Directory_Emails = $this->Directory_Emails->findAll(FALSE);
		foreach ($aTmp_Directory_Emails as $oDirectory_Email)
		{
			$aTmpEmails[] = trim($oDirectory_Email->value);
		}

		$aDirectory_Emails = $oObject->Directory_Emails->findAll(FALSE);
		foreach ($aDirectory_Emails as $oDirectory_Email)
		{
			strlen(trim($oDirectory_Email->value)) && !in_array($oDirectory_Email->value, $aTmpEmails)
				&& $this->add(clone $oDirectory_Email);
		}

		// Directory_Socials
		$aTmpSocials = array();

		$aTmp_Directory_Socials = $this->Directory_Socials->findAll(FALSE);
		foreach ($aTmp_Directory_Socials as $oDirectory_Social)
		{
			$aTmpSocials[] = trim($oDirectory_Social->value);
		}

		$aDirectory_Socials = $oObject->Directory_Socials->findAll(FALSE);
		foreach ($aDirectory_Socials as $oDirectory_Social)
		{
			strlen(trim($oDirectory_Social->value)) && !in_array($oDirectory_Social->value, $aTmpSocials)
				&& $this->add(clone $oDirectory_Social);
		}

		// Directory_Messengers
		$aTmpMessengers = array();

		$aTmp_Directory_Messengers = $this->Directory_Messengers->findAll(FALSE);
		foreach ($aTmp_Directory_Messengers as $oDirectory_Messenger)
		{
			$aTmpMessengers[] = trim($oDirectory_Messenger->value);
		}

		$aDirectory_Messengers = $oObject->Directory_Messengers->findAll(FALSE);
		foreach ($aDirectory_Messengers as $oDirectory_Messenger)
		{
			strlen(trim($oDirectory_Messenger->value)) && !in_array($oDirectory_Messenger->value, $aTmpMessengers)
				&& $this->add(clone $oDirectory_Messenger);
		}

		// Directory_Websites
		$aTmpWebsites = array();

		$aTmp_Directory_Websites = $this->Directory_Websites->findAll(FALSE);
		foreach ($aTmp_Directory_Websites as $oDirectory_Website)
		{
			$aTmpWebsites[] = trim($oDirectory_Website->value);
		}

		$aDirectory_Websites = $oObject->Directory_Websites->findAll(FALSE);
		foreach ($aDirectory_Websites as $oDirectory_Website)
		{
			strlen(trim($oDirectory_Website->value)) && !in_array($oDirectory_Website->value, $aTmpWebsites)
				&& $this->add(clone $oDirectory_Website);
		}

		$this->save();

		$oObject->markDeleted();

		return $this;
	}

	/**
	 * Return html profile block
	 */
	public function getProfileBlock()
	{
		$aDirectory_Phones = $this->Directory_Phones->findAll();

		$oUser = Core_Auth::getCurrentUser();
		$sFullName = $this->name;

		$oAdmin_Form = Core_Entity::factory('Admin_Form', 230);

		$imgLink = $oAdmin_Form->Admin_Form_Actions->checkAllowedActionForUser($oUser, 'view')
			? '<a href="/admin/siteuser/representative/index.php?hostcms[action]=view&hostcms[checked][0][' . $this->id . ']=1" onclick="$.modalLoad({path: \'/admin/siteuser/representative/index.php\', action: \'view\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $this->id . ']=1\', windowId: \'id_content\'}); return false">' . htmlspecialchars($sFullName) . '</a>'
			: htmlspecialchars($sFullName);

		$nameLink = $oAdmin_Form->Admin_Form_Actions->checkAllowedActionForUser($oUser, 'edit')
			? '<a href="/admin/siteuser/representative/index.php?hostcms[action]=edit&hostcms[checked][0][' . $this->id . ']=1" onclick="$.modalLoad({path: \'/admin/siteuser/representative/index.php\', action: \'edit\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $this->id . ']=1&siteuser_id=' . $this->siteuser_id . '\', view: \'list\', windowId: \'id_content\'}); return false">
				<i class="fa fa-building"></i>
				<i class="fa fa-pencil"></i>
			</a>'
			: '<i class="fa fa-building"></i>';

		return '<li class="ticket-item">
			<div class="row">
				<div class="ticket-user ticket-siteuser col-lg-8 col-xs-12"><img class="user-avatar lazy" data-src="' . $this->getAvatar() .'" />' .
					'<span class="user-name">' . $imgLink . '</span>' . '
				</div>
				<div class="ticket-time col-lg-4 col-xs-12">
					' . ( isset($aDirectory_Phones[0]) ? ('<span class="time">' . htmlspecialchars($aDirectory_Phones[0]->value) . '</span>') : '') . '
				</div>
				<div class="ticket-state bg-palegreen">' . $nameLink . '</div>
			</div>
		</li>';
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event siteuser.onBeforeGetRelatedSite
	 * @hostcms-event siteuser.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Siteuser->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}