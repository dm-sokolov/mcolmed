<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lead_Controller_Kanban
 *
 * @package HostCMS
 * @subpackage Lead
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lead_Controller_Kanban extends Admin_Form_Controller_View
{
	public function execute()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_View = Admin_View::create($this->_Admin_Form_Controller->Admin_View)
			->pageTitle($oAdmin_Form_Controller->pageTitle)
			->module($oAdmin_Form_Controller->module);

		$aAdminFormControllerChildren = array();

		foreach ($oAdmin_Form_Controller->getChildren() as $oAdmin_Form_Entity)
		{
			if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs
				|| $oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus)
			{
				$oAdmin_View->addChild($oAdmin_Form_Entity);
			}
			else
			{
				$aAdminFormControllerChildren[] = $oAdmin_Form_Entity;
			}
		}

		// При показе формы могут быть добавлены сообщения в message, поэтому message показывается уже после отработки формы
		ob_start();
		?>
		<div class="table-toolbar">
			<?php $this->_Admin_Form_Controller->showFormMenus()?>
			<div class="table-toolbar-right pull-right">
				<?php $this->_Admin_Form_Controller->pageSelector()?>
				<?php $this->_Admin_Form_Controller->showChangeViews()?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
		foreach ($aAdminFormControllerChildren as $oAdmin_Form_Entity)
		{
			$oAdmin_Form_Entity->execute();
		}

		$this->_showContent();
		$content = ob_get_clean();

		$oAdmin_View
			->content($content)
			->message($oAdmin_Form_Controller->getMessage())
			->show();

		//$oAdmin_Form_Controller->applyEditable();
		$oAdmin_Form_Controller->showSettings();

		return $this;
	}

	/**
	 * Show form content in administration center
	 * @return self
	 */
	protected function _showContent()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_Language = $oAdmin_Form_Controller->getAdminLanguage();

		$aAdmin_Form_Fields = $oAdmin_Form->Admin_Form_Fields->findAll();

		$oSortingField = $oAdmin_Form_Controller->getSortingField();

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		$oUser = Core_Auth::getCurrentUser();

		$oLead_Statuses = Core_Entity::factory('Lead_Status');
		$oLead_Statuses->queryBuilder()
			->where('lead_statuses.type', '=', 0)
			->clearOrderBy()
			->orderBy('lead_statuses.sorting', 'ASC');

		$aLead_Statuses = $oLead_Statuses->findAll(FALSE);

		$aStatuses = array();

		?><style><?php
		foreach ($aLead_Statuses as $oLead_Status)
		{
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

			$aStatuses[$oLead_Status->id] = array(
				'name' => $oLead_Status->name,
				'color' => $oLead_Status->color,
				'class' => $statusClass
			);

			?>.lead-status-<?php echo $oLead_Status->id?> .well.bordered-left { border-left-color: <?php echo htmlspecialchars($oLead_Status->color)?>} <?php
		}
		?></style><?php
		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();
		?>
		<div class="container kanban-board">
			<div class="horizon-prev"><img src="/admin/images/scroll/l-arrow.png"></div>
			<div class="horizon-next"><img src="/admin/images/scroll/r-arrow.png"></div>
			<div class="row">

			<?php
			foreach ($aStatuses as $iLeadStatusId => $aLeadStatus)
			{
				?><div class="kanban-col col-xs-12 col-sm-3">
					<h5 style="color: <?php echo htmlspecialchars($aLeadStatus['color'])?>; padding-bottom: 5px; border-bottom: 2px solid <?php echo htmlspecialchars($aLeadStatus['color'])?>"><?php echo htmlspecialchars($aLeadStatus['name'])?></h5>
					<ul id="entity-list-<?php echo $iLeadStatusId?>" data-step-id="<?php echo $iLeadStatusId?>" class="kanban-list connectedSortable lead-status-<?php echo $iLeadStatusId?>">
					<?php
					foreach ($aEntities as $key => $oEntity)
					{
						if ($oEntity->lead_status_id == $iLeadStatusId)
						{
						?>
						<li id="lead-<?php echo $oEntity->id?>" data-id="<?php echo $oEntity->id?>" class="<?php echo $aLeadStatus['class']?>" data-lead-id="<?php echo $oEntity->id?>">
							<div class="well bordered-left">
								<div class="drag-handle"></div>
								<div class="row">
									<?php
									if ($oEntity->crm_source_id)
									{
									?>
									<div class="col-xs-12 col-sm-6">
										<?php echo $oEntity->showSource()?>
									</div>
									<?php
									}
									?>
									<div class="col-xs-12 text-align-right">
										<?php echo $oEntity->entityBackend()?>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12 well-body">
										<span><?php echo htmlspecialchars($oEntity->getFullName())?></span>
									</div>
								</div>
								<?php
								if (strlen($oEntity->comment))
								{
								?>
								<div class="row">
									<div class="col-xs-12 well-description">
										<span><?php echo htmlspecialchars($oEntity->comment)?></span>
									</div>
								</div>
								<?php
								}

								if (!is_null($oEntity->datetime) && $oEntity->datetime != '0000-00-00 00:00:00')
								{
								?>
								<div class="row">
									<div class="col-xs-12 well-description">
										<div class="event-date">
										<?php echo Event_Controller::getDateTime($oEntity->datetime)?>
										</div>
									</div>
								</div>
								<?php
								}
								?>
								<div class="row">
									<div class="col-xs-12">
										<?php echo $oEntity->showPhones()?>
										<?php echo $oEntity->showEmails()?>
									</div>
								</div>
								<div class="edit-entity" onclick="$.modalLoad({path: '/admin/lead/index.php', action: 'edit',operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oEntity->id?>]=1', windowId: 'id_content'});"><i class="fa fa-pencil"></i></div>
							</div>
						</li>
						<?php
						}
					}
					?>
					</ul>
				</div><?php
			}
			?>
			</div>

			<div class="kanban-action-wrapper hidden">
				<div class="kanban-actions text-align-center">
					<?php
					$oLead_Statuses = Core_Entity::factory('Lead_Status');
					$oLead_Statuses->queryBuilder()
						->where('lead_statuses.type', '!=', 0)
						->clearOrderBy()
						->orderBy('lead_statuses.sorting', 'ASC');

					$aLead_Statuses = $oLead_Statuses->findAll(FALSE);

					$count = count($aLead_Statuses);

					$width = $count
						? 90 / $count
						: 100;

					$deleteWidth = $width == 100
						? 100
						: 10;

					$deleteColor = '#777';

					foreach ($aLead_Statuses as $oLead_Status)
					{
						?>
						<ul id="entity-list-<?php echo $oLead_Status->id?>" data-step-id="<?php echo $oLead_Status->id?>" data-id="<?php echo $oLead_Status->id?>" data-background="<?php echo htmlspecialchars(Core_Str::hex2lighter($oLead_Status->color, 0.88))?>" data-old-background="<?php echo htmlspecialchars($oLead_Status->color)?>" style="width: <?php echo $width?>%; background-color: <?php echo htmlspecialchars($oLead_Status->color)?>; color: #fff;" class="connectedSortable kanban-action-item"><div class="kanban-action-item-name"><?php echo htmlspecialchars($oLead_Status->name)?></div><div class="return hidden"><i class="fa fa-undo"></i> <?php echo htmlspecialchars($oLead_Status->name)?></div></ul>
						<?php
					}
					?>

					<ul data-id="0" data-background="<?php echo htmlspecialchars(Core_Str::hex2lighter($deleteColor, 0.88))?>" data-old-background="<?php echo htmlspecialchars($deleteColor)?>" style="width: <?php echo $deleteWidth?>%; background-color: <?php echo htmlspecialchars($deleteColor)?>; color: #fff;" class="connectedSortable kanban-action-item"><div class="kanban-action-item-name"><i class="fa fa-trash"></i></div><div class="return hidden"><i class="fa fa-undo"></i></div></ul>
				</div>
			</div>
		</div>
		<script>
		$(function() {
			$.sortableKanban({path: '/admin/lead/index.php', container: '.kanban-board', windowId: '<?php echo $windowId?>', moveCallback: $._kanbanStepMoveLeadCallback});
			$.showKanban('.kanban-board');
		});
		</script>
		<?php
		return $this;
	}
}