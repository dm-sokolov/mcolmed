<?php

if (Core::moduleIsActive('siteuser'))
{
	$login = Core_Array::end(explode('/', trim(Core::$url['path'], '/')));

	$oSiteuser = Core_Entity::factory('Siteuser')->getByLogin($login);
	is_null($oSiteuser) && $oSiteuser = Core_Entity::factory('Siteuser');

	$Siteuser_Controller_Show = new Siteuser_Controller_Show(
		$oSiteuser
	);

	Core_Page::instance()->object = $Siteuser_Controller_Show;

	if ($oSiteuser->id)
	{
		Core_Page::instance()->title($oSiteuser->login);
		Core_Page::instance()->description($oSiteuser->login);
		Core_Page::instance()->keywords($oSiteuser->login);

		// AJAX add as friend
		if (!is_null(Core_Array::getGet('addFriend')))
		{
			$oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			// Пользователь авторизован
			if (!is_null($oCurrentSiteuser) && $oSiteuser->id != $oCurrentSiteuser->id)
			{
				$oFriend = $oCurrentSiteuser->Friends->getByRecipient_siteuser_id($oSiteuser->id);

				// Add as friend
				is_null($oFriend) && $oCurrentSiteuser->add($oSiteuser);

				echo json_encode('Added');
				exit();
			}
		}

		// AJAX delete friend
		if (!is_null(Core_Array::getGet('removeFriend')))
		{
			$oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			// Пользователь авторизован
			if (!is_null($oCurrentSiteuser) && $oCurrentSiteuser->id != $oSiteuser->id)
			{
				$oSiteuser_Relationship = $oCurrentSiteuser->Siteuser_Relationships->getByRecipient_siteuser_id($oSiteuser->id);

				// Remove friend
				!is_null($oSiteuser_Relationship) && $oSiteuser_Relationship->delete();

				echo json_encode('Removed');
				exit();
			}
		}
	}
}