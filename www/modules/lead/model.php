<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead_Model
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Model extends Core_Entity
{
	public $contact = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'lead_directory_email' => array('foreign_key' => 'lead_id'),
		'directory_email' => array('through' => 'lead_directory_email', 'through_table_name' => 'lead_directory_emails', 'foreign_key' => 'lead_id'),

		'lead_directory_phone' => array('foreign_key' => 'lead_id'),
		'directory_phone' => array('through' => 'lead_directory_phone', 'through_table_name' => 'lead_directory_phones', 'foreign_key' =>
		'lead_id'),

		'lead_directory_website' => array('foreign_key' => 'lead_id'),
		'directory_website' => array('through' => 'lead_directory_website', 'through_table_name' => 'lead_directory_websites', 'foreign_key' => 'lead_id'),

		'lead_directory_address' => array('foreign_key' => 'lead_id'),
		'directory_address' => array('through' => 'lead_directory_address', 'through_table_name' => 'lead_directory_addresses', 'foreign_key' => 'lead_id'),

		'lead_note' => array(),
		'lead_shop_item' => array(),
		'lead_step' => array(),
		'lead_event' => array(),
		'event' => array('through' => 'lead_event'),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array(),
		'site' => array(),
		'lead_status' => array(),
		'lead_need' => array(),
		'lead_maturity' => array(),
		'crm_source' => array(),
		'siteuser' => array(),
		'shop' => array(),
		'shop_order' => array(),
		'deal' => array(),
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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['last_contacted'] = '0000-00-00 00:00:00';
		}
	}

	/**
	 * Get full name of lead
	 * @return string
	 */
	public function getFullName()
	{
		$aPartsFullName = array();

		!empty($this->surname) && $aPartsFullName[] = $this->surname;
		!empty($this->name) && $aPartsFullName[] = $this->name;
		!empty($this->patronymic) && $aPartsFullName[] = $this->patronymic;

		return implode(' ', $aPartsFullName);
	}

	/**
	 * Show source badge
	 * @return string
	 */
	public function showSource()
	{
		?><span class="badge badge-square" style="color: <?php echo htmlspecialchars($this->Crm_Source->color)?>; background-color:<?php echo Core_Str::hex2lighter($this->Crm_Source->color, 0.88)?>"><i class="<?php echo htmlspecialchars($this->Crm_Source->icon)?>"></i> <span class="hidden-xxs hidden-xs"><?php echo htmlspecialchars($this->Crm_Source->name)?></span></span><?php
	}

	/**
	 * Show phones badge
	 * @return string
	 */
	public function showPhones()
	{
		$aLead_Directory_Phones = $this->Lead_Directory_Phones->findAll(FALSE);
		foreach ($aLead_Directory_Phones as $oLead_Directory_Phone)
		{
			$oDirectory_Phone = $oLead_Directory_Phone->Directory_Phone;
			?><span class="badge badge-square badge-max-width lead-phone"><?php echo htmlspecialchars($oDirectory_Phone->value)?></span><?php
		}
	}

	/**
	 * Show e-mail badge
	 * @return string
	 */
	public function showEmails()
	{
		$aLead_Directory_Emails = $this->Lead_Directory_Emails->findAll(FALSE);
		foreach ($aLead_Directory_Emails as $oLead_Directory_Email)
		{
			$oDirectory_Email = $oLead_Directory_Email->Directory_Email;
			?><span class="badge badge-square badge-max-width lead-email"><a href="mailto:<?php echo htmlspecialchars($oDirectory_Email->value)?>"><?php echo htmlspecialchars($oDirectory_Email->value)?></a></span><?php
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function contactBackend()
	{
		ob_start();

		?><div class="semi-bold small"><?php echo htmlspecialchars($this->getFullName())?></div><?php

		if ($this->crm_source_id)
		{
			?><div class="margin-top-5"><?php echo $this->showSource()?></div><?php
		}

		if ($this->last_contacted != '0000-00-00 00:00:00')
		{
			?><span class="badge badge-square badge-max-width small lead-last-contact" title="<?php echo Core::_('Lead.last_contacted')?>"><i class="fa fa-handshake-o"></i> <?php echo Core_Date::timestamp2string(Core_Date::sql2timestamp($this->last_contacted))?></span><?php
		}

		?><div class="margin-top-5"><?php

		$this->showPhones();

		$this->showEmails();
		?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function datetimeBackend()
	{
		return '<span class="small2">' . Core_Date::timestamp2string(Core_Date::sql2timestamp($this->datetime)) . '</span>';
	}

	/**
	 * Get lead status bar
	 * @return string
	 */
	public function getStatusBar($oAdmin_Form_Controller)
	{
		$aLead_Statuses = Core_Entity::factory('Lead_Status')->getAllBySite_id(CURRENT_SITE);

		ob_start();

		if (count($aLead_Statuses))
		{
			$css = '<style>';

			$oCurrentLeadStatus = $this->Lead_Status;

			if ($oCurrentLeadStatus)
			{
				?>
				<div class="lead-status-name lead-status-name-<?php echo $this->id?>" style="color: <?php echo htmlspecialchars($oCurrentLeadStatus->color)?>"><?php echo htmlspecialchars($oCurrentLeadStatus->name)?></div>
				<?php
			}
			?>
			<div class="lead-stage-wrapper lead-stage-wrapper-<?php echo $this->id?>">
				<?php
				foreach ($aLead_Statuses as $oLead_Status)
				{
					$class = $oLead_Status->id == $this->lead_status_id
						? 'active'
						: '';

					switch ($oLead_Status->type)
					{
						case 1:
							$statusClass = 'finish';
						break;
						case 2:
							$statusClass = 'failed';
						break;
						default:
							$statusClass = '';
					}

					$darkerColor = Core_Str::hex2darker($oLead_Status->color, 0.1);

					$css .= '.lead-stage-wrapper #lead-stage-' . $oLead_Status->id . '.active { background-color: ' . htmlspecialchars($oLead_Status->color) . '; border-color: ' . $darkerColor . ' !important; }';
					?>
					<div id="lead-stage-<?php echo $oLead_Status->id?>" data-id="<?php echo $oLead_Status->id?>" class="lead-stage <?php echo $class?> <?php echo $statusClass?>" data-color="<?php echo htmlspecialchars($oLead_Status->color)?>" data-dark="<?php echo $darkerColor?>" data-name="<?php echo htmlspecialchars($oLead_Status->name)?>"></div>
					<?php
				}
				?>
			</div>

			<?php
			$css .= '</style>';

			$windowId = $oAdmin_Form_Controller->getWindowId();
			?>

			<script>
				$(function() {
					$.leadStatusBar(<?php echo $this->id?>, '<?php echo $oAdmin_Form_Controller->getWindowId()?>');

					$('#<?php echo $windowId?> .lead-stage-wrapper-<?php echo $this->id?> .lead-stage').on('mouseover', function(e){
						var jParent = $(this).parents('.lead-stage-wrapper'),
							jNameDiv = jParent.prev();

						if ($(this).css('cursor') == 'pointer')
						{
							jNameDiv
								.text($(this).data('name'))
								.css('color', $(this).data('color'));
						}
					});

					$('#<?php echo $windowId?> .lead-stage-wrapper-<?php echo $this->id?> .lead-stage').on('mouseout', function(){
						var jParent = $(this).parents('.lead-stage-wrapper'),
							jNameDiv = jParent.prev();

						jNameDiv
							.text(jParent.find('.lead-stage.active').data('name'))
							.css('color', jParent.find('.lead-stage.active').data('color'));
					});
				});
			</script>
			<?php
			echo $css;
		}

		return ob_get_clean();
	}

	/**
	 * Morph lead into another entity
	 * @param int $type type of entity
	 * @param int $siteuser_id siteuser id
	 * @return string
	 */
	public function morph($type, $siteuser_id = 0, $deal_template_id = 0)
	{
		if ($type == 0 || $type > 4)
		{
			return 'unknownMorphType';
		}

		$oSite = $this->Site;

		switch($type)
		{
			// Новый клиент
			case 1:
				if (Core::moduleIsActive('siteuser'))
				{
					$aLead_Directory_Emails = $this->Lead_Directory_Emails->findAll();

					$oSiteuser = isset($aLead_Directory_Emails[0])
						? $oSite->Siteusers->getByEmail($aLead_Directory_Emails[0]->Directory_Email->value)
						: NULL;

					$oSiteuser = $this->_convertToSiteuser($oSiteuser);

					$this->siteuser_id = $oSiteuser->id;
					$this->save();

					return 'success';
				}
			break;
			// Существующий клиент
			case 2:
				if (Core::moduleIsActive('siteuser') && $siteuser_id)
				{
					$oSiteuser = $oSite->Siteusers->getById($siteuser_id);

					if (!is_null($oSiteuser))
					{
						$oSiteuser = $this->_convertToSiteuser($oSiteuser);

						$this->siteuser_id = $oSiteuser->id;
						$this->save();

						return 'success';
					}
				}
			break;
			// Заказ
			case 3:
				if (Core::moduleIsActive('shop'))
				{
					$oShop_Order = $this->_convertToShopOrder();

					$this->shop_order_id = $oShop_Order->id;
					$this->save();

					return 'success';
				}
			break;
			// Сделка
			case 4:
				if (Core::moduleIsActive('deal') && $deal_template_id)
				{
					$oDeal_Template = Core_Entity::factory('Deal_Template')->getById($deal_template_id);

					if (!is_null($oDeal_Template))
					{
						$oDeal = $this->_convertToDeal($oDeal_Template);

						$this->deal_id = $oDeal->id;
						$this->save();

						return 'success';
					}
				}
			break;
		}
	}

	/*
	 * Convert lead to shop order
	 * @param object $oDeal_Template Deal_Template_Model
	 * @return Dea_Model object
	 */
	protected function _convertToDeal(Deal_Template_Model $oDeal_Template)
	{
		$oShop_Currency = Core_Entity::factory('Shop_Currency')->getDefault();

		$oDeal = Core_Entity::factory('Deal');
		$oDeal->shop_id = $this->shop_id;
		$oDeal->creator_id = $this->user_id;
		$oDeal->user_id = $this->user_id;
		$oDeal->shop_currency_id = $oShop_Currency->id;
		$oDeal->name = Core::_('Lead.morph_deal_name', $this->getFullName());
		$oDeal->description = Core::_('Lead.morph_description', $this->getFullName());
		$oDeal->start_datetime = Core_Date::timestamp2sql(time());

		$oDeal->deal_template_id = $oDeal_Template->id;

		$aDeal_Template_Steps = $oDeal_Template->Deal_Template_Steps->findAll();
		$oDeal->deal_template_step_id = isset($aDeal_Template_Steps[0])
			? $aDeal_Template_Steps[0]->id
			: 0;

		$oCompany_Department = $this->User->Company_Departments->getFirst();

		$oDeal->company_id = $oCompany_Department ? $oCompany_Department->company_id : 0;

		if (Core::moduleIsActive('siteuser') && $this->siteuser_id)
		{
			$oDeal->siteuser_id = $this->siteuser_id;

			$oSiteuser = $this->Siteuser;

			$aSiteuser_Companies = $oSiteuser->Siteuser_Companies->findAll();
			foreach ($aSiteuser_Companies as $oSiteuser_Company)
			{
				$oDeal_Siteuser = Core_Entity::factory('Deal_Siteuser');
				$oDeal_Siteuser->siteuser_company_id = $oSiteuser_Company->id;

				$oDeal->add($oDeal_Siteuser);
			}

			$aSiteuser_People = $oSiteuser->Siteuser_People->findAll();
			foreach ($aSiteuser_People as $oSiteuser_Person)
			{
				$oDeal_Siteuser = Core_Entity::factory('Deal_Siteuser');
				$oDeal_Siteuser->siteuser_person_id = $oSiteuser_Person->id;

				$oDeal->add($oDeal_Siteuser);
			}
		}

		// Товары
		$aLead_Shop_Items = $this->Lead_Shop_Items->findAll();
		foreach ($aLead_Shop_Items as $oLead_Shop_Item)
		{
			$oDeal_Shop_Item = Core_Entity::factory('Deal_Shop_Item');
			$oDeal_Shop_Item->shop_item_id = $oLead_Shop_Item->shop_item_id;
			$oDeal_Shop_Item->name = $oLead_Shop_Item->name;
			$oDeal_Shop_Item->quantity = $oLead_Shop_Item->quantity;
			$oDeal_Shop_Item->price = $oLead_Shop_Item->price;
			$oDeal_Shop_Item->marking = $oLead_Shop_Item->marking;
			$oDeal_Shop_Item->rate = $oLead_Shop_Item->rate;
			$oDeal_Shop_Item->user_id = $oLead_Shop_Item->user_id;
			$oDeal_Shop_Item->type = $oLead_Shop_Item->type;
			$oDeal_Shop_Item->shop_warehouse_id = $oLead_Shop_Item->shop_warehouse_id;

			$oDeal->add($oDeal_Shop_Item);
		}

		$oDeal->amount = $this->amount;
		$oDeal->save();

		return $oDeal;
	}

	/*
	 * Convert lead to shop order
	 * @return Shop_Order_Model object
	 */
	protected function _convertToShopOrder()
	{
		$oShop = $this->Shop;

		$oShop_Order = Core_Entity::factory('Shop_Order');
		$oShop_Order->name = $this->name;
		$oShop_Order->surname = $this->surname;
		$oShop_Order->patronymic = $this->patronymic;
		$oShop_Order->company = $this->company;

		// Адреса
		$aLead_Directory_Addresses = $this->Lead_Directory_Addresses->findAll();
		if (isset($aLead_Directory_Addresses[0]))
		{
			$oDirectory_Address = $aLead_Directory_Addresses[0]->Directory_Address;

			$oShop_Order->postcode = $oDirectory_Address->postcode;
			$oShop_Order->address = $oDirectory_Address->value;

			$oShop_Country = Core_Entity::factory('Shop_Country')->getByName(trim($oDirectory_Address->country));

			if (!is_null($oShop_Country))
			{
				$oShop_Order->shop_country_id = $oShop_Country->id;

				$oShop_Country_Location_Cities = Core_Entity::factory('Shop_Country_Location_City');
				$oShop_Country_Location_Cities->queryBuilder()
					->select('shop_country_location_cities.*')
					->join('shop_country_locations', 'shop_country_locations.id', '=', 'shop_country_location_cities.shop_country_location_id')
					->where('shop_country_locations.shop_country_id', '=', $oShop_Country->id)
					->where('shop_country_location_cities.name', 'LIKE', trim($oDirectory_Address->city))
					->limit(1);

				$aShop_Country_Location_Cities = $oShop_Country_Location_Cities->findAll();

				if (isset($aShop_Country_Location_Cities[0]))
				{
					$oShop_Order->shop_country_location_city_id = $aShop_Country_Location_Cities[0]->id;
					$oShop_Order->shop_country_location_id = $aShop_Country_Location_Cities[0]->shop_country_location_id;
				}
			}
		}

		// Телефоны
		$aLead_Directory_Phones = $this->Lead_Directory_Phones->findAll();
		$oShop_Order->phone = isset($aLead_Directory_Phones[0])
			? $aLead_Directory_Phones[0]->Directory_Phone->value()
			: '';

		// E-mails
		$aLead_Directory_Emails = $this->Lead_Directory_Emails->findAll();
		$oShop_Order->email = isset($aLead_Directory_Emails[0])
			? $aLead_Directory_Emails[0]->Directory_Email->value()
			: '';

		$oShop_Order->description = Core::_('Lead.morph_description', $this->getFullName());

		Core::moduleIsActive('siteuser')
			&& $this->siteuser_id
			&& $oShop_Order->siteuser_id = $this->siteuser_id;

		$oShop->add($oShop_Order);

		$oShop_Order->createInvoice();
		$oShop_Order->save();

		// Товары
		$aLead_Shop_Items = $this->Lead_Shop_Items->findAll();
		foreach ($aLead_Shop_Items as $oLead_Shop_Item)
		{
			$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
			$oShop_Order_Item->shop_item_id = $oLead_Shop_Item->shop_item_id;
			$oShop_Order_Item->name = $oLead_Shop_Item->name;
			$oShop_Order_Item->quantity = $oLead_Shop_Item->quantity;
			$oShop_Order_Item->price = $oLead_Shop_Item->price;
			$oShop_Order_Item->marking = $oLead_Shop_Item->marking;
			$oShop_Order_Item->rate = $oLead_Shop_Item->rate;
			$oShop_Order_Item->user_id = $oLead_Shop_Item->user_id;
			$oShop_Order_Item->type = $oLead_Shop_Item->type;
			$oShop_Order_Item->shop_warehouse_id = $oLead_Shop_Item->shop_warehouse_id;

			$oShop_Order->add($oShop_Order_Item);
		}

		return $oShop_Order;
	}

	/*
	 * Convert lead to siteuser
	 * @param Siteuser_Model|NULL $oSiteuser
	 * @return Siteuser_Model object
	 */
	protected function _convertToSiteuser($oSiteuser)
	{
		$oSite = $this->Site;

		$oUser = Core_Auth::getCurrentUser();
		$user_id = !is_null($oUser) ? $oUser->id : 0;

		$aLead_Directory_Emails = $this->Lead_Directory_Emails->findAll();

		if (is_null($oSiteuser))
		{
			$email = isset($aLead_Directory_Emails[0])
				? trim($aLead_Directory_Emails[0]->Directory_Email->value)
				: NULL;

			$oSiteuser = Core_Entity::factory('Siteuser');
			$oSiteuser->site_id = $oSite->id;
			$oSiteuser->crm_source_id = $this->crm_source_id;
			$oSiteuser->login = !is_null($email) ? $email : '';
			$oSiteuser->password = Core_Password::get();
			$oSiteuser->email = $email;
			$oSiteuser->datetime = Core_Date::timestamp2sql(time());
			$oSiteuser->guid = Core_Guid::get();
			$oSiteuser->active = 1;
			$oSiteuser->user_id = $user_id;
			$oSiteuser->last_activity = Core_Date::timestamp2sql(time());
			$oSiteuser->save();

			$oSiteuser->login == ''
				&& $oSiteuser->login('id' . $oSiteuser->id)->save();
		}

		$oSiteuser_Person = $oSiteuser->Siteuser_People->getByNameAndSurname($this->name, $this->surname);

		if (is_null($oSiteuser_Person))
		{
			$oSiteuser_Person = Core_Entity::factory('Siteuser_Person');
			$oSiteuser_Person->siteuser_id = $oSiteuser->id;
			$oSiteuser_Person->name = $this->name;
			$oSiteuser_Person->surname = $this->surname;
			$oSiteuser_Person->patronymic = $this->patronymic;
			$oSiteuser_Person->post = $this->post;
			$oSiteuser_Person->save();
		}

		// Адреса
		$aLead_Directory_Addresses = $this->Lead_Directory_Addresses->findAll();
		if (isset($aLead_Directory_Addresses[0]) && !strlen($oSiteuser_Person->address))
		{
			$oDirectory_Address = $aLead_Directory_Addresses[0]->Directory_Address;

			$oSiteuser_Person->country = $oDirectory_Address->country;
			$oSiteuser_Person->postcode = $oDirectory_Address->postcode;
			$oSiteuser_Person->city = $oDirectory_Address->city;
			$oSiteuser_Person->address = $oDirectory_Address->value;
		}

		!strlen($oSiteuser_Person->birthday)
			&& $oSiteuser_Person->birthday = $this->birthday;

		$oSiteuser_Person->user_id = $user_id;
		$oSiteuser_Person->save();

		// Телефоны
		$aLead_Directory_Phones = $this->Lead_Directory_Phones->findAll();
		foreach ($aLead_Directory_Phones as $oLead_Directory_Phone)
		{
			$oDirectory_Phone = $oLead_Directory_Phone->Directory_Phone;

			$oPerson_Directory_Phone = $oSiteuser_Person->Directory_Phones->getByValue($oDirectory_Phone->value);

			if (is_null($oPerson_Directory_Phone))
			{
				$oClone_Directory_Phone = clone $oDirectory_Phone;
				$oClone_Directory_Phone->save();

				$oSiteuser_Person_Directory_Phone = Core_Entity::factory('Siteuser_Person_Directory_Phone');
				$oSiteuser_Person_Directory_Phone->directory_phone_id = $oClone_Directory_Phone->id;
				$oSiteuser_Person->add($oSiteuser_Person_Directory_Phone);
			}
		}

		// E-mails
		foreach ($aLead_Directory_Emails as $oLead_Directory_Email)
		{
			$oDirectory_Email = $oLead_Directory_Email->Directory_Email;

			$oPerson_Directory_Email = $oSiteuser_Person->Directory_Emails->getByValue($oDirectory_Email->value);

			if (is_null($oPerson_Directory_Email))
			{
				$oClone_Directory_Email = clone $oDirectory_Email;
				$oClone_Directory_Email->save();

				$oSiteuser_Person_Directory_Email = Core_Entity::factory('Siteuser_Person_Directory_Email');
				$oSiteuser_Person_Directory_Email->directory_email_id = $oClone_Directory_Email->id;
				$oSiteuser_Person->add($oSiteuser_Person_Directory_Email);
			}
		}

		// Сайты
		$aLead_Directory_Websites = $this->Lead_Directory_Websites->findAll();
		foreach ($aLead_Directory_Websites as $oLead_Directory_Website)
		{
			$oDirectory_Website = $oLead_Directory_Website->Directory_Website;

			$oPerson_Directory_Website = $oSiteuser_Person->Directory_Websites->getByValue($oDirectory_Website->value);

			if (is_null($oPerson_Directory_Website))
			{
				$oClone_Directory_Website = clone $oDirectory_Website;
				$oClone_Directory_Website->save();

				$oSiteuser_Person_Directory_Website = Core_Entity::factory('Siteuser_Person_Directory_Website');
				$oSiteuser_Person_Directory_Website->directory_website_id = $oClone_Directory_Website->id;
				$oSiteuser_Person->add($oSiteuser_Person_Directory_Website);
			}
		}

		if (strlen(trim($this->company)))
		{
			$oSiteuser_Company = $oSiteuser->Siteuser_Companies->getByName($this->company);

			if (is_null($oSiteuser_Company))
			{
				$oSiteuser_Company = Core_Entity::factory('Siteuser_Company');
				$oSiteuser_Company->name = $this->company;
				$oSiteuser_Company->user_id = $user_id;

				$oSiteuser->add($oSiteuser_Company);
			}
		}

		return $oSiteuser;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function lead_need_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$aMasLeadNeeds = array(array('value' => Core::_('Admin.none'), 'color' => '#aebec4'));

		$aLead_Needs = Core_Entity::factory('Lead_Need')->getAllBySite_id(CURRENT_SITE);

		foreach ($aLead_Needs as $oLead_Need)
		{
			$aMasLeadNeeds[$oLead_Need->id] = array(
				'value' => $oLead_Need->name,
				'color' => $oLead_Need->color
			);
		}

		$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

		$oCore_Html_Entity_Dropdownlist
			->value($this->lead_need_id)
			->options($aMasLeadNeeds)
			->onchange("$.adminLoad({path: '{$oAdmin_Form_Controller->getPath()}', additionalParams: 'hostcms[checked][0][{$this->id}]=0&leadNeedId=' + $(this).find('li[selected]').prop('id'), action: 'changeNeed', windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function lead_maturity_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$aMasLeadMaturities = array(array('value' => Core::_('Admin.none'), 'color' => '#aebec4'));

		$aLead_Maturities = Core_Entity::factory('Lead_Maturity')->getAllBySite_id(CURRENT_SITE);

		foreach ($aLead_Maturities as $oLead_Maturity)
		{
			$aMasLeadMaturities[$oLead_Maturity->id] = array(
				'value' => $oLead_Maturity->name,
				'color' => $oLead_Maturity->color
			);
		}

		$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

		$oCore_Html_Entity_Dropdownlist
			->value($this->lead_maturity_id)
			->options($aMasLeadMaturities)
			->onchange("$.adminLoad({path: '{$oAdmin_Form_Controller->getPath()}', additionalParams: 'hostcms[checked][0][{$this->id}]=0&leadMaturityId=' + $(this).find('li[selected]').prop('id'), action: 'changeMaturity', windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function statusBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->getStatusBar($oAdmin_Form_Controller);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function entityBackend()
	{
		ob_start();

		if (Core::moduleIsActive('siteuser') && $this->siteuser_id)
		{
			?><a href="/admin/siteuser/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $this->siteuser_id?>]=1" onclick="$.modalLoad({path: '/admin/siteuser/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $this->siteuser_id?>]=1', view: 'list', windowId: 'id_content'}); return false"><i class="fa fa-user-o fa-fw margin-right-5 info" title="<?php echo htmlspecialchars($this->Siteuser->login)?>"></i></a><?php
		}

		if (Core::moduleIsActive('shop') && $this->shop_order_id)
		{
			?><a href="/admin/shop/order/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $this->shop_order_id?>]=1&shop_id=<?php echo $this->shop_id?>" onclick="$.modalLoad({path: '/admin/shop/order/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $this->shop_order_id?>]=1&shop_id=<?php echo $this->shop_id?>', view: 'list', windowId: 'id_content'}); return false"><i class="fa fa-shopping-basket fa-fw margin-right-5 darkorange" title="<?php echo htmlspecialchars($this->Shop_Order->invoice)?>"></i></a><?php
		}

		if (Core::moduleIsActive('deal') && $this->deal_id)
		{
			?><a href="/admin/deal/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $this->deal_id?>]=1" onclick="$.modalLoad({path: '/admin/deal/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $this->deal_id?>]=1', view: 'list', windowId: 'id_content'}); return false"><i class="fa fa-handshake-o fa-fw margin-right-5 yellow"title="<?php echo htmlspecialchars($this->Deal->name)?>"></i></a><?php
		}

		return ob_get_clean();
	}

	/**
	 * Notify Bots
	 * @return self
	 */
	public function notifyBotsChangeStatus()
	{
		if (Core::moduleIsActive('bot'))
		{
			$oModule = Core::$modulesList['lead'];
			Bot_Controller::notify($oModule->id, 0, $this->lead_status_id, $this);
		}

		return $this;
	}

	/**
	 * Get responsible users
	 * @return array
	 */
	public function getResponsibleUsers()
	{
		return $this->user_id
			? array($this->User)
			: array();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event lead.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Directory_Emails->deleteAll(FALSE);
		$this->Directory_Phones->deleteAll(FALSE);
		$this->Directory_Websites->deleteAll(FALSE);
		$this->Directory_Addresses->deleteAll(FALSE);
		$this->Lead_Directory_Emails->deleteAll(FALSE);
		$this->Lead_Directory_Phones->deleteAll(FALSE);
		$this->Lead_Directory_Addresses->deleteAll(FALSE);
		$this->Lead_Directory_Websites->deleteAll(FALSE);
		$this->Lead_Notes->deleteAll(FALSE);
		$this->Lead_Shop_Items->deleteAll(FALSE);
		$this->Lead_Steps->deleteAll(FALSE);
		$this->Lead_Events->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event lead.onBeforeGetRelatedSite
	 * @hostcms-event lead.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}