<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Lead.edit_title', $this->_object->getFullName())
			: Core::_('Lead.add_title'));

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab
			->add($oAdditionalRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oAdditionalRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('surname')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->class('form-control input-lg semi-bold black'), $oMainRow1)
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->class('form-control input-lg semi-bold black'), $oMainRow1)
			->move($this->getField('patronymic')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->class('form-control input-lg'), $oMainRow1)
			->move($this->getField('company')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))->class('form-control semi-bold black'), $oMainRow2)
			->move($this->getField('post')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
			->move($this->getField('amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))->class('form-control'), $oMainRow2)
			->move($this->getField('birthday')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);

		$oMainTab
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oAdditionalRow2);

		$oAdditionalTab
			->move($this->getField('site_id')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oAdditionalRow1)
			->move($this->getField('lead_status_id')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oAdditionalRow1)
			->move($this->getField('user_id')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5')), $oAdditionalRow1)
			->move($this->getField('siteuser_id')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oAdditionalRow2)
			->move($this->getField('shop_order_id')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oAdditionalRow2)
			->move($this->getField('deal_id')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oAdditionalRow2);

		$oAdditionalTab->delete($this->getField('lead_need_id'));

		$aMasLeadNeeds = array(array('value' => Core::_('Admin.none'), 'color' => '#aebec4'));

		$aLead_Needs = Core_Entity::factory('Lead_Need')->getAllBySite_id(CURRENT_SITE);

		foreach ($aLead_Needs as $oLead_Need)
		{
			$aMasLeadNeeds[$oLead_Need->id] = array(
				'value' => $oLead_Need->name,
				'color' => $oLead_Need->color
			);
		}

		$oDropdownlistLeadNeeds = Admin_Form_Entity::factory('Dropdownlist')
			->options($aMasLeadNeeds)
			->name('lead_need_id')
			->value($this->_object->lead_need_id)
			->caption(Core::_('Lead.lead_need_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow3->add($oDropdownlistLeadNeeds);

		$oAdditionalTab->delete($this->getField('lead_maturity_id'));

		$aMasLeadMaturities = array(array('value' => Core::_('Admin.none'), 'color' => '#aebec4'));

		$aLead_Maturities = Core_Entity::factory('Lead_Maturity')->getAllBySite_id(CURRENT_SITE);

		foreach ($aLead_Maturities as $oLead_Maturity)
		{
			$aMasLeadMaturities[$oLead_Maturity->id] = array(
				'value' => $oLead_Maturity->name,
				'color' => $oLead_Maturity->color
			);
		}

		$oDropdownlistLeadMaturities = Admin_Form_Entity::factory('Dropdownlist')
			->options($aMasLeadMaturities)
			->name('lead_maturity_id')
			->value($this->_object->lead_maturity_id)
			->caption(Core::_('Lead.lead_maturity_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow3->add($oDropdownlistLeadMaturities);

		$oAdditionalTab->delete($this->getField('crm_source_id'));

		$aMasCrmSources = array(array('value' => Core::_('Admin.none'), 'color' => '#aebec4'));

		$aCrm_Sources = Core_Entity::factory('Crm_Source')->findAll();
		foreach ($aCrm_Sources as $oCrm_Source)
		{
			$aMasCrmSources[$oCrm_Source->id] = array(
				'value' => $oCrm_Source->name,
				'color' => $oCrm_Source->color,
				'icon' => $oCrm_Source->icon
			);
		}

		$oDropdownlistCrmSources = Admin_Form_Entity::factory('Dropdownlist')
			->options($aMasCrmSources)
			->name('crm_source_id')
			->value($this->_object->crm_source_id)
			->caption(Core::_('Lead.crm_source_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow3
			->add($oDropdownlistCrmSources);

		$oMainTab
			->move($this->getField('last_contacted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

		$aLead_Statuses = Core_Entity::factory('Lead_Status')->getAllBySite_id(CURRENT_SITE);

		$css = '<style>';

		$sWizard = '<div class="col-xs-12 margin-bottom-10"><div id="lead-status-steps" class="wizard lead-status-step-wizard" data-target="#simplewizard-steps">
			<ul class="steps" data-lead-id="' . intval($this->_object->id) . '" data-step-id="' . ($this->_object->id ? intval($this->_object->lead_status_id) : 0 ) . '">';

		$i = 1;

		foreach ($aLead_Statuses as $oLead_Status)
		{
			$css .= '#simplewizardstep' . $oLead_Status->id . ' span:first-child { border-color: ' . $oLead_Status->color . '; color: ' . $oLead_Status->color . ' }' . "\n";
			$css .= '.lead-status-step-wizard ul li#simplewizardstep' . $oLead_Status->id . '.active::before { background-color: ' . $oLead_Status->color . ' }' . "\n";
			$css .= '.lead-status-step-wizard ul li#simplewizardstep' . $oLead_Status->id . '.active { color: ' . $oLead_Status->color . ' }' . "\n";
			$css .= '.lead-status-step-wizard ul li:hover { cursor: pointer; }' . "\n";
			$css .= '.lead-status-step-wizard ul li#simplewizardstep' . $oLead_Status->id . '.previous::before { background-color: ' . $oLead_Status->color . ' }' . "\n";

			$class = $oLead_Status->id == $this->_object->lead_status_id
				? 'active'
				: ($this->_object->lead_status_id == 0 && $i == 1
					? 'active'
					: '');

			switch ($oLead_Status->type)
			{
				case 1:
					$statusClass = ' finish';
				break;
				case 2:
					$statusClass = ' failed';
				break;
				default:
					$statusClass = '';
			}

			$sWizard .= '<li class="' . $class . $statusClass . '" id="simplewizardstep' . $oLead_Status->id . '" data-target="#simplewizardstep' . $oLead_Status->id . '" data-id="' . $oLead_Status->id . '"><span class="step">' . $i . '</span>' . htmlspecialchars($oLead_Status->name) . '<span class="chevron"></span></li>';

			$i++;
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$sWizard .= '</ul>
			</div></div>
			<script>
				$(function() {
					$("#' . $windowId . ' .lead-status-step-wizard ul.steps li").on("click", function(){
						$("#' . $windowId . ' .lead-status-step-wizard ul.steps li").each(function(){
							$(this)
								.removeClass("active")
								.removeClass("previous");
						});

						$(this).addClass("active");
						$("#' . $windowId . ' input[name=lead_status_id]").val($(this).data("id"));

						$(this).prevUntil("ul.steps").addClass("previous");

						if ($(this).hasClass("finish"))
						{
							mainFormLocker.unlock();

							var lead_status_id = $(this).data("id"),
								id = "hostcms[checked][0][' . $this->_object->id . ']",
								post = {},
								operation = "";

							post["last_step"] = 0;
							post["mode"] = "edit";

							if ($(this).hasClass("finish"))
							{
								operation = "finish";
								post["last_step"] = 1;
							}

							post[id] = 1;
							post["lead_status_id"] = lead_status_id;

							$.adminLoad({path: "/admin/lead/index.php", action: "morphLead", operation: operation, post: post, additionalParams: "", windowId: "' . $windowId . '"});
						}
					});

					$("#' . $windowId . ' .lead-status-step-wizard ul.steps li.active").prevUntil("ul.steps").addClass("previous");

					var jFirstStatus = $("#' . $windowId . ' .lead-status-step-wizard ul.steps li:first-child");
					jFirstStatus.length && $("#' . $windowId . ' input[name=lead_status_id]").val() == 0
						? $("#' . $windowId . ' input[name=lead_status_id]").val(jFirstStatus.data("id"))
						: 0;
				});
			</script>
		';

		$css .= '</style>';

		$oMainRow4->add(
			Admin_Form_Entity::factory('Code')->html($sWizard . $css)
		);

		// Телефоны
		$oLeadPhonesRow = Directory_Controller_Tab::instance('phone')
			->title(Core::_('Directory_Phone.phones'))
			->relation($object->Lead_Directory_Phones)
			->showPublicityControlElement(TRUE)
			->execute();

		// Email'ы
		$oLeadEmailsRow = Directory_Controller_Tab::instance('email')
			->title(Core::_('Directory_Email.emails'))
			->relation($object->Lead_Directory_Emails)
			->showPublicityControlElement(TRUE)
			->execute();

		// Сайты
		$oLeadWebsitesRow = Directory_Controller_Tab::instance('website')
			->title(Core::_('Directory_Website.sites'))
			->relation($object->Lead_Directory_Websites)
			->showPublicityControlElement(TRUE)
			->execute();

		// Адреса
		$oLeadAddressesRow = Directory_Controller_Tab::instance('address')
			->title(Core::_('Directory_Address.addresses'))
			->relation($object->Lead_Directory_Addresses)
			->showPublicityControlElement(TRUE)
			->execute();

		$oMainTab
			->add($oLeadPhonesRow)
			->add($oLeadEmailsRow)
			->add($oLeadAddressesRow)
			->add($oLeadWebsitesRow);

		$oMainTab
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			// ->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
			->move($this->getField('comment')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5);

		$oAdditionalTab->delete($this->getField('shop_id'));

		$aShops = Core_Entity::factory('Site', CURRENT_SITE)->Shops->findAll();

		$aShopsSelectOptions = array();

		foreach($aShops as $oShop)
		{
			$aShopsSelectOptions[$oShop->id] = htmlspecialchars($oShop->name);
		}

		$oAdmin_Form_Entity_Select_Shops = Admin_Form_Entity::factory('Select')
			->options($aShopsSelectOptions)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->id('select_shop_id')
			->name('select_shop_id')
			->value($this->_object->shop_id)
			->caption(Core::_('Lead.shop_id'));

		$oMainRow6
			->add($oAdmin_Form_Entity_Select_Shops)
			->add(
				Admin_Form_Entity::factory('Input')
					->divAttr(array('class' => 'hidden'))
					->type('hidden')
					->id('shop_id')
					->name('shop_id')
					->value($this->_object->shop_id)
			);

		// При редактировании сделки и наличии связанных с ней товаров выбор магазина не доступен
		if ($this->_object->id && $this->_object->Lead_Shop_Items->getCount())
		{
			$oAdmin_Form_Entity_Select_Shops->disabled('disabled');
		}
		else
		{
			$oMainRow6
				->add(
					 Admin_Form_Entity::factory('Script')
						->value('$("#' . $windowId . ' #shop_id").val($("#' . $windowId . ' #select_shop_id").val()); $("#' . $windowId . ' #select_shop_id").on("change", function (){
								$("#' . $windowId . ' #shop_id").val($(this).val());
							});'
						)
				);
		}

		// Дела
		/*$oLeadEvents = $this->_object->Events;

		$oLeadEvents
			->queryBuilder()
			->orderBy('events.id', 'DESC');

		$aLeadEvents = $oLeadEvents->findAll(FALSE);

		if (count($aLeadEvents))
		{
			$sLeadEventsContainerId = 'related-events';

			$oMainRow7->add(
				Admin_Form_Entity::factory('Code')
					->html(
						'<div class="col-xs-12">
							<h6 class="row-title before-blue no-margin-top">' . Core::_('Lead.tabEvents') . '
								<span>
									<i id="toggle-display-lead-events" class="fa fa-plus-circle fa-lg blue cursor-pointer no-margin" data-target="#' . $sLeadEventsContainerId . '"></i>
								</span>
							</h6>
						</div>'
					)
			);

			$oDivEvents = Admin_Form_Entity::factory('Div')
				->id($sLeadEventsContainerId)
				->class('margin-bottom-10')
				->style("display: none;")
				->add(
					Admin_Form_Entity::factory('Script')
						->value("$(function (){
							$.adminLoad({ path: '/admin/lead/event/index.php', additionalParams: 'lead_id=" . $this->_object->id . "&hideMenu=1', windowId: '{$sLeadEventsContainerId}' });
						});")
				);

			$oMainRow7->add($oDivEvents);
		}*/

		// История сделки
		$oLead_Steps = $this->_object->Lead_Steps;
		$oLead_Steps->queryBuilder()
			->clearOrderBy()
			->orderBy('lead_steps.id', 'DESC');

		$aLead_Steps = $oLead_Steps->findAll(FALSE);

		// Лента действий
		ob_start();
		?>
		<table class="table table-hover deal-history-table">
			<tbody>
				<?php
				$prevDate = NULL;
				$bClass = TRUE;

				foreach ($aLead_Steps as $oLead_Step)
				{
					$iDatetime = Core_Date::sql2timestamp($oLead_Step->datetime);
					$sDate = Core_Date::timestamp2date($iDatetime);

					if ($prevDate != $sDate)
					{
						$bClass = FALSE;

						// Печатаем полоску
						?>
						<tr class="border-top-none">
							<td colspan="3">
								<div class="hr-container">
									<hr class="hr-text" data-content="<?php echo Core_Date::timestamp2string(Core_Date::date2timestamp($sDate), FALSE)?>" />
								</div>
							</td>
						</tr>
						<?php
						$prevDate = $sDate;
					}

					$class = !$bClass
						? 'class="border-top-none"'
						: '';
					?>
					<tr <?php echo $class?>>
						<td class="darkgray"><?php echo date("H:i", $iDatetime)?></td>
						<td width="60%">
							<div style="color: <?php echo $oLead_Step->Lead_Status->color?>"><?php echo htmlspecialchars($oLead_Step->Lead_Status->name)?></div>
						</td>
						<td width="40%" class="darkgray">
							<?php
							if ($oLead_Step->User->id)
							{
								$oLead_Step->User->showAvatarWithName();
							}
							?>
						</td>
					</tr>
					<?php
					$bClass = TRUE;
				}
				?>
			</tbody>
		</table>

		<?php
		$oHtml_Table_Deal_Steps = Admin_Form_Entity::factory('Code')
			->html(ob_get_clean());

		$countNotes = $this->_object->Lead_Notes->getCount()
			? '<span class="badge badge-palegreen">' . $this->_object->Lead_Notes->getCount() . '</span>'
			: '';

		$countShopItems = $this->_object->Lead_Shop_Items->getCount()
			? '<span class="badge badge-orange">' . $this->_object->Lead_Shop_Items->getCount() . '</span>'
			: '';

		$countEvents = $this->_object->Lead_Events->getCount()
			? '<span class="badge badge-yellow">' . $this->_object->Lead_Events->getCount() . '</span>'
			: '';

		ob_start();
		?>
		<div class="tabbable">
			<ul class="nav nav-tabs tabs-flat" id="dealTabs">
				<li class="active">
					<a data-toggle="tab" href="#notes">
						<?php echo Core::_("Lead.tabNotes")?> <?php echo $countNotes?>
					</a>
				</li>
				<?php
				if (Core::moduleIsActive('shop'))
				{
				?>
					<li>
						<a data-toggle="tab" href="#items">
							<?php echo Core::_("Lead.tabShopItems")?> <?php echo $countShopItems?>
						</a>
					</li>
				<?php
				}

				if (Core::moduleIsActive('event'))
				{
				?>
				<li>
					<a data-toggle="tab" href="#events">
						<?php echo Core::_("Lead.tabEvents")?> <?php echo $countEvents?>
					</a>
				</li>
				<?php
				}

				if ($this->_object->id)
				{
					?>
					<li>
						<a data-toggle="tab" href="#history">
							<?php echo Core::_("Lead.tabHistory")?>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
			<div class="tab-content tabs-flat">
				<div id="notes" class="tab-pane active">
				<?php
				Admin_Form_Entity::factory('Div')
					->controller($this->_Admin_Form_Controller)
					->id("{$windowId}-lead-notes")
					->add(
						$this->_object->id
							? $this->_addLeadNotes()
							: Admin_Form_Entity::factory('Code')->html(
								Core_Message::get(Core::_('Lead.enable_after_save'), 'warning')
							)
					)
					->execute();
				?>
				</div>
				<?php
				if (Core::moduleIsActive('shop'))
				{
				?>
					<div id="items" class="tab-pane">
					<?php
					Admin_Form_Entity::factory('Div')
						->id("{$windowId}-lead-shop-items")
						->add(
							$this->_object->id
								? $this->_addLeadShopItems()
								: Admin_Form_Entity::factory('Code')->html(
									Core_Message::get(Core::_('Lead.enable_after_save'), 'warning')
								)
						)
						->execute();
					?>
					</div>
				<?php
				}

				if (Core::moduleIsActive('event'))
				{
				?>
					<div id="events" class="tab-pane">
					<?php
						Admin_Form_Entity::factory('Div')
							->id("{$windowId}-related-events")
							->class('related-events')
							->add(
								$this->_object->id
									? $this->_addLeadEvents()
									: Admin_Form_Entity::factory('Code')->html(
										Core_Message::get(Core::_('Lead.enable_after_save'), 'warning')
									)
							)
							->execute();
					?>
					</div>
				<?php
				}

				if ($this->_object->id)
				{
					?>
					<div id="history" class="tab-pane">
						<div class="scroll-history-<?php echo $this->_object->id?>">
							<?php echo $oHtml_Table_Deal_Steps->execute() ?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		$height = count($aLead_Steps) <= 5
			? 'auto'
			: '400px';
		?>
		<script>
		$(function (){
			setTimeout(function () {
				$('#<?php echo $windowId?> .scroll-history-<?php echo $this->_object->id?>').slimscroll({
						height: '<?php echo $height?>',
						color: 'rgba(0, 0, 0, 0.3)',
						size: '5px'
					});
			}, 500);
		});
		</script>
		<?php
		$oMainRow7->add(Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12')
			->add(
				Admin_Form_Entity::factory('Code')
					->html(ob_get_clean())
			)
		);

		return $this;
	}

	protected function _addLeadNotes()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				$.adminLoad({ path: '/admin/lead/note/index.php', additionalParams: 'lead_id=" . $this->_object->id . "', windowId: '{$windowId}-lead-notes' });
			});");
	}

	protected function _addLeadShopItems()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				$.adminLoad({ path: '/admin/lead/shop/item/index.php', additionalParams: 'lead_id=" . $this->_object->id . "', windowId: '{$windowId}-lead-shop-items' });
			});");
	}

	/*
	 * Add lead events
	 * @return Admin_Form_Entity
	 */
	protected function _addLeadEvents()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				$.adminLoad({ path: '/admin/lead/event/index.php', additionalParams: 'lead_id={$this->_object->id}&parentWindowId={$windowId}', windowId: '{$windowId}-related-events' });
			});");
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Lead_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$bAddLead = is_null($this->_object->id);

		$oCurrentLeadStatus = $bAddLead ? NULL : $this->_object->Lead_Status;

		$previousObject = clone $this->_object;

		parent::_applyObjectProperty();

		$object = $this->_object;

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oCurrentUser = Core_Auth::getCurrentUser();

		if ($bAddLead)
		{
			ob_start();
			$this->_addLeadNotes()->execute();
			Core::moduleIsActive('shop') && $this->_addLeadShopItems()->execute();
			Core::moduleIsActive('event') && $this->_addLeadEvents()->execute();
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());
		}

		Directory_Controller_Tab::instance('address')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('email')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('phone')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);
		Directory_Controller_Tab::instance('website')->applyObjectProperty($this->_Admin_Form_Controller, $this->_object);

		if ($bAddLead || !$bAddLead && !is_null($oCurrentLeadStatus) && $oCurrentLeadStatus->id != $this->_object->lead_status_id)
		{
			$sNewLeadStepDatetime = Core_Date::timestamp2sql(time());

			Core_Entity::factory('Lead_Step')
				->lead_id($this->_object->id)
				->lead_status_id($this->_object->lead_status_id)
				->user_id($oCurrentUser->id)
				->datetime($sNewLeadStepDatetime)
				->save();
		}

		if ($previousObject->lead_status_id != $this->_object->lead_status_id)
		{
			$this->_object->notifyBotsChangeStatus();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		// $windowId = $this->_Admin_Form_Controller->getWindowId();

		// Всегда id_content
		$sJsRefresh = '<script>
		if ($("#id_content .kanban-board").length && typeof _windowSettings != \'undefined\') {
			$("#id_content .btn-view-selector #kanban").click();
		}
		</script>';

		switch ($operation)
		{
			case 'save':
			case 'saveModal':
			case 'applyModal':
				$operation == 'saveModal' && $this->addMessage($sJsRefresh);
				$operation == 'applyModal' && $this->addContent($sJsRefresh);
			break;
			case 'markDeleted':
				$this->_object->markDeleted();

				$operation == 'markDeleted' && $this->addContent($sJsRefresh);
			break;
		}

		return parent::execute($operation);
	}
}