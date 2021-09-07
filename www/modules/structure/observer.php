<?php
class Structure_Observer
{
    static public function onBeforeGetXml($object, $args)
    {

        var_dump('123');

//        if ($object->id == 123 && Core::moduleIsActive('siteuser'))
//        {
//            $oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
//
//            if ($oSiteuser)
//            {
//                $object
//                    // Запрещаем вывод в XML стандартного значения тега name
//                    ->addForbiddenTag('name')
//                    // Добавляем свое значение
//                    ->addXmlTag('name', $object->name . ' ' . htmlspecialchars($oSiteuser->login));
//            }
//        }
    }
}

// Add structure observer
Core_Event::attach('structure.onBeforeGetXml', array('Structure_Observer', 'onBeforeGetXml'));