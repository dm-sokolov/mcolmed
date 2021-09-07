<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cloud Controller Loadkey
 *
 * @package HostCMS
 * @subpackage Cloud
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cloud_Controller_Loadkey extends Admin_Form_Action_Controller
{
	public function execute($operation = NULL)
	{
		$sPath = '';

		$bStatus = TRUE;

		$oCloud_Controller = Cloud_Controller::factory($this->_object->id);
		if (!is_null($oCloud_Controller))
		{
			try
			{
				$sPath = $oCloud_Controller->getOauthCodeUrl();
			}
			catch(Core_Exception $e)
			{
				$sPath = $e->getMessage();
				$bStatus = FALSE;
			}
		}

		$aResponse = array(
			'url' => $sPath,
			'status'=> $bStatus
		);

		Core::showJson($aResponse);
	}
}