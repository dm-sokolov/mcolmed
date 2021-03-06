<?php
$id = Core_Array::getGet('id');
if (Core::moduleIsActive('advertisement') && $id)
{
	header('HTTP/1.0 301 Redirect');

	$oAdvertisement_Controller = Advertisement_Controller::instance()
		// Время хранения информации о показе
		->keep_days(3);

	$location = $oAdvertisement_Controller->getLocation($id);

	$advertisement_id = intval(Core_Array::getGet('banner_id'));

	if (!$location && $advertisement_id)
	{
		$oAdvertisement = Core_Entity::factory('Advertisement')->find($advertisement_id);

		// Баннер найден
		!is_null($oAdvertisement) && $location = $oAdvertisement->href;
	}

	if ($location)
	{
		?><script language="javascript">location.href='<?php echo $location?>'</script><?php
	}
	exit();
}