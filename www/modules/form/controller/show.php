<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ формы.
 *
 * Доступные свойства:
 *
 * - formFill ссылка на объект Form_Fill, доступный после process()
 * - mailType тип письма, 0 - 'text/html', 1 - 'text/plain'. По умолчанию 1
 * - mailXsl объект XSL-шаблона для отправки письма
 * - mailSubject тема письма о заполнении формы, если не указана, то используется указанная для формы
 * - from электронный адрес, от которого направляется письмо. По умолчанию первый из указанных кураторов формы
 *
 * <code>
 * $Form_Controller_Show = new Form_Controller_Show(
 * 	Core_Entity::factory('Form', 1)
 * );
 *
 * $Form_Controller_Show
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('ОтобразитьФорму')
 * 	)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Form
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Form_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'values',
		'mailType',
		'mailXsl',
		'mailSubject',
		'mailFromFieldName',
		'formFill',
		'captcha',
		'from'
	);

	protected $_aEmails = array();

	/**
	 * Error Code
	 * 0 - CAPTCHA
	 * 1 - заполнены не все поля
	 * 2 - слишком частое добавление
	 * 3 - Spam
	 */
	protected $_error = NULL;

	/**
	 * Get Error Code
	 * @return mixed NULL|integer
	 */
	public function getError()
	{
		return $this->_error;
	}

	protected $_aForm_Fields_Dir_Tree = array();
	protected $_aForm_Fields = array();
	protected $_aForm_Fields_Tree = array();

	/**
	 * Constructor.
	 * @param Form_Model $oForm form
	 */
	public function __construct(Form_Model $oForm)
	{
		parent::__construct($oForm->clearEntities());

		$this->mailType = 1;
		$this->values = array();

		// Массив адресов получателей
		$this->_aEmails = $this->_getEmails();

		$this->captcha = $oForm->use_captcha;

		// Send from first email in the list
		$this->from = reset($this->_aEmails);

		// Разделы формы
		$aForm_Field_Dirs = $oForm->Form_Field_Dirs->findAll(TRUE);
		foreach ($aForm_Field_Dirs as $oForm_Field_Dir)
		{
			$this->_aForm_Fields_Dir_Tree[$oForm_Field_Dir->parent_id][] = $oForm_Field_Dir;
		}

		// Поля формы
		$aForm_Fields = $oForm->Form_Fields->getAllByactive(1, TRUE);
		foreach ($aForm_Fields as $oForm_Field)
		{
			$this->_aForm_Fields[$oForm_Field->id] = $oForm_Field;
			$this->_aForm_Fields_Tree[$oForm_Field->form_field_dir_id][] = $oForm_Field;
		}

		$this->_addFormFields(0, $this);
	}

	/**
	 * Add additional email
	 * @param string $email email
	 */
	public function addEmail($email)
	{
		Core_Valid::email($email)
			&& !in_array($email, $this->_aEmails)
			&& $this->_aEmails[] = $email;

		return $this;
	}

	/**
	 * Get emails
	 * @return array
	 */
	public function getEmails()
	{
		return $this->_aEmails;
	}

	/**
	 * Clear emails
	 * @return self
	 */
	public function clearEmails()
	{
		$this->_aEmails = array();
		return $this;
	}

	/**
	 * Create notification for subscribers
	 * @return self
	 */
	protected function _createNotification(Form_Fill_Model $oForm_Fill)
	{
		$oModule = Core::$modulesList['form'];

		$oForm = $this->getEntity();

		if ($oModule && Core::moduleIsActive('notification'))
		{
			$oNotification_Subscribers = Core_Entity::factory('Notification_Subscriber');
			$oNotification_Subscribers->queryBuilder()
				->where('notification_subscribers.module_id', '=', $oModule->id)
				->where('notification_subscribers.type', '=', 0)
				->where('notification_subscribers.entity_id', '=', $oForm->id);

			$aNotification_Subscribers = $oNotification_Subscribers->findAll(FALSE);

			if (count($aNotification_Subscribers))
			{
				$oNotification = Core_Entity::factory('Notification');
				$oNotification
					->title(Core::_('Form.notification_new_form', strip_tags($oForm->name)))
					// ->description(strip_tags(''))
					->datetime(Core_Date::timestamp2sql(time()))
					->module_id($oModule->id)
					->type(0) // Заполнена форма
					->entity_id($oForm_Fill->id)
					->save();

				foreach ($aNotification_Subscribers as $oNotification_Subscriber)
				{
					// Связываем уведомление с сотрудником
					Core_Entity::factory('User', $oNotification_Subscriber->user_id)
						->add($oNotification);
				}
			}
		}

		return $this;
	}

	/**
	 * Array of uploaded files
	 * @var array
	 */
	protected $_aUploadedFiles = array();

	/**
	 * Add Form Values
	 * @return self
	 */
	public function addValues()
	{
		foreach ($this->_aForm_Fields as $oForm_Field)
		{
			// Get <value> node
			$Core_Xml_Entity_Value = NULL;
			$aChildren = $oForm_Field->getEntities();

			foreach ($aChildren as $oChild)
			{
				if (isset($oChild->name) && $oChild->name == 'value')
				{
					$Core_Xml_Entity_Value = $oChild;
					break;
				}
			}

			// or create new
			if (is_null($Core_Xml_Entity_Value))
			{
				$Core_Xml_Entity_Value = Core::factory('Core_Xml_Entity')
					->name('value');

				$oForm_Field->addEntity($Core_Xml_Entity_Value);
			}

			// если это список чекбоксов
			if ($oForm_Field->type == 9)
			{
				$Core_Xml_Entity_Values = Core::factory('Core_Xml_Entity')
					->name('values');

				$aList_Items = Core::moduleIsActive('list')
					? $oForm_Field->List->List_Items->getAllByActive(1)
					: array();

				foreach ($aList_Items as $oList_Item)
				{
					$value = Core_Array::get($this->values, $oForm_Field->name . '_' . $oList_Item->id);

					if (!is_null($value))
					{
						// Value
						$Core_Xml_Entity_Values->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('value')
									->value($oList_Item->id)
							);
					}
				}

				$oForm_Field->addEntity($Core_Xml_Entity_Values);
			}
			// File
			elseif ($oForm_Field->type == 2)
			{
				// Nothing to do
			}
			else
			{
				$aValues = Core_Array::get($this->values, $oForm_Field->name);

				// Могут быть множественные значения
				!is_array($aValues) && $aValues = array($aValues);

				foreach ($aValues as $value)
				{
					if (!is_null($value))
					{
						$Core_Xml_Entity_Value->value(
							isset($this->values[$oForm_Field->name]) && !is_array($this->values[$oForm_Field->name])
								? ($oForm_Field->type == 4
									? 1
									: $this->values[$oForm_Field->name])
								: ($oForm_Field->type == 4
									? 0
									: $oForm_Field->default_value)
						);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Run when pressing submit button
	 * @return self
	 * @hostcms-event Form_Controller_Show.onBeforeProcess
	 * @hostcms-event Form_Controller_Show.onAfterProcess
	 */
	public function process()
	{
		$this->_error = NULL;

		Core_Event::notify(get_class($this) . '.onBeforeProcess', $this);

		$oForm = $this->getEntity();

		$this->addValues();

		// нажали кнопку - получаем данные из формы и сохраняем их
		if ($this->captcha == 0 || Core_Captcha::valid(Core_Array::getPost('captcha_id'), Core_Array::getPost('captcha')))
		{
			// Массив содержащий пути прикрепленных файлов и их имена
			$this->_aUploadedFiles = array();

			// Antispam
			if (Core::moduleIsActive('antispam'))
			{
				$Antispam_Controller = new Antispam_Controller();

				foreach ($this->_aForm_Fields as $oForm_Field)
				{
					$antispamValues = Core_Array::get($this->values, $oForm_Field->name, '');

					if (!is_array($antispamValues))
					{
						$Antispam_Controller->addText($antispamValues);
					}
					else
					{
						foreach ($antispamValues as $antispamValue)
						{
							!is_array($antispamValue)
								&& $Antispam_Controller->addText($antispamValue);
						}
					}
				}

				$bAntispamAnswer = $Antispam_Controller->execute();

				if (!$bAntispamAnswer)
				{
					$this->_error = 3;

					// Spam!
					$this->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('errorId')
							->value($this->_error)
					);
					return $this;
				}
			}

			// Проверяем на соответствие заполнения обязательным полям
			foreach ($this->_aForm_Fields as $oForm_Field)
			{
				if ($oForm_Field->obligatory
					// тип не "Список из флажков" и не "Файл"
					&& $oForm_Field->type != 9 && $oForm_Field->type != 2
					&& trim(Core_Array::get($this->values, $oForm_Field->name, '')) == ''
				)
				{
					$this->_error = 1;
					// Заполните все обязательные поля!
					$this->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('errorId')
							->value($this->_error)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('errorFormFieldId')
							->value($oForm_Field->id)
					);
					return $this;
				}
			}

			$sIp = Core_Array::get($_SERVER, 'REMOTE_ADDR');

			// Проверка времени, прошедшего с момента заполнения предыдущей формы
			$oForm_Fills = $oForm->Form_Fills;

			$oForm_Fills->queryBuilder()
				->where('ip', '=', $sIp)
				->where('datetime', '>', Core_Date::timestamp2sql(time() - ADD_COMMENT_DELAY))
				->limit(1);

			if ($oForm_Fills->getCount())
			{
				$this->_error = 2;

				// Прошло слишком мало времени с момента последней отправки Вами формы!
				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('errorId')
						->value($this->_error)
				);
				return $this;
			}

			$this->formFill = $oForm_Fill = Core_Entity::factory('Form_Fill')->ip($sIp);
			$oForm->add($oForm_Fill);

			// Форма заполнена успешно
			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('ip')
					->value($oForm_Fill->ip)
			)->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('datetime')
					->value($oForm_Fill->datetime)
			);

			// UTM, Openstat or From
			$oSource_Controller = new Source_Controller();
			$oForm_Fill->source_id = $oSource_Controller->getId();

			$oForm_Fill->clearEntitiesAfterGetXml(FALSE);

			foreach ($this->_aForm_Fields as $oForm_Field)
			{
				$oForm_Field->clearEntitiesAfterGetXml(FALSE);

				// если это список чекбоксов
				if ($oForm_Field->type == 9)
				{
					$aList_Items = Core::moduleIsActive('list')
						? $oForm_Field->List->List_Items->getAllByActive(1)
						: array();

					foreach ($aList_Items as $oList_Item)
					{
						$value = Core_Array::get($this->values, $oForm_Field->name . '_' . $oList_Item->id);

						if (!is_null($value))
						{
							$value = trim($value);

							$oForm_Fill_Field = Core_Entity::factory('Form_Fill_Field')
								->value($value)
								->form_field_id($oForm_Field->id);

							$oForm_Fill
								->add($oForm_Fill_Field)
								->addEntity(
									$oForm_Fill_Field->clearEntitiesAfterGetXml(FALSE)
								);
						}
					}
				}
				// File
				elseif ($oForm_Field->type == 2)
				{
					$value = Core_Array::get($this->values, $oForm_Field->name);

					if (is_array($value))
					{
						$aToUpload = array();
						// One file
						if (!is_array($value['name']) && $value['size'] > 0)
						{
							$aToUpload[] = array(
								'size' => $value['size'],
								'name' => $value['name'],
								'tmp_name' => $value['tmp_name']
							);
						}
						// Multiple Values
						elseif (is_array($value['name']))
						{
							$iCount = count($value['name']);

							for ($i = 0; $i < $iCount; $i++)
							{
								if ($value['size'][$i] > 0)
								{
									$aToUpload[] = array(
										'size' => $value['size'][$i],
										'name' => $value['name'][$i],
										'tmp_name' => $value['tmp_name'][$i]
									);
								}
							}
						}

						foreach ($aToUpload as $aFileValue)
						{
							$oForm_Fill_Field = Core_Entity::factory('Form_Fill_Field')
								->value($aFileValue['name'])
								->form_field_id($oForm_Field->id);

							$oForm_Fill
								->add($oForm_Fill_Field)
								->addEntity(
									$oForm_Fill_Field->clearEntitiesAfterGetXml(FALSE)
								);

							Core_File::moveUploadedFile($aFileValue['tmp_name'], $oForm_Fill_Field->getPath());

							$this->_aUploadedFiles[] = array(
								'filepath' => $oForm_Fill_Field->getPath(),
								'filename' => $aFileValue['name']
							);
						}
					}
				}
				else
				{
					$aValues = Core_Array::get($this->values, $oForm_Field->name);

					// Могут быть множественные значения
					!is_array($aValues) && $aValues = array($aValues);

					// Удалять Emoji
					$bRemoveEmoji = strtolower(Core_Array::get(Core_DataBase::instance()->getConfig(), 'charset')) != 'utf8mb4';

					foreach ($aValues as $value)
					{
						if (!is_null($value))
						{
							$bRemoveEmoji
								&& $value = Core_Str::removeEmoji($value);

							$value = trim($value);

							$oForm_Fill_Field = Core_Entity::factory('Form_Fill_Field')
								->value($oForm_Field->type == 4 ? 1 : $value)
								->form_field_id($oForm_Field->id);

							$oForm_Fill
								->add($oForm_Fill_Field)
								->addEntity(
									$oForm_Fill_Field->clearEntitiesAfterGetXml(FALSE)
								);
						}
					}
				}
			}

			$this->addEntity($oForm_Fill);

			$this->_entity->addEntities($this->_entities);

			$this->sendEmail();

			$this->_createNotification($oForm_Fill);

			$oForm->create_lead
				&& Core::moduleIsActive('lead')
				&& $this->_createLead($oForm_Fill);

			Core_Event::notify(get_class($this) . '.onAfterProcess', $this, array($oForm_Fill));

			$this
				->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('success')
						->value(1)
				);
		}
		else
		{
			$this->_error = 0;

			// Вы неверно ввели число подтверждения отправки формы!
			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('errorId')
					->value($this->_error)
			);
		}

		return $this;
	}

	/**
	 * Create lead from form fill.
	 * @param Form_Fill_Model $oForm_Fill fill form
	 * @return self
	 */
	protected function _createLead(Form_Fill_Model $oForm_Fill)
	{
		$oForm = $this->getEntity();

		$oSite = $oForm->Site;

		$aForm_Lead_Conformities = $oForm->Form_Lead_Conformities->findAll();

		if (count($aForm_Lead_Conformities))
		{
			$aConformities = $aLeadValues = array();
			foreach ($aForm_Lead_Conformities as $oForm_Lead_Conformity)
			{
				$aConformities[$oForm_Lead_Conformity->form_field_id] = $oForm_Lead_Conformity->conformity;
			}

			$aForm_Fill_Field = $oForm_Fill->Form_Fill_Fields->findAll(FALSE);
			foreach ($aForm_Fill_Field as $oForm_Fill_Field)
			{
				if (isset($aConformities[$oForm_Fill_Field->form_field_id]))
				{
					if (strlen($oForm_Fill_Field->value))
					{
						$conformity = $aConformities[$oForm_Fill_Field->form_field_id];

						if (isset($aLeadValues[$conformity]))
						{
							!is_array($aLeadValues[$conformity])
								&& $aLeadValues[$conformity] = array($aLeadValues[$conformity]);

							$aLeadValues[$conformity][] = $oForm_Fill_Field->value;
						}
						else
						{
							$aLeadValues[$conformity] = $oForm_Fill_Field->value;
						}
					}
				}
			}

			// Ищем клиента по email
			if (isset($aLeadValues['email']) && strlen($aLeadValues['email']) && Core::moduleIsActive('siteuser'))
			{
				$oSiteuser = $oSite->Siteusers->getByEmail($aLeadValues['email']);

				if (is_null($oSiteuser))
				{
					$oSiteusers = $oSite->Siteusers;
					$oSiteusers->queryBuilder()
						->clear()
						->select('siteusers.*')
						->join('siteuser_people', 'siteusers.id', '=', 'siteuser_people.siteuser_id')
						->join('siteuser_people_directory_emails', 'siteuser_people.id', '=', 'siteuser_people_directory_emails.siteuser_person_id')
						->join('directory_emails', 'siteuser_people_directory_emails.directory_email_id', '=', 'directory_emails.id')
						->where('directory_emails.value', '=', $aLeadValues['email'])
						->limit(1);

					$aSiteusers = $oSiteusers->findAll();

					$oSiteuser = isset($aSiteusers[0])
						? $aSiteusers[0]
						: NULL;

					if (is_null($oSiteuser))
					{
						$oSiteusers = $oSite->Siteusers;
						$oSiteusers->queryBuilder()
							->select('siteusers.*')
							->join('siteuser_companies', 'siteusers.id', '=', 'siteuser_companies.siteuser_id')
							->join('siteuser_company_directory_emails', 'siteuser_companies.id', '=', 'siteuser_company_directory_emails.siteuser_company_id')
							->join('directory_emails', 'siteuser_company_directory_emails.directory_email_id', '=', 'directory_emails.id')
							->where('directory_emails.value', '=', $aLeadValues['email'])
							->limit(1);

						$aSiteusers = $oSiteusers->findAll();

						$oSiteuser = isset($aSiteusers[0])
							? $aSiteusers[0]
							: NULL;
					}
				}
			}
			else
			{
				$oSiteuser = NULL;
			}

			if (is_null($oSiteuser))
			{
				$oLead = Core_Entity::factory('Lead');
				$oLead->site_id = $oForm->site_id;

				$oLead_Statuses = $oSite->Lead_Statuses;
				$oLead_Statuses->queryBuilder()
					->clearOrderBy()
					->orderBy('lead_statuses.sorting', 'ASC')
					->limit(1);

				$aLead_Statuses = $oLead_Statuses->findAll();

				$oLead->lead_status_id = isset($aLead_Statuses[0])
					? $aLead_Statuses[0]->id
					: 0;

				$oLead->crm_source_id = $oForm->crm_source_id;

				$oLead->save();

				$bCreateAddress = FALSE;

				foreach ($aLeadValues as $conformity => $mValues)
				{
					switch ($conformity)
					{
						case 'email':
							!is_array($mValues) && $mValues = array($mValues);

							foreach ($mValues as $email)
							{
								$oDirectory_Email = Core_Entity::factory('Directory_Email')
									->directory_email_type_id(0)
									->public(0)
									->value($email)
									->save();

								$oLead->add($oDirectory_Email);
							}
						break;
						case 'phone':
							!is_array($mValues) && $mValues = array($mValues);

							foreach ($mValues as $phone)
							{
								$oDirectory_Phone = Core_Entity::factory('Directory_Phone')
									->directory_phone_type_id(0)
									->public(0)
									->value($phone)
									->save();

								$oLead->add($oDirectory_Phone);
							}
						break;
						case 'postcode':
						case 'country':
						case 'city':
						case 'address':
							$bCreateAddress = TRUE;
						break;
						case 'website':
							!is_array($mValues) && $mValues = array($mValues);

							foreach ($mValues as $website)
							{
								$oDirectory_Website = Core_Entity::factory('Directory_Website')
									->value($website)
									->save();

								$oLead->add($oDirectory_Website);
							}
						break;
						default:
							$oLead->$conformity = is_array($mValues) ? $mValues[0] : $mValues;
					}
				}

				if ($bCreateAddress)
				{
					$postcode = Core_Array::get($aLeadValues, 'postcode');
					$country = Core_Array::get($aLeadValues, 'country');
					$city = Core_Array::get($aLeadValues, 'city');
					$address = Core_Array::get($aLeadValues, 'address', '');

					$oDirectory_Address = Core_Entity::factory('Directory_Address')
						->postcode(is_array($postcode) ? $postcode[0] : $postcode)
						->country(is_array($country) ? $country[0] : $country)
						->city(is_array($city) ? $city[0] : $city)
						->value(is_array($address) ? $address[0] : $address)
						->directory_address_type_id(0)
						->public(0)
						->save();

					$oLead->add($oDirectory_Address);
				}

				$oLead->save();
			}
		}

		return $this;
	}

	/**
	 * Send Form By Email
	 * @return self
	 */
	public function sendEmail()
	{
		if (is_null($this->mailXsl))
		{
			throw new Core_Exception('Form Mail XSL does not exist.');
		}

		if (!is_object($this->formFill))
		{
			throw new Core_Exception('Additional call sendEmail() available just after process() call!');
		}

		$sXml = $this->getXml();

		$sMailText = Xsl_Processor::instance()
			->xml($sXml)
			->xsl($this->mailXsl)
			->process();

		$sMailText = trim($sMailText);

		// Тема письма
		$subject = $this->_getSubject();

		$replyTo = !is_null($this->mailFromFieldName) && Core_Valid::email(
				Core_Array::get($this->values, $this->mailFromFieldName)
			)
			? Core_Array::get($this->values, $this->mailFromFieldName)
			: NULL;

		// При текстовой отправке нужно преобразовать HTML-сущности в символы
		$this->mailType == 1 && $sMailText = html_entity_decode($sMailText, ENT_COMPAT, 'UTF-8');

		foreach ($this->_aEmails as $key => $sEmail)
		{
			// Delay 0.350s for second mail and others
			$key > 0 && usleep(350000);

			$oCore_Mail = Core_Mail::instance()
				->clear()
				->to($sEmail)
				->from($this->from)
				->subject($subject)
				->message($sMailText)
				->contentType($this->mailType == 0 ? 'text/html' : 'text/plain')
				->header('X-HostCMS-Reason', 'Form')
				->messageId();

			!is_null($replyTo)
				&& $oCore_Mail->header('Reply-To', $replyTo);

			foreach ($this->_aUploadedFiles as $aUploadedFile)
			{
				$oCore_Mail->attach($aUploadedFile);
			}

			$oCore_Mail->send();
		}

		return $this;
	}

	/**
	 * Get subject
	 * @return string
	 */
	protected function _getSubject()
	{
		$oForm = $this->getEntity();

		$subject = !is_null($this->mailSubject)
			? strval($this->mailSubject)
			: $oForm->email_subject;

		$subject = str_replace(array(
				'{id}',
				'{date}',
				'{datetime}'
			), array(
				$this->formFill->id,
				strftime(DATE_FORMAT, Core_Date::sql2timestamp($this->formFill->datetime)),
				strftime(DATE_TIME_FORMAT, Core_Date::sql2timestamp($this->formFill->datetime))
			), $subject);

		return $subject;
	}

	/**
	 * Get array of emails for notification
	 * @return array
	 */
	protected function _getEmails()
	{
		$oForm = $this->getEntity();

		// массив адресов получателей
		$aEmails = array_map('trim', explode(',', str_replace(';', ',', $oForm->email)));

		// Remove invalid email addresses
		$aEmails = array_filter($aEmails, array('Core_Valid', 'email'));

		return $aEmails;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Form_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		//$this->values = array();
		count($this->values) && $this->addValues();

		$siteuser_id = 0;
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			$oSiteuser && $siteuser_id = $oSiteuser->id;
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_id')
				->value($siteuser_id)
		);

		return parent::show();
	}

	/**
	 * _aListItemsTree
	 * @var array
	 */
	protected $_aListItemsTree = array();

	/**
	 * Add form fields
	 * @param int $parent_id parent group ID
	 * @param object $oParentObject parent object
	 * @return self
	 */
	protected function _addFormFields($parent_id, $oParentObject)
	{
		$oForm = $this->getEntity();

		$oForm->clearEntitiesAfterGetXml(FALSE);

		// Разделы формы
		if (isset($this->_aForm_Fields_Dir_Tree[$parent_id]))
		{
			foreach ($this->_aForm_Fields_Dir_Tree[$parent_id] as $oForm_Field_Dir)
			{
				$oParentObject->addEntity(
					$oForm_Field_Dir
						->clearEntities()
						->clearEntitiesAfterGetXml(FALSE)
				);

				$this->_addFormFields($oForm_Field_Dir->id, $oForm_Field_Dir);
			}
		}

		$aListTypes = array(3, 6, 9);

		// Поля формы
		if (isset($this->_aForm_Fields_Tree[$parent_id]))
		{
			foreach ($this->_aForm_Fields_Tree[$parent_id] as $oForm_Field)
			{
				$oForm_Field
					->clearEntities()
					->clearEntitiesAfterGetXml(FALSE);

				// Значения по умолчанию
				if ($oForm_Field->type != 9)
				{
					// Value
					$oForm_Field->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('value')
							->value(
								$oForm_Field->type == 4
									? 0
									: $oForm_Field->default_value)
						);
				}

				// Общий список значений
				if (Core::moduleIsActive('list')
					&& in_array($oForm_Field->type, $aListTypes) && $oForm_Field->list_id != 0)
				{
					$oList = $oForm_Field->List;

					$oTmpList = clone $oList;
					$oTmpList
						->clearEntities()
						->clearEntitiesAfterGetXml(FALSE)
						->id($oList->id);

					$oList_Items = $oList->List_Items;
					$oList_Items->queryBuilder()
						->where('list_items.active', '=', 1);

					$aList_Items = $oList_Items->findAll(FALSE);
					foreach ($aList_Items as $oList_Item)
					{
						$this->_aListItemsTree[$oList_Item->parent_id][] = $oList_Item;
					}

					$this->_addListItems(0, $oTmpList);

					// Clear after each List!
					$this->_aListItemsTree = array();

					$oForm_Field->addEntity($oTmpList);
				}

				$oParentObject->addEntity($oForm_Field);
			}
		}

		return $this;
	}

	/**
	 * Add List Items to the $oObject
	 * @param int $parentId
	 * @param List_Model $oObject
	 * @return self
	 */
	protected function _addListItems($parentId, $oObject)
	{
		if (isset($this->_aListItemsTree[$parentId]))
		{
			foreach ($this->_aListItemsTree[$parentId] as $oList_Item)
			{
				$oObject->addEntity(
					$oList_Item->clearEntities()->clearEntitiesAfterGetXml(FALSE)
				);

				$this->_addListItems($oList_Item->id, $oList_Item);
			}
		}

		return $this;
	}
}
