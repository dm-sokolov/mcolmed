<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Восстановление пароля пользователея
 *
 * Доступные методы:
 *
 * - contentType('text/plain') Content Type, по умолчанию 'text/plain'
 * - subject() тема письма
 * - from() адрес электронной почты отправителя, по умолчанию используется первый адрес, указанный для сайта
 *
 * <code>
 * $Siteuser_Controller_Restore_Password = new Siteuser_Controller_Restore_Password(
 * 	$oSiteuser
 * );
 *
 * $Siteuser_Controller_Restore_Password
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('ПисьмоВосстановлениеПароля')
 * 	)
 * 	->sendNewPassword();
 * </code>
 *
 * @package HostCMS
 * @subpackage Siteuser
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Siteuser_Controller_Restore_Password extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'contentType',
		'subject',
		'from',
	);

	/**
	 * Constructor.
	 * @param Siteuser_Model $oSiteuser user
	 */
	public function __construct(Siteuser_Model $oSiteuser)
	{
		parent::__construct($oSiteuser->clearEntities());
		$this->subject = 'Restore password';
		$this->contentType = 'text/plain';
		$this->from = $oSiteuser->Site->getFirstEmail();
	}

	/**
	 * Send new password to user
	 * @return self
	 * @hostcms-event Siteuser_Controller_Restore_Password.onBeforeSendNewPassword
	 */
	public function sendNewPassword()
	{
		$oSiteuser = $this->getEntity();

		$oSite = $oSiteuser->Site;

		// Create and save new password
		$new_password = Core_Password::get();
		$oSiteuser->password = Core_Hash::instance()->hash($new_password);
		$oSiteuser->save();

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('new_password')
				->value($new_password)
		)
		->addEntity(
			$oSite->clearEntities()->showXmlAlias()
		);

		Core_Event::notify(get_class($this) . '.onBeforeSendNewPassword', $this, array($new_password));

		$sXml = $this->getXml();

		$content = Xsl_Processor::instance()
			->xml($sXml)
			->xsl($this->_xsl)
			->process();

		$this->clearEntities();

		Core_Mail::instance()
			->to($oSiteuser->email)
			->from($this->from)
			->subject($this->subject)
			->message(trim($content))
			->contentType($this->contentType)
			->header('X-HostCMS-Reason', 'User-Restore-Password')
			->header('Precedence', 'bulk')
			->messageId()
			->send();

		return $this;
	}
}