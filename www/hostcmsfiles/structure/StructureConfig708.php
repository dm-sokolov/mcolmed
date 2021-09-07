<?php

if (Core_Array::getRequest('getForm') && Core_Array::getRequest('_', FALSE))
{
	$xslId = intval(Core_Array::getRequest('xsl'));
	$notificationMailXsl = 'ПисьмоКураторуФормыВФорматеHTML';
	$oForm = Core_Entity::factory('Form', Core_Array::getRequest('getForm', 0));
    $docname =Core_Array::getRequest('docname');
    $infoitem_id = Core_Array::getRequest('infosys-item-id');

	ob_start();

	$Form_Controller_Show = new Form_Controller_Show($oForm);

	if (!is_null(Core_Array::getRequest($oForm->button_name)))
	{
		$Form_Controller_Show
			->values($_REQUEST + $_FILES)
			->mailType(0) // 0 - html, 1- plain text
			->mailXsl(
				Core_Entity::factory('Xsl')->getByName($notificationMailXsl)
			)
			->mailFromFieldName('email')
			->process();

if ($infoitem_id) {
$oProperty = Core_Entity::factory('Property', 470);

if($oProperty) {
$aPropertyValues = $oProperty->getValues($infoitem_id);
$prev_value = $aPropertyValues[0]->value;
if ($prev_value > 3) {
 $aPropertyValues[0]->value = $prev_value - 1;
}
//echo $aPropertyValues[0]->value; 
$aPropertyValues[0]->save();
}
}

	}

	$Form_Controller_Show
		->xsl(Core_Entity::factory('Xsl', $xslId))
		->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('phone')
				->value(Core_Page::instance()->config['phone'])
		)
               ->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('docname')
				->value($docname)
		)
		->show();

	Core::showJson(array('html' => ob_get_clean()));
}

$oCore_Page = Core_Page::instance();
$oCore_Response = $oCore_Page->deleteChild()->response->status(404);
$oSite = Core_Entity::factory('Site', CURRENT_SITE);

$oStructure = Core_Entity::factory('Structure')->find($oSite->error404);

$oCore_Page = Core_Page::instance();

if ($oStructure->type == 0)
{
	$oDocument_Versions = $oStructure->Document->Document_Versions->getCurrent();

	if (!is_null($oDocument_Versions))
	{
		$oCore_Page->template($oDocument_Versions->Template);
	}
}

$oCore_Page->addChild($oStructure->getRelatedObjectByType());
$oStructure->setCorePageSeo($oCore_Page);

$oCore_Page->buildingPage && $oCore_Page->execute();