<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

class Informationsystem_Item_Observer
{
	static public function onBeforeRedeclaredGetXml($object)
	{
		$description = Core_Str::stripTags($object->description);

		if (mb_strlen($description) > 255)
		{
			$description = mb_substr($description, 0, 245) . '…' . mb_substr($description, -7);

			$object
				->addForbiddenTags(array('description'))
				->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('description')
						->value($description)
				)->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('description_real')
						->value($object->description)
				);
		}
	}
}