<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Core command controller.
 *
 * @package HostCMS
 * @subpackage Core\Command
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Command_Controller_Sitecount extends Core_Command_Controller
{
	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Core_Command_Controller_Sitecount.onBeforeShowAction
	 * @hostcms-event Core_Command_Controller_Sitecount.onAfterShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$oCore_Response = new Core_Response();

		Core_Page::instance()
			->response($oCore_Response);

		$oCore_Response
			->status(503)
			->header('Content-Type', "text/html; charset=UTF-8")
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS');

		$title = Core::_('Core.title_limit_available_sites_exceeded');

		ob_start();
		$oSkin = Core_Skin::instance()
			->title($title)
			->setMode('authorization')
			->header();

		Core::factory('Core_Html_Entity_Div')
			->class('indexMessage')
			->add(Core::factory('Core_Html_Entity_H1')->value($title))
			->add(Core::factory('Core_Html_Entity_P')->value(Core::_('Core.message_limit_available_sites_exceeded')
			))
			->add(Core::factory('Core_Html_Entity_P')->value(Core::_('Core.message_remove_unnecessary_sites')
			))
			->execute();

		$oSkin->footer();

		$oCore_Response->body(ob_get_clean());

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($oCore_Response));

		return $oCore_Response;
	}
}