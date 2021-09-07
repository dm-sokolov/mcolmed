<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * 25.01.2017
 * art studio Morozov&Pimnev
 * исправляем косяк Медиасайта
 */

class Core_Response_Observer
{
	static public function onBeforeShowBody($response)
	{
		include_once CMS_FOLDER . 'opt_tng/start.php';
	}

	static public function onAfterShowBody($response)
	{
		include_once CMS_FOLDER . 'opt_tng/finish.php';
	}
}