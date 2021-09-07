<?php

/**
 * Counter.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'counter');

// Код формы
$iAdmin_Form_Id = 88;
$sAdminFormAction = '/admin/counter/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Counter.title'))
	->pageTitle(Core::_('Counter.title'));

$sFormPath = $oAdmin_Form_Controller->getPath();

// подключение верхнего меню
include CMS_FOLDER . '/admin/counter/menu.php';

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Строка навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Counter.title'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sFormPath, NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sFormPath, NULL, NULL, ''))
);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oController_Edit = Admin_Form_Action_Controller::factory(
		'Counter_Controller_Edit', $oAdmin_Form_Action
	);

	$oController_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oController_Edit);
}

// Источник данных
$oAdmin_Form_Controller->addDataset(new Counter_Dataset());

$oCounters = Core_Entity::factory('Site', CURRENT_SITE)->Counters;

$oCounters->queryBuilder()
	->where('date', '<=', Core_Date::timestamp2sql(time()))
	->where('date', '>=', Core_Date::timestamp2sql(strtotime('-3 months')));

//$aObjects = $oCounters->findAll();

if (count($oCounters->findAll()))
{
	$sWindowId = $oAdmin_Form_Controller->getWindowId();

	ob_start();

	$iMonth = 6;
	?>
	<div class="widget counter">
		<div class="widget-body">
			<div class="tabbable">
				<ul id="counterTabs" class="nav nav-tabs tabs-flat nav-justified">
					<li class="active">
						<a href="#website_traffic" data-toggle="tab"><?php echo Core::_('Counter.website_traffic')?></a>
					</li>
					<li class="">
						<a href="#search_bots" data-toggle="tab"><?php echo Core::_('Counter.crawlers')?></a>
					</li>
				</ul>

				<div class="tab-content tabs-flat no-padding">
					<div id="website_traffic" class="tab-pane animated fadeInUp active">
						<div class="row">
							<div class="col-xs-12">
								<div id="website-traffic-chart" class="chart chart-lg"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="col-sm-12 col-md-6">
									<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Counter.reset')?></button>
								</div>
							</div>
						</div>
					</div>
					<div id="search_bots" class="tab-pane padding-left-5 padding-right-10 animated fadeInUp">
						<div class="row">
							<div class="col-xs-12">
								<div id="search-bots-chart" class="chart chart-lg" style="width:100%"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="col-sm-12 col-md-6">
									<button class="btn btn-palegreen" id="setOriginalZoom"><i class="fa fa-area-chart icon-separator"></i><?php echo Core::_('Counter.reset')?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php
	$iBeginTimestamp = strtotime("-{$iMonth} month");

	$aHits = array();

	$iEndTimestamp = Core_Date::date2timestamp(date('Y-m-d 23:59:59'));
	for ($iTmp = $iBeginTimestamp; $iTmp <= $iEndTimestamp; $iTmp += 86400)
	{
		$aHits["'" . date('Y-m-d', $iTmp) . "'"] = 0;
	}

	$aBots = $aHosts = $aNewUsers = $aSessions = $aHits;

	$oCounters = Core_Entity::factory('Site', CURRENT_SITE)->Counters;
	$oCounters
		->queryBuilder()
		->where('date', '>=', date('Y-m-d 00:00:00', $iBeginTimestamp));

	$aCounters = $oCounters->findAll(FALSE);

	$iHitsCount = $iHostsCount = $iBotsCount = $iSessionsCount = $iNewUsersCount = 0;
	foreach ($aCounters as $oCounter)
	{
		$index = "'" . $oCounter->date . "'";

		$aSessions[$index] = $oCounter->sessions;
		$iSessionsCount += $oCounter->sessions;

		$aHits[$index] = $oCounter->hits;

		$iHitsCount += $oCounter->hits;

		$aHosts[$index] = $oCounter->hosts;
		$iHostsCount += $oCounter->hosts;

		$aNewUsers[$index] = $oCounter->new_users;
		$iNewUsersCount += $oCounter->new_users;

		$aBots[$index] = $oCounter->bots;
		$iBotsCount += $oCounter->bots;
	}

	$sTitles  = implode(',', array_keys($aHits));
	$sHits = implode(',', array_values($aHits));
	$sHosts = implode(',', array_values($aHosts));
	$sBots = implode(',', array_values($aBots));
	$sSessions = implode(',', array_values($aSessions));
	$sNewUsers = implode(',', array_values($aNewUsers));

	?>
	<script type="text/javascript">
	$(function(){
		var aScripts = [
			'jquery.flot.js',
			'jquery.flot.time.min.js',
			'jquery.flot.categories.min.js',
			'jquery.flot.tooltip.min.js',
			'jquery.flot.crosshair.min.js',
			'jquery.flot.selection.min.js',
			'jquery.flot.pie.min.js',
			'jquery.flot.resize.js'
		];

		$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/charts/flot/').done(function() {

			var titles = [<?php echo $sTitles?>],
				sessions_values = [<?php echo $sSessions?>],
				hits_values = [<?php echo $sHits?>],
				hosts_values = [<?php echo $sHosts?>],
				new_users_values = [<?php echo $sNewUsers?>],
				bots_values = [<?php echo $sBots?>],
				valueTitlesSissions = new Array(),
				valueTitlesHits = new Array(),
				valueTitlesHosts = new Array(),
				valueTitlesNewUsers = new Array(),
				valueTitlesBots = new Array();

			for(var i = 0; i < sessions_values.length; i++) {
				valueTitlesSissions.push([new Date(titles[i]), sessions_values[i]]);
				valueTitlesHits.push([new Date(titles[i]), hits_values[i]]);
				valueTitlesHosts.push([new Date(titles[i]), hosts_values[i]]);
				valueTitlesNewUsers.push([new Date(titles[i]), new_users_values[i]]);
				valueTitlesBots.push([new Date(titles[i]), bots_values[i]]);
			}

			var themeprimary = getThemeColorFromCss('themeprimary'), gridbordercolor = "#eee",  dataWebsiteTraffic = [{
				color: themeprimary,
				label: "<?php echo Core::_('Counter.graph_sessions')?>",
				data: valueTitlesSissions
			},
			{
				color: themesecondary,
				label: "<?php echo Core::_('Counter.graph_hits')?>",
				data: valueTitlesHits
			},
			{
				color: themethirdcolor,
				label: "<?php echo Core::_('Counter.graph_hosts')?>",
				data: valueTitlesHosts
			},
			{
				color: themefourthcolor,
				label: "<?php echo Core::_('Counter.graph_new_users')?>",
				data: valueTitlesNewUsers
			}
			/*,
			{
				color: themefifthcolor,
				label: "<?php echo Core::_('Counter.stat_bots')?>",
				data: valueTitlesBots
			}*/
			],
			dataSearchBots = [
				{
					color: themefifthcolor,
					label: "<?php echo Core::_('Counter.graph_bots')?>",
					data: valueTitlesBots
				}
			];

			var options = {
				series: {
					lines: {
						show: true
					},
					points: {
						show: true
					}
				},
				legend: {
					noColumns: 4,
					backgroundOpacity: 0.65
				},
				xaxis: {
					mode: "time",
					timeformat: "%d.%m.%Y",
					//tickDecimals: 0,
					color: gridbordercolor

				},
				yaxis: {
					min: 0,
					color: gridbordercolor
				},
				selection: {
					mode: "x"
				},
				grid: {
					hoverable: true,
					clickable: false,
					borderWidth: 0,
					aboveData: false
				},
				tooltip: true,
				tooltipOpts: {
					defaultTheme: false,
					dateFormat: "%d.%m.%Y",
					content: "<b>%s</b> : <span>%x</span> : <span>%y</span>",
				},
				crosshair: {
					mode: "x"
				}
			};

			var placeholderWebsiteTraffic = $("#<?php echo $sWindowId?> #website-traffic-chart"),
				placeholderSearchBots = $("#<?php echo $sWindowId?> #search-bots-chart");

			placeholderWebsiteTraffic.bind("plotselected", function (event, ranges) {
				//var zoom = $("#zoom").is(":checked");

				//if (zoom) {
					plotWebsiteTraffic = $.plot(placeholderWebsiteTraffic, dataWebsiteTraffic, $.extend(true, {}, options, {
						xaxis: {
							min: ranges.xaxis.from,
							max: ranges.xaxis.to
						}
					}));
				//}
			});

			placeholderSearchBots.bind("plotselected", function (event, ranges) {
				//var zoom = $("#zoom").is(":checked");

				//if (zoom) {
					plotSearchBots = $.plot(placeholderSearchBots, dataSearchBots, $.extend(true, {}, options, {
						xaxis: {
							min: ranges.xaxis.from,
							max: ranges.xaxis.to
						}
					}));
				//}
			});

			/*
			$("#zoom").on('change', function(){
				$this = $(this);

				if (!$this.prop('checked'))
				{
					$('#setOriginalZoom').hide();
					plot = $.plot(placeholder, data, options);
				}
				else
				{
					$('#setOriginalZoom').show();
				}
			});
			*/

			$('#<?php echo $sWindowId?> #website_traffic #setOriginalZoom').on('click', function(){
				plotWebsiteTraffic = $.plot(placeholderWebsiteTraffic, dataWebsiteTraffic, options);
			});

			$('#<?php echo $sWindowId?> #search_bots #setOriginalZoom').on('click', function(){
				plotSearchBots = $.plot(placeholderSearchBots, dataSearchBots, options);
			});


			/*placeholderWebsiteTraffic.bind("plotunselected", function (event) {
				// Do Some Work
			});*/

			var plotWebsiteTraffic = $.plot(placeholderWebsiteTraffic, dataWebsiteTraffic, options),
				plotSearchBots = $.plot(placeholderSearchBots, dataSearchBots, options);

			$("#<?php echo $sWindowId?> #website_traffic #clearSelection").click(function () {
				plotWebsiteTraffic.clearSelection();
			});

			$("#<?php echo $sWindowId?> #search_bots #clearSelection").click(function () {
				plotSearchBots.clearSelection();
			});

			/*
			$("#website_traffic #setSelection").click(function () {
				plotWebsiteTraffic.setSelection({
					xaxis: {
						from: 1994,
						to: 1995
					}
				});
			});
			*/
		});
	});
	</script>
	<?php

	$oAdmin_Form_Controller->addEntity(
		Admin_Form_Entity::factory('Code')->html(ob_get_clean())
	);
}

$oAdmin_Form_Controller->execute();