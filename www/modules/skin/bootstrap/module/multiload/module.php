<?php


/**
* multiload observer
* 
* @author KAD Systems (©) 2015	
* @date 
*/

defined('HOSTCMS') || exit('HostCMS: access denied.');

class Skin_Bootstrap_Module_Multiload_Module extends Multiload_Module
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        Core_Event::attach('Skin_Bootstrap.onLoadSkinConfig', array('Skin_Bootstrap_Module_Multiload_Module', 'onLoadSkinConfig'));
    }

    static public function onLoadSkinConfig($object, $args)
    {
        // Load config
        $aConfig = $object->getConfig();

        // Add module into 'content' section, see config.php
        $aConfig['adminMenu']['content']['modules'][] = 'multiload';

        // Set new config
        $object->setConfig($aConfig);
    }
}