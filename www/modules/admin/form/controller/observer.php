<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

class Admin_Form_Controller_Observer
{
	public static function onBeforeAddEntity($controller, $args)
	{
		list($oAdmin_Form_Entity) = $args;

		switch ($controller->getAdminForm()->id)
		{
			case 24:  // Формы

				if (strpos(get_class($oAdmin_Form_Entity), 'Admin_Form_Entity_Menus') !== FALSE)
				{
					$oAdmin_Form_Entity->add(
						Admin_Form_Entity::factory('Menu')
							->name('Отчеты')
							->icon('fa fa-book')
							->href($controller->getAdminLoadHref('/admin/form/report/index.php', NULL, NULL, ""))
							->onclick($controller->getAdminLoadAjax('/admin/form/report/index.php', NULL, NULL, ""))
					);
				}

				break;
			case 65:  // Интернет-магазин, товары и группы

				if (strpos(get_class($oAdmin_Form_Entity), 'Admin_Form_Entity_Menus') !== FALSE)
				{
					$oAdmin_Form_Entity->add(
						Admin_Form_Entity::factory('Menu')
							->name('Отчеты по заявкам')
							->icon('fa fa-book')
							->href($controller->getAdminLoadHref('/admin/form/report/index.php', NULL, NULL, ""))
							->onclick($controller->getAdminLoadAjax('/admin/form/report/index.php', NULL, NULL, ""))
					);
				}

				break;
		}
	}
}