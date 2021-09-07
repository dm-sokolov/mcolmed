<?php

require_once('../bootstrap.php');

if (Core::moduleIsActive('counter'))
{
	$oSite = Core_Entity::factory('Site')->find(intval(Core_Array::getGet('id')));

	if ($oSite->id)
	{
		!defined('CURRENT_SITE') && define('CURRENT_SITE', $oSite->id);

		Core::initConstants($oSite);

		Counter_Controller::instance()
			->site($oSite)
			->referrer(urldecode(strval(Core_Array::getGet('refer'))))
			->page(urldecode(strval(Core_Array::getGet('current_page'))))
			//->cookies(strval(Core_Array::getGet('cookie')))
			//->java(strval(Core_Array::getGet('java')))
			//->colorDepth(strval(Core_Array::getGet('px')))
			//->js(strval(Core_Array::getGet('js_version')))
			->display(strval(Core_Array::getGet('screen')))
			->ip(strval(Core_Array::get($_SERVER, 'REMOTE_ADDR')))
			->userAgent(strval(Core_Array::get($_SERVER, 'HTTP_USER_AGENT')))
			->counterId(intval(Core_Array::getGet('counter')))
			// По умолчанию счетчик обновляем
			->updateCounter(intval(Core_Array::getGet('update_counter', 1)))
			->buildCounter();
	}
}