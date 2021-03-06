<?php

if (Core::moduleIsActive('siteuser'))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
	is_null($oSiteuser) && $oSiteuser = Core_Entity::factory('Siteuser');

	$Siteuser_Controller_Show = new Siteuser_Controller_Show(
		$oSiteuser
	);

	// Авторизация по логину и паролю
	if (Core_Array::getPost('apply'))
	{
		$oSiteuser = $oSiteuser->Site->Siteusers->getByLoginAndPassword(
			strval(Core_Array::getPost('login')), strval(Core_Array::getPost('password'))
		);

		if (!is_null($oSiteuser))
		{
			if ($oSiteuser->active)
			{
				$expires = Core_Array::getPost('remember')
					? 2678400 // 31 день
					: 86400; // 1 день

				$oSiteuser->setCurrent($expires);

				// Change controller's siteuser
				$Siteuser_Controller_Show->setEntity($oSiteuser);

				// Location
				!is_null(Core_Array::getPost('location')) && $Siteuser_Controller_Show->go(
					strval(Core_Array::getPost('location'))
				);
			}
			else
			{
				$Siteuser_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('error')->value('Пользователь не активирован!')
					);
			}
		}
		else
		{
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error')->value('Введите корректный логин и пароль!')
			);
		}
	}

	// Авторизация по логину OpenID
	if (Core_Array::getPost('applyOpenIDLogin'))
	{
		$oSiteuser_OpenID_Controller = Siteuser_OpenID_Controller::instance();

		$iSiteuser_Identity_Provider = intval(Core_Array::getPost('identity_provider'));

		$oSiteuser_Identity_Provider = Core_Entity::factory('Siteuser_Identity_Provider')->find($iSiteuser_Identity_Provider);

		if (is_null($oSiteuser_Identity_Provider->id))
		{
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('provider_error')->value('Провайдер аутентификации не найден!')
			);
		}
		else
		{
			$sLogin = Core_Array::getPost('openid_login');
			$sIdentityURL = sprintf($oSiteuser_Identity_Provider->url, $sLogin);

			$oSiteuser_OpenID_Controller
				->setIdentityURL($sIdentityURL)
				->setTrustRoot('http://' . Core_Array::get($_SERVER, "HTTP_HOST"))
				->setRequiredFields(array('email', 'fullname'))
				->setOptionalFields(array('nickname', 'dob', 'gender', 'postcode', 'country', 'language', 'timezone'));

			if ($oSiteuser_OpenID_Controller->getOpenIDServer())
			{
				// Send Response from OpenID server to this script
				$oSiteuser_OpenID_Controller
					->setReturnURL(
						'http://' . Core_Array::get($_SERVER, "HTTP_HOST") . Core_Page::instance()->structure->getPath()
					)
					->redirect();
			}
			else
			{
				$aError = $oSiteuser_OpenID_Controller->GetError();
				$Siteuser_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('provider_error')->value($aError['description'])
				);
			}
		}
	}

	// Авторизация по OpenID
	if (Core_Array::getPost('applyOpenID'))
	{
		$oSiteuser_OpenID_Controller = Siteuser_OpenID_Controller::instance();

		$sIdentityURL = Core_Array::getPost('openid');

		$oSiteuser_OpenID_Controller
			->setIdentityURL($sIdentityURL)
			->setTrustRoot('http://' . Core_Array::get($_SERVER, "HTTP_HOST"))
			->setRequiredFields(array('email', 'fullname'))
			->setOptionalFields(array('nickname', 'dob', 'gender', 'postcode', 'country', 'language', 'timezone'));

		if ($oSiteuser_OpenID_Controller->getOpenIDServer())
		{
			// Send Response from OpenID server to this script
			$oSiteuser_OpenID_Controller
				->setReturnURL(
					'http://' . Core_Array::get($_SERVER, "HTTP_HOST") . Core_Page::instance()->structure->getPath()
				)
				->redirect();
		}
		else
		{
			$aError = $oSiteuser_OpenID_Controller->GetError();
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('provider_error')->value($aError['description'])
			);
		}
	}

	// Данные от сервера OpenID
	if (Core_Array::getGet('openid_mode') == 'id_res')
	{
		$oSiteuser_OpenID_Controller = Siteuser_OpenID_Controller::instance();

		$bValidate = $oSiteuser_OpenID_Controller
			->setIdentityURL(Core_Array::getGet('openid_identity'))
			->validateWithServer();

		// VALID
		if ($bValidate)
		{
			$sIdentity = $oSiteuser_OpenID_Controller->getIdentityUrl();

			$oSite = Core_Entity::factory('Site', CURRENT_SITE);

			$oSiteusers = $oSite->Siteusers;
			$oSiteusers->queryBuilder()
				->select('siteusers.*')
				->join('siteuser_identities', 'siteuser_identities.siteuser_id', '=', 'siteusers.id')
				->where('siteuser_identities.identity', '=', $sIdentity)
				->limit(1);

			$aSiteusers = $oSiteusers->findAll(FALSE);

			if (!count($aSiteusers))
			{
				// Create new siteuser
				$oSiteuser = Core_Entity::factory('Siteuser');

				$nickname = trim(strval($oSiteuser_OpenID_Controller->getAttribute('nickname')));

				if (strlen($nickname) && !is_null($oSite->Siteusers->getByLogin($nickname)))
				{
					$nickname = '';
				}

				$oSiteuser->login = $nickname;
				$oSiteuser->password = Core_Hash::instance()->hash(
					Core_Password::get(12)
				);
				$oSiteuser->email = trim(strval($oSiteuser_OpenID_Controller->getAttribute('email')));
				$oSiteuser->name = trim(strval($oSiteuser_OpenID_Controller->getAttribute('fullname')));
				$oSiteuser->save();

				if (!strlen($oSiteuser->login))
				{
					$oSiteuser->login = 'id' . $oSiteuser->id;
					$oSiteuser->save();
				}

				// Add siteuser's identity
				$oSiteuser_Identity = Core_Entity::factory('Siteuser_Identity');
				$oSiteuser_Identity->identity = $sIdentity;
				$oSiteuser->add($oSiteuser_Identity);

				// Add into default group
				$oSiteuser_Group = $oSiteuser->Site->Siteuser_Groups->getDefault();

				if (!is_null($oSiteuser_Group))
				{
					$oSiteuser_Group->add($oSiteuser);
				}

				$oSiteuser->activate();
			}
			else
			{
				$oSiteuser = $aSiteusers[0];
			}

			$oSiteuser->setCurrent();

			// Change controller's siteuser
			$Siteuser_Controller_Show->setEntity($oSiteuser);
		}
		elseif ($oSiteuser_OpenID_Controller->isError())
		{
			$aError = $oSiteuser_OpenID_Controller->GetError();
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('provider_error')->value($aError['description'])
			);
		}
		else
		{
			$Siteuser_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('provider_error')->value('Ошибка проверки подписи! Повторите авторизацию.')
			);
		}
	}

	// Подтверждение регистрации пользователем
	if (Core_Array::getGet('accept'))
	{
		$oSiteuser = Core_Entity::factory('Siteuser')->getByGuid(strval(Core_Array::getGet('accept')));

		if (!is_null($oSiteuser))
		{
			$oSiteuser->activate()->setCurrent();
			$Siteuser_Controller_Show->setEntity($oSiteuser);
		}
	}

	// Отмена регистрации пользователем
	if (Core_Array::getGet('cancel'))
	{
		$oSiteuser = Core_Entity::factory('Siteuser')->getByGuid(strval(Core_Array::getGet('cancel')));

		if (!is_null($oSiteuser))
		{
			// Отменяем авторизацию текущего пользователя
			$oSiteuser->delete()->unsetCurrent();

			// Set empty siteuser
			$Siteuser_Controller_Show->setEntity(Core_Entity::factory('Siteuser'));
		}
	}

	// Пользователь выходит из кабинета
	if (Core_Array::getGet('action') == 'exit')
	{
		// Отменяем авторизацию текущего пользователя
		$oSiteuser->unsetCurrent();

		// Set empty siteuser
		$Siteuser_Controller_Show->setEntity(Core_Entity::factory('Siteuser'));
	}

	Core_Page::instance()->object = $Siteuser_Controller_Show;
}