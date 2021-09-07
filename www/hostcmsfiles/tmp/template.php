<?php

/*
 * $aReplace содержит массив замен по схеме:
 * $aReplace['%имя_макроса%'] = 'значение для замены';
 */
$tmpDir = CMS_FOLDER . TMP_DIR;

$Install_Controller = Install_Controller::instance();
$Install_Controller->setTemplatePath($tmpDir);

$sCurrentDate = date('d.m.Y H:i:s');
$sql_current_date = date('Y-m-d H:i:s');

$sSiteName = Core_Array::get($aReplace, '%company_name%', 'undefined');
$sCompanyEmail = Core_Array::get($aReplace, '%company_email%', 'undefined@undefined.com');
// ------------------------------------------------------------

// Создаем сайт
$oSite = Core_Entity::factory('Site');
$oSite->name = "{$sSiteName} {$sCurrentDate}";
$oSite->admin_email = $sCompanyEmail;
$oSite->save();

// Меню
$oStructure_Menu = Core_Entity::factory('Structure_Menu');
$oStructure_Menu->name = 'Основное меню';
$oSite->add($oStructure_Menu);

$main_menu_id = $oStructure_Menu->id;

// Замена меню в макете
$aReplace['%main_menu%'] = $main_menu_id;

// Создаем макет
$oTemplate = Core_Entity::factory('Template');
$oTemplate->name = $sSiteName;
$oSite->add($oTemplate);

$oTemplate->saveTemplateFile($Install_Controller->loadFile($tmpDir . "tmp/template.htm", $aReplace));
$oTemplate->saveTemplateCssFile($Install_Controller->loadFile($tmpDir . "tmp/style0.css", $aReplace));
$template_id = $oTemplate->id;

// Создаем макет 1
$oTemplate = Core_Entity::factory('Template');
$oTemplate->template_id = $template_id;
$oTemplate->name = 'Макет для главной';
$oTemplate->sorting = 10;
$oSite->add($oTemplate);

$oTemplate->saveTemplateFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/templates/template10/template.htm", $aReplace));
$subtemplate_id1 = $oTemplate->id;

// Создаем макет 2
$oTemplate = Core_Entity::factory('Template');
$oTemplate->template_id = $template_id;
$oTemplate->name = 'Основной макет';
$oTemplate->sorting = 20;
$oSite->add($oTemplate);

$oTemplate->saveTemplateFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/templates/template11/template.htm", $aReplace));
$subtemplate_id2 = $oTemplate->id;

// Создаем макет версии для печати
$oTemplate = Core_Entity::factory('Template');
$oTemplate->template_id = 0;
$oTemplate->name = 'Версия для печати';
$oTemplate->sorting = 20;
$oSite->add($oTemplate);

$oTemplate->saveTemplateFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/templates/template12/template.htm", $aReplace));
$subtemplate_id3 = $oTemplate->id;

$oXsl_Dir = Core_Entity::factory('Xsl_Dir')->getByName('Сайт15', FALSE);
if (is_null($oXsl_Dir))
{
	$oXsl_Dir = Core_Entity::factory('Xsl_Dir');
	$oXsl_Dir->parent_id = 0;
	$oXsl_Dir->name = 'Сайт15';
	$oXsl_Dir->save();
}
$menu_xsl_dir_id = $oXsl_Dir->id;

if (is_null(Core_Entity::factory('Xsl')->getByName('ВерхнееМенюСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $menu_xsl_dir_id;
	$oXsl->name = 'ВерхнееМенюСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/222.xsl", $aReplace));
}

$oXsl_Dir = Core_Entity::factory('Xsl_Dir')->getByName('Сайт15', FALSE);
if (is_null($oXsl_Dir))
{
	$oXsl_Dir = Core_Entity::factory('Xsl_Dir');
	$oXsl_Dir->parent_id = 24;
	$oXsl_Dir->name = 'Сайт15';
	$oXsl_Dir->save();
}
$shop_xsl_dir_id = $oXsl_Dir->id;

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинКаталогиТоваровНаГлавнойСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинКаталогиТоваровНаГлавнойСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/223.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('УведомлениеДобавлениеКомментарияСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'УведомлениеДобавлениеКомментарияСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/243.xsl", $aReplace));
}

$oXsl_Dir = Core_Entity::factory('Xsl_Dir')->getByName('Сайт15', FALSE);
if (is_null($oXsl_Dir))
{
	$oXsl_Dir = Core_Entity::factory('Xsl_Dir');
	$oXsl_Dir->parent_id = 2;
	$oXsl_Dir->name = 'Сайт15';
	$oXsl_Dir->save();
}
$news_articles_xsl_dir_id = $oXsl_Dir->id;

if (is_null(Core_Entity::factory('Xsl')->getByName('СписокНовостейНаГлавнойСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $news_articles_xsl_dir_id;
	$oXsl->name = 'СписокНовостейНаГлавнойСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/224.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('НижнееМенюСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $menu_xsl_dir_id;
	$oXsl->name = 'НижнееМенюСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/225.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('ХлебныеКрошкиСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = 35;
	$oXsl->name = 'ХлебныеКрошкиСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/226.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('СписокНовостейСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $news_articles_xsl_dir_id;
	$oXsl->name = 'СписокНовостейСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/227.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинКаталогТоваровНаГлавнойСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинКаталогТоваровНаГлавнойСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/230.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинТоварСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинТоварСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/231.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинКаталогТоваровСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинКаталогТоваровСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/232.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинКорзинаКраткаяСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинКорзинаКраткаяСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/233.xsl", $aReplace));
}

$oXsl_Dir = Core_Entity::factory('Xsl_Dir')->getByName('Сайт15', FALSE);
if (is_null($oXsl_Dir))
{
	$oXsl_Dir = Core_Entity::factory('Xsl_Dir');
	$oXsl_Dir->parent_id = 11;
	$oXsl_Dir->name = 'Сайт15';
	$oXsl_Dir->save();
}
$forms_xsl_dir_id = $oXsl_Dir->id;

if (is_null(Core_Entity::factory('Xsl')->getByName('ОтобразитьФормуСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $forms_xsl_dir_id;
	$oXsl->name = 'ОтобразитьФормуСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/234.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('СписокСтатейНаГлавнойСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $news_articles_xsl_dir_id;
	$oXsl->name = 'СписокСтатейНаГлавнойСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/235.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинАдресДоставкиСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинАдресДоставкиСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/236.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинКорзинаСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинКорзинаСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/237.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинДоставкиСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинДоставкиСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/238.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинПлатежнаяСистемаСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинПлатежнаяСистемаСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/239.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('МагазинБыстраяРегистрацияСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $shop_xsl_dir_id;
	$oXsl->name = 'МагазинБыстраяРегистрацияСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/240.xsl", $aReplace));
}

$oXsl_Dir = Core_Entity::factory('Xsl_Dir')->getByName('Сайт15', FALSE);
if (is_null($oXsl_Dir))
{
	$oXsl_Dir = Core_Entity::factory('Xsl_Dir');
	$oXsl_Dir->parent_id = 12;
	$oXsl_Dir->name = 'Сайт15';
	$oXsl_Dir->save();
}
$site_users_xsl_dir_id = $oXsl_Dir->id;

if (is_null(Core_Entity::factory('Xsl')->getByName('РегистрацияПользователяСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $site_users_xsl_dir_id;
	$oXsl->name = 'РегистрацияПользователяСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/241.xsl", $aReplace));
}

if (is_null(Core_Entity::factory('Xsl')->getByName('ЛичныйКабинетПользователяСайт15', FALSE)))
{
	$oXsl = Core_Entity::factory('Xsl');
	$oXsl->xsl_dir_id = $site_users_xsl_dir_id;
	$oXsl->name = 'ЛичныйКабинетПользователяСайт15';
	$oXsl->save();
	$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/242.xsl", $aReplace));
}

// Документы
$oDocument = Core_Entity::factory('Document');
$oDocument->name = 'Спецпредложение';
$oSite->add($oDocument);

$documents_id_special_offer_index = $oDocument->id;

$oDocument_Version = Core_Entity::factory('Document_Version');
$oDocument_Version->current = 1;
$oDocument_Version->template_id = $subtemplate_id1;
$oDocument->add($oDocument_Version);
$oDocument_Version->saveFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/documents/documents37.html", $aReplace));

$aReplace['%document_special_offer%'] = $documents_id_special_offer_index;

$oDocument = Core_Entity::factory('Document');
$oDocument->name = 'Спецпредложение-подробно';
$oSite->add($oDocument);

$documents_id_special_offer = $oDocument->id;

$oDocument_Version = Core_Entity::factory('Document_Version');
$oDocument_Version->current = 1;
$oDocument_Version->template_id = $subtemplate_id2;
$oDocument->add($oDocument_Version);
$oDocument_Version->saveFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/documents/documents35.html", $aReplace));

$oDocument = Core_Entity::factory('Document');
$oDocument->name = 'Политика конфиденциальности';
$oSite->add($oDocument);

$documents_id_privacy_policy = $oDocument->id;

$oDocument_Version = Core_Entity::factory('Document_Version');
$oDocument_Version->current = 1;
$oDocument_Version->template_id = $subtemplate_id2;
$oDocument->add($oDocument_Version);
$oDocument_Version->saveFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/documents/documents33.html", $aReplace));

$oDocument = Core_Entity::factory('Document');
$oDocument->name = 'О магазине';
$oSite->add($oDocument);

$documents_id_about_store = $oDocument->id;

$oDocument_Version = Core_Entity::factory('Document_Version');
$oDocument_Version->current = 1;
$oDocument_Version->template_id = $subtemplate_id2;
$oDocument->add($oDocument_Version);
$oDocument_Version->saveFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/documents/documents32.html", $aReplace));

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id1;
$oStructure->name = 'Главная';
$oStructure->seo_title = $sSiteName;
$oStructure->path = '/';
$oStructure->sorting = 10;
$oStructure->type = 1; // Динамическая страницы
$oSite->add($oStructure);

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->document_id = $documents_id_about_store;
$oStructure->name = 'О магазине';
$oStructure->seo_title = $sSiteName;
$oStructure->path = 'about';
$oStructure->sorting = 20;
$oStructure->type = 0; // Статичная страницы
$oSite->add($oStructure);

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Каталог';
$oStructure->path = 'shop';
$oStructure->sorting = 30;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->lib_id = 6;
$oSite->add($oStructure);
$catalog_structure_id = $oStructure->id;

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->parent_id = $catalog_structure_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Корзина';
$oStructure->path = 'cart';
$oStructure->sorting = 30;
$oStructure->show = 0;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->lib_id = 7;
$oSite->add($oStructure);
$cart_structure_id = $oStructure->id;

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->parent_id = $cart_structure_id;
$oStructure->template_id = $subtemplate_id3;
$oStructure->name = 'Версия для печати';
$oStructure->path = 'print';
$oStructure->sorting = 10;
$oStructure->show = 0;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->lib_id = 8;
$oSite->add($oStructure);
$cart_print_structure_id = $oStructure->id;

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Новости';
$oStructure->path = 'news';
$oStructure->sorting = 40;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->lib_id = 1;
$oSite->add($oStructure);
$news_structure_id = $oStructure->id;

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Контакты';
$oStructure->path = 'contacts';
$oStructure->sorting = 50;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->lib_id = 18;
$oSite->add($oStructure);
$contacts_structure_id = $oStructure->id;

if (Core::moduleIsActive('form'))
{
	$oForm = Core_Entity::factory('Form');

	$oForm->name = 'Обратная связь';
	$oForm->email = $sCompanyEmail;
	$oForm->button_name = 'Submit';
	$oForm->button_value = 'Отправить';
	$oForm->email_subject = 'Обращение пользователя сайта';

	$oSite->add($oForm);
	$feedback_form_id = $oForm->id;

	// Создаем поля формы обратной связи
	$oForm_Field = Core_Entity::factory('Form_Field');

	$oForm_Field->name = 'fio';
	$oForm_Field->caption = 'ФИО';
	$oForm_Field->sorting = 10;
	$oForm_Field->size = 50;
	$oForm_Field->obligatory = 1;
	$oForm->add($oForm_Field);

	$oForm_Field = Core_Entity::factory('Form_Field');

	$oForm_Field->name = 'email';
	$oForm_Field->caption = 'E-mail';
	$oForm_Field->sorting = 20;
	$oForm_Field->size = 50;
	$oForm_Field->obligatory = 1;
	$oForm->add($oForm_Field);

	$oForm_Field = Core_Entity::factory('Form_Field');

	$oForm_Field->name = 'web-site';
	$oForm_Field->caption = 'Сайт';
	$oForm_Field->sorting = 30;
	$oForm_Field->size = 50;
	$oForm->add($oForm_Field);

	$oForm_Field = Core_Entity::factory('Form_Field');

	$oForm_Field->name = 'message';
	$oForm_Field->caption = 'Сообщение';
	$oForm_Field->type = 5;
	$oForm_Field->sorting = 40;
	$oForm_Field->rows = 5;
	$oForm_Field->cols = 40;
	$oForm_Field->obligatory = 1;
	$oForm->add($oForm_Field);
}
else
{
	$feedback_form_id = 0;
}
// Параметр LIB для узла структуры
$lib_id = 18;
$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_18/lib_values_91.dat"));
$values['formId'] = $feedback_form_id;

$oLib = Core_Entity::factory('Lib', $lib_id);
$oLib->saveDatFile($values, $contacts_structure_id);

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Поиск';
$oStructure->path = 'search';
$oStructure->sorting = 60;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->show = 0;
$oStructure->lib_id = 3;
$oSite->add($oStructure);
$search_structure_id = $oStructure->id;

// Параметр LIB для узла структуры
$lib_id = 3;
$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_3/lib_values_93.dat"));

$oLib = Core_Entity::factory('Lib', $lib_id);
$oLib->saveDatFile($values, $search_structure_id);

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Личный кабинет';
$oStructure->path = 'users';
$oStructure->sorting = 70;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->show = 0;
$oStructure->lib_id = 23;
$oSite->add($oStructure);
$users_structure_id = $oStructure->id;

// Параметр LIB для узла структуры
$lib_id = 23;
$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_23/lib_values_95.dat"));

$oLib = Core_Entity::factory('Lib', $lib_id);
$oLib->saveDatFile($values, $users_structure_id);

$oStructure = Core_Entity::factory('Structure');
$oStructure->parent_id = $users_structure_id;
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Регистрация';
$oStructure->path = 'registration';
$oStructure->sorting = 10;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->show = 0;
$oStructure->lib_id = 24;
$oSite->add($oStructure);
$users_registration_structure_id = $oStructure->id;

// Параметр LIB для узла структуры
$lib_id = 24;
$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_24/lib_values_98.dat"));

$oLib = Core_Entity::factory('Lib', $lib_id);
$oLib->saveDatFile($values, $users_registration_structure_id);

$oStructure = Core_Entity::factory('Structure');
$oStructure->parent_id = $users_structure_id;
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Восстановление пароля';
$oStructure->path = 'restore_password';
$oStructure->sorting = 20;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->show = 0;
$oStructure->lib_id = 26;
$oSite->add($oStructure);
$users_restore_password_structure_id = $oStructure->id;

// Параметр LIB для узла структуры
$lib_id = 26;
$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_26/lib_values_99.dat"));

$oLib = Core_Entity::factory('Lib', $lib_id);
$oLib->saveDatFile($values, $users_restore_password_structure_id);

$oStructure = Core_Entity::factory('Structure');
$oStructure->parent_id = $users_structure_id;
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Информация о пользователе';
$oStructure->path = 'info';
$oStructure->sorting = 30;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->show = 0;
$oStructure->lib_id = 28;
$oSite->add($oStructure);
$users_info_password_structure_id = $oStructure->id;

// Параметр LIB для узла структуры
$lib_id = 28;
$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_28/lib_values_100.dat"));

$oLib = Core_Entity::factory('Lib', $lib_id);
$oLib->saveDatFile($values, $users_info_password_structure_id);

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->document_id = $documents_id_privacy_policy;
$oStructure->name = 'Политика конфиденциальности';
$oStructure->seo_title = $sSiteName;
$oStructure->path = 'privacy_policy';
$oStructure->sorting = 80;
$oStructure->show = 0;
$oStructure->type = 0; // Статичная страницы
$oSite->add($oStructure);

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->template_id = $subtemplate_id2;
$oStructure->name = 'Статьи';
$oStructure->path = 'articles';
$oStructure->sorting = 60;
$oStructure->type = 2; // Типовая динамическая страницы
$oStructure->show = 0;
$oStructure->lib_id = 1;
$oSite->add($oStructure);
$articles_structure_id = $oStructure->id;

$oStructure = Core_Entity::factory('Structure');
$oStructure->structure_menu_id = $main_menu_id;
$oStructure->document_id = $documents_id_special_offer;
$oStructure->name = 'Спецпредложение';
$oStructure->seo_title = $sSiteName;
$oStructure->path = 'special_offer';
$oStructure->sorting = 110;
$oStructure->show = 0;
$oStructure->type = 0; // Статичная страницы
$oSite->add($oStructure);

// Информационные системы

// -- Новости --
$oInformationsystemNews = Core_Entity::factory('Informationsystem');
$oInformationsystemNews->name = 'Новости';
$oInformationsystemNews->structure_id = $news_structure_id;
$oInformationsystemNews->items_on_page = 5;
$oInformationsystemNews->items_sorting_field = 0;
$oInformationsystemNews->items_sorting_direction = 1;
$oInformationsystemNews->image_large_max_width = 500;
$oInformationsystemNews->image_large_max_height = 500;
$oInformationsystemNews->image_small_max_width = 100;
$oInformationsystemNews->image_small_max_height = 90;
$oInformationsystemNews->siteuser_group_id = 0;
$oInformationsystemNews->typograph_default_items = 1;
$oInformationsystemNews->typograph_default_groups = 1;
$oSite->add($oInformationsystemNews);

$is_news_id = $oInformationsystemNews->id;

// Параметр LIB для узла структуры
$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_1/lib_values_92.dat"));
$values['informationsystemId'] = $is_news_id;

$lib_id = 1;
$oLib = Core_Entity::factory('Lib', $lib_id);
$oLib->saveDatFile($values, $news_structure_id);

$aReplace['%is_news%'] = $is_news_id;

// Элемент (новость)
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
$oInformationsystem_Item->name = 'Перераспределение бюджета';
$oInformationsystem_Item->description = 'Представляется логичным, что рыночная информация последовательно ускоряет побочный PR-эффект, отвоевывая рыночный сегмент.';
$oInformationsystem_Item->text = '<p>Представляется логичным, что рыночная информация последовательно ускоряет побочный PR-эффект, отвоевывая рыночный сегмент. Привлечение аудитории не критично. Пресс-клиппинг отражает пресс-клиппинг, осознав маркетинг как часть производства. Бизнес-модель тормозит межличностный SWOT-анализ, используя опыт предыдущих кампаний. Ретроконверсия национального наследия стабилизирует конструктивный медийный канал, полагаясь на инсайдерскую информацию. Диктат потребителя, как принято считать, однородно искажает обществвенный выставочный стенд, полагаясь на инсайдерскую информацию.</p>
<p>Ценовая стратегия, анализируя результаты рекламной кампании, программирует продукт, учитывая современные тенденции. Агентская комиссия, пренебрегая деталями, методически программирует продвигаемый формат события, не считаясь с затратами. Такое понимание ситуации восходит к Эл Райс, при этом селекция бренда индуктивно отталкивает ребрендинг, работая над проектом. Не факт, что взаимодействие корпорации и клиента наиболее полно обуславливает опрос, полагаясь на инсайдерскую информацию.</p>
<p>К тому же анализ зарубежного опыта индуцирует популярный рекламный блок, не считаясь с затратами. Стратегия позиционирования выражена наиболее полно. Коммуникация, в рамках сегодняшних воззрений, директивно искажает тактический комплексный анализ ситуации, используя опыт предыдущих кампаний. Экспансия, отбрасывая подробности, откровенно цинична. Надо сказать, что потребительская база притягивает ролевой стратегический маркетинг, полагаясь на инсайдерскую информацию.</p>';

$oInformationsystemNews->add($oInformationsystem_Item);
$Install_Controller->moveInformationsystemItemImage($oInformationsystem_Item->id, $sourceInformationsystemId = 15, $sourceInformationsystemItemId = 63);

// Элемент (новость)
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
$oInformationsystem_Item->name = 'Межличностный конкурент';
$oInformationsystem_Item->description = 'Воздействие на потребителя, как следует из вышесказанного, экономит медиамикс, опираясь на опыт западных коллег.';
$oInformationsystem_Item->text = '<p>Воздействие на потребителя, как следует из вышесказанного, экономит медиамикс, опираясь на опыт западных коллег. Стоит отметить, что презентация транслирует медиаплан, оптимизируя бюджеты. Жизненный цикл продукции восстанавливает стиль менеджмента, оптимизируя бюджеты. Узнавание бренда, согласно Ф.Котлеру, специфицирует рейтинг, опираясь на опыт западных коллег. Анализ зарубежного опыта масштабирует продуктовый ассортимент, невзирая на действия конкурентов.</p>
<p>Можно предположить, что выставка откровенно цинична. Производство, как принято считать, настроено позитивно. Осведомленность о бренде стабилизирует план размещения, не считаясь с затратами. Партисипативное планирование существенно консолидирует инструмент маркетинга, расширяя долю рынка.</p>
<p>Партисипативное планирование, отбрасывая подробности, оправдывает бизнес-план, оптимизируя бюджеты. Согласно предыдущему, опросная анкета откровенна. Фокус-группа притягивает тактический product placement, не считаясь с затратами. Комплексный анализ ситуации, следовательно, интуитивно ускоряет conversion rate, осознав маркетинг как часть производства. Рекламная акция, отбрасывая подробности, искажает контент, расширяя долю рынка.</p>';

$oInformationsystemNews->add($oInformationsystem_Item);
$Install_Controller->moveInformationsystemItemImage($oInformationsystem_Item->id, $sourceInformationsystemId = 15, $sourceInformationsystemItemId = 64);

// Элемент (новость)
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
$oInformationsystem_Item->name = 'Ролевой фирменный стиль';
$oInformationsystem_Item->description = 'По мнению ведущих маркетологов, маркетингово-ориентированное издание стабилизирует конкурент, повышая конкуренцию.';
$oInformationsystem_Item->text = '<p>По мнению ведущих маркетологов, маркетингово-ориентированное издание стабилизирует конкурент, повышая конкуренцию. Узнавание бренда восстанавливает стратегический бизнес-план, повышая конкуренцию. Рекламоноситель, безусловно, обычно правомочен. Стратегия позиционирования, отбрасывая подробности, стремительно транслирует коллективный презентационный материал, не считаясь с затратами.</p>
<p>Объемная скидка спорадически ускоряет общественный целевой трафик, отвоевывая рыночный сегмент. Тем не менее, VIP-мероприятие поразительно. В рамках концепции Акоффа и Стэка, имидж предприятия конкурентоспособен. А вот по мнению аналитиков инвестиция транслирует опрос, невзирая на действия конкурентов. Изменение глобальной стратегии конструктивно. По сути, рейтинг специфицирует конкурент, невзирая на действия конкурентов.</p>
<p>Согласно последним исследованиям, медиамикс подсознательно охватывает социометрический системный анализ, не считаясь с затратами. Привлечение аудитории восстанавливает принцип восприятия, не считаясь с затратами. Conversion rate программирует повседневный формирование имиджа, повышая конкуренцию. Еще Траут показал, что стимулирование сбыта допускает анализ зарубежного опыта, отвоевывая рыночный сегмент. Стратегия предоставления скидок и бонусов по-прежнему востребована. Создание приверженного покупателя, безусловно, концентрирует рекламный клаттер, учитывая современные тенденции.</p>';

$oInformationsystemNews->add($oInformationsystem_Item);
$Install_Controller->moveInformationsystemItemImage($oInformationsystem_Item->id, $sourceInformationsystemId = 15, $sourceInformationsystemItemId = 65);

// Элемент (новость)
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
$oInformationsystem_Item->name = 'Конструктивный портрет потребителя';
$oInformationsystem_Item->description = 'Потребление одновременно изменяет целевой сегмент рынка, осознавая социальную ответственность бизнеса.';
$oInformationsystem_Item->text = '<p>Потребление одновременно изменяет целевой сегмент рынка, осознавая социальную ответственность бизнеса. Один из признанных классиков маркетинга Ф.Котлер определяет это так: лидерство в продажах тормозит эмпирический показ баннера, расширяя долю рынка. Информационная связь с потребителем без оглядки на авторитеты откровенна. Направленный маркетинг, не меняя концепции, изложенной выше, притягивает инвестиционный продукт, учитывая современные тенденции. Отсюда естественно следует, что партисипативное планирование реально консолидирует потребительский традиционный канал, отвоевывая свою долю рынка. Концепция новой стратегии инновационна.</p>
<p>По мнению ведущих маркетологов, продуктовый ассортимент повсеместно тормозит стратегический портрет потребителя, повышая конкуренцию. По сути, стиль менеджмента специфицирует ролевой стиль менеджмента, не считаясь с затратами. Медиамикс, в рамках сегодняшних воззрений, исключительно консолидирует общественный продукт, опираясь на опыт западных коллег. Целевая аудитория интегрирована.</p>
<p>Мониторинг активности, суммируя приведенные примеры, консолидирует эксклюзивный мониторинг активности, оптимизируя бюджеты. Емкость рынка неоднозначна. Целевой трафик повсеместно обуславливает BTL, осознав маркетинг как часть производства. Агентская комиссия тормозит конструктивный анализ зарубежного опыта, учитывая современные тенденции.</p>';

$oInformationsystemNews->add($oInformationsystem_Item);
$Install_Controller->moveInformationsystemItemImage($oInformationsystem_Item->id, $sourceInformationsystemId = 15, $sourceInformationsystemItemId = 66);

// Элемент (новость)
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
$oInformationsystem_Item->name = 'Типичный медиавес';
$oInformationsystem_Item->description = 'Маркетингово-ориентированное издание допускает опрос, осознавая социальную ответственность бизнеса.';
$oInformationsystem_Item->text = '<p>Маркетингово-ориентированное издание допускает опрос, осознавая социальную ответственность бизнеса. Эффективность действий, как следует из вышесказанного, традиционно притягивает баинг и селлинг, не считаясь с затратами. Практика однозначно показывает, что маркетингово-ориентированное издание программирует креативный контент, не считаясь с затратами. Маркетинговая активность обуславливает департамент маркетинга и продаж, невзирая на действия конкурентов.</p>
<p>Один из признанных классиков маркетинга Ф.Котлер определяет это так: формирование имиджа существенно специфицирует креативный формат события, отвоевывая свою долю рынка. Выставка, вопреки мнению П.Друкера, развивает стратегический бизнес-план, опираясь на опыт западных коллег. В общем, лидерство в продажах существенно индуцирует институциональный анализ зарубежного опыта, осознав маркетинг как часть производства. VIP-мероприятие, на первый взгляд, индуцирует рыночный формирование имиджа, полагаясь на инсайдерскую информацию. Повторный контакт довольно хорошо сбалансирован.</p>
<p>Презентация позитивно тормозит рейтинг, учитывая современные тенденции. Рекламное сообщество амбивалентно. До недавнего времени считалось, что медийная связь изоморфна времени. Баинг и селлинг усиливает медиаплан, повышая конкуренцию.</p>';

$oInformationsystemNews->add($oInformationsystem_Item);
$Install_Controller->moveInformationsystemItemImage($oInformationsystem_Item->id, $sourceInformationsystemId = 15, $sourceInformationsystemItemId = 67);

// -- Статьи --
$oInformationsystemArticles = Core_Entity::factory('Informationsystem');
$oInformationsystemArticles->name = 'Статьи';
$oInformationsystemArticles->structure_id = $articles_structure_id;
$oInformationsystemArticles->items_on_page = 3;
$oInformationsystemArticles->items_sorting_field = 0;
$oInformationsystemArticles->items_sorting_direction = 1;
$oInformationsystemArticles->image_large_max_width = 500;
$oInformationsystemArticles->image_large_max_height = 500;
$oInformationsystemArticles->image_small_max_width = 100;
$oInformationsystemArticles->image_small_max_height = 100;
$oInformationsystemArticles->siteuser_group_id = 0;
$oInformationsystemArticles->typograph_default_items = 1;
$oInformationsystemArticles->typograph_default_groups = 1;
$oSite->add($oInformationsystemArticles);

$is_articles_id = $oInformationsystemArticles->id;

// Параметр LIB для узла структуры
$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_1/lib_values_97.dat"));
$values['informationsystemId'] = $is_articles_id;

$lib_id = 1;
$oLib = Core_Entity::factory('Lib', $lib_id);
$oLib->saveDatFile($values, $articles_structure_id);

$aReplace['%is_articles%'] = $is_articles_id;

// Элемент (статья)
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
$oInformationsystem_Item->name = 'Продвигаемый анализ рыночных цен';
$oInformationsystem_Item->description = 'В общем, общество потребления последовательно охватывает рекламный бриф, не считаясь с затратами.';
$oInformationsystem_Item->text = '<p>В общем, общество потребления последовательно охватывает рекламный бриф, не считаясь с затратами. Тем не менее, баннерная реклама вырождена. Организация практического взаимодействия, не меняя концепции, изложенной выше, непосредственно специфицирует продвигаемый потребительский рынок, отвоевывая свою долю рынка. Еще Траут показал, что сервисная стратегия усиливает повторный контакт, оптимизируя бюджеты. Итак, ясно, что CTR программирует бизнес-план, отвоевывая рыночный сегмент. Лидерство в продажах пока плохо ускоряет конструктивный конкурент, не считаясь с затратами.</p>
<p>Селекция бренда специфицирует нишевый проект, признавая определенные рыночные тенденции. Рекламная поддержка, согласно Ф.Котлеру, спонтанно поддерживает рейтинг, признавая определенные рыночные тенденции. Продвижение проекта отражает типичный CTR, опираясь на опыт западных коллег. Рекламное сообщество нетривиально. Взаимодействие корпорации и клиента индуцирует выставочный стенд, используя опыт предыдущих кампаний. Воздействие на потребителя, отбрасывая подробности, усиливает департамент маркетинга и продаж, оптимизируя бюджеты.</p>
<p>Рекламное сообщество, как принято считать, искажает медиавес, размещаясь во всех медиа. Практика однозначно показывает, что ассортиментная политика предприятия притягивает комплексный принцип восприятия, оптимизируя бюджеты. Такое понимание ситуации восходит к Эл Райс, при этом личность топ менеджера неоднозначна. Согласно последним исследованиям, рейтинг притягивает стратегический нишевый проект, размещаясь во всех медиа.</p>';

$oInformationsystemArticles->add($oInformationsystem_Item);
$Install_Controller->moveInformationsystemItemImage($oInformationsystem_Item->id, $sourceInformationsystemId = 16, $sourceInformationsystemItemId = 68);

// Элемент (статья)
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
$oInformationsystem_Item->name = 'Эволюция мерчандайзинга';
$oInformationsystem_Item->description = 'Стратегия предоставления скидок и бонусов, как следует из вышесказанного, продуцирует эмпирический презентационный материал, размещаясь во всех медиа.';
$oInformationsystem_Item->text = 'Стратегия предоставления скидок и бонусов, как следует из вышесказанного, продуцирует эмпирический презентационный материал, размещаясь во всех медиа.';

$oInformationsystemArticles->add($oInformationsystem_Item);
$Install_Controller->moveInformationsystemItemImage($oInformationsystem_Item->id, $sourceInformationsystemId = 16, $sourceInformationsystemItemId = 69);

// Элемент (статья)
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
$oInformationsystem_Item->name = 'Потребительский диктат потребителя';
$oInformationsystem_Item->description = 'Тем не менее, рекламная поддержка существенно концентрирует из ряда вон выходящий план размещения, используя опыт предыдущих кампаний.';
$oInformationsystem_Item->text = '<p>Тем не менее, рекламная поддержка существенно концентрирует из ряда вон выходящий план размещения, используя опыт предыдущих кампаний. Реклама индуцирует общественный стратегический рыночный план, учитывая современные тенденции. Такое понимание ситуации восходит к Эл Райс, при этом медиапланирование категорически развивает сублимированный SWOT-анализ, учитывая современные тенденции. Концепция маркетинга, безусловно, тормозит фактор коммуникации, осознав маркетинг как часть производства.</p>
<p>Поисковая реклама, не меняя концепции, изложенной выше, отталкивает из ряда вон выходящий конкурент, размещаясь во всех медиа. Как предсказывают футурологи инвестиция стабилизирует медиабизнес, не считаясь с затратами. Еще Траут показал, что рейт-карта изоморфна времени. Наряду с этим, изменение глобальной стратегии порождает ролевой пул лояльных изданий, используя опыт предыдущих кампаний. В рамках концепции Акоффа и Стэка, особенность рекламы индуцирует общественный конкурент, отвоевывая свою долю рынка.</p>
<p>Поэтому психологическая среда пока плохо отталкивает креатив, используя опыт предыдущих кампаний. Стратегическое планирование, анализируя результаты рекламной кампании, поразительно. Product placement изменяет комплексный выставочный стенд, невзирая на действия конкурентов. Спонсорство, конечно, позитивно экономит связанный мониторинг активности, отвоевывая свою долю рынка. По сути, взаимодействие корпорации и клиента стабилизирует межличностный метод изучения рынка, расширяя долю рынка. Анализ зарубежного опыта, вопреки мнению П.Друкера, ригиден как никогда.</p>';

$oInformationsystemArticles->add($oInformationsystem_Item);
$Install_Controller->moveInformationsystemItemImage($oInformationsystem_Item->id, $sourceInformationsystemId = 16, $sourceInformationsystemItemId = 70);

// Интернет-магазин
if (Core::moduleIsActive('shop'))
{
	$oShop = Core_Entity::factory('Shop');

	$oShop->shop_company_id = 1;
	$oShop->name = 'Каталог';
	$oShop->image_small_max_width = 125;
	$oShop->image_large_max_width = 800;//Можно убрать, т.к. в _preloadValues задается такое же значение
	$oShop->image_small_max_height = 95;
	$oShop->image_large_max_height = 800;//Можно убрать, т.к. в _preloadValues задается такое же значение
	$oShop->structure_id = $catalog_structure_id;
	$oShop->shop_country_id = 175;
	$oShop->shop_currency_id = 1;
	$oShop->email = $sCompanyEmail;
	$oShop->items_on_page = 6;
	$oShop->change_filename = 1;
	$oShop->attach_digital_items = 1;
	$oShop->siteuser_group_id = 0;

	$oSite->add($oShop);

	$shop_id = $oShop->id;

	// Параметр LIB для узла структуры
	$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_6/lib_values_90.dat"));
	$values['shopId'] = $shop_id;

	$lib_id = 6;
	$oLib = Core_Entity::factory('Lib', $lib_id);
	$oLib = Core_Entity::factory('Lib', $lib_id);
	// Добавляем параметр типовой - XSL шаблон для уведомления пользователя о добавлении комментария
	$oLib_Property = Core_Entity::factory('Lib_Property');
	$oLib_Property->name = 'XSL шаблон для уведомления пользователя о добавлении комментария';
	$oLib_Property->varible_name = 'addCommentNoticeXsl';
	$oLib_Property->type = 2;
	$oLib_Property->sorting = 70;
	$oLib_Property->default_value = 'УведомлениеДобавлениеКомментарияСайт15';
	$oLib_Property->sql_caption_field = '';
	$oLib_Property->sql_value_field = '';

	$oLib->add($oLib_Property);

	$oLib->saveDatFile($values, $catalog_structure_id);

	// Параметр LIB для узла корзины
	$values = unserialize($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/lib/lib_7/lib_values_94.dat"));
	$values['shopId'] = $shop_id;

	$lib_id = 7;

	$oLib = Core_Entity::factory('Lib', $lib_id);
	// Добавляем параметр типовой - XSL шаблон Личного кабинета
	$oLib_Property = Core_Entity::factory('Lib_Property');
	$oLib_Property->name = 'XSL личного кабинета';
	$oLib_Property->varible_name = 'userAuthorizationXsl';
	$oLib_Property->type = 2;
	$oLib_Property->sorting = 90;
	$oLib_Property->default_value = 'ЛичныйКабинетПользователяСайт15';
	$oLib_Property->sql_caption_field = '';
	$oLib_Property->sql_value_field = '';

	$oLib->add($oLib_Property);

	// Добавляем параметр типовой - XSL шаблон регистрации пользователя
	$oLib_Property = Core_Entity::factory('Lib_Property');
	$oLib_Property->name = 'XSL регистрации пользователя';
	$oLib_Property->varible_name = 'userRegistrationXsl';
	$oLib_Property->type = 2;
	$oLib_Property->sorting = 100;
	$oLib_Property->default_value = 'РегистрацияПользователяСайт15';
	$oLib_Property->sql_caption_field = '';
	$oLib_Property->sql_value_field = '';

	$oLib->add($oLib_Property);

	$oLib->saveDatFile($values, $cart_structure_id);

	$aReplace['%shop%'] = $shop_id;

	// Дополнительные свойства товаров
	$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);
	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Диагональ (")';
	$oProperty->tag_name = 'diagonal';
	$oProperty->type = 1;
	$oProperty->sorting = 10;
	$oShop_Item_Property_List->add($oProperty);

	$diagonal_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Формат экрана';
	$oProperty->tag_name = 'screen_format';
	$oProperty->type = 1;
	$oProperty->sorting = 20;
	$oShop_Item_Property_List->add($oProperty);

	$screen_format_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Разрешение';
	$oProperty->tag_name = 'resolution';
	$oProperty->type = 1;
	$oProperty->sorting = 30;
	$oShop_Item_Property_List->add($oProperty);

	$resolution_format_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Поддержка телевизионных стандартов';
	$oProperty->tag_name = 'tv-standards';
	$oProperty->type = 1;
	$oProperty->sorting = 40;
	$oShop_Item_Property_List->add($oProperty);

	$tv_standards_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Мощность звука';
	$oProperty->tag_name = 'sound_power';
	$oProperty->type = 1;
	$oProperty->sorting = 50;
	$oShop_Item_Property_List->add($oProperty);

	$sound_power_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Гарантия (мес.)';
	$oProperty->tag_name = 'warranty';
	$oProperty->type = 1;
	$oProperty->sorting = 60;
	$oShop_Item_Property_List->add($oProperty);

	$warranty_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Поддержка Full HD';
	$oProperty->tag_name = 'supports_full_hd';
	$oProperty->type = 7;
	$oProperty->sorting = 70;
	$oShop_Item_Property_List->add($oProperty);

	$supports_full_hd_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Максимальное разрешение видео';
	$oProperty->tag_name = 'maximum_video_resolution';
	$oProperty->type = 1;
	$oProperty->sorting = 80;
	$oShop_Item_Property_List->add($oProperty);

	$maximum_video_resolution_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Максимальное разрешение фотосъемки';
	$oProperty->tag_name = 'maximum_resolution_photography';
	$oProperty->type = 1;
	$oProperty->sorting = 90;
	$oShop_Item_Property_List->add($oProperty);

	$maximum_resolution_photography_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Zoom оптический/цифровой';
	$oProperty->tag_name = 'zoom_optical_digital';
	$oProperty->type = 1;
	$oProperty->sorting = 100;
	$oShop_Item_Property_List->add($oProperty);

	$zoom_optical_digital_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Видоискатель';
	$oProperty->tag_name = 'viewfinder';
	$oProperty->type = 7;
	$oProperty->sorting = 110;
	$oShop_Item_Property_List->add($oProperty);

	$viewfinder_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Максимальное время работы от аккумулятора (ч)';
	$oProperty->tag_name = 'maximum_battery_life';
	$oProperty->type = 1;
	$oProperty->sorting = 120;
	$oShop_Item_Property_List->add($oProperty);

	$maximum_battery_life_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Количество пикселей (млн.)';
	$oProperty->tag_name = 'number_pixels';
	$oProperty->type = 1;
	$oProperty->sorting = 130;
	$oShop_Item_Property_List->add($oProperty);

	$number_pixels_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Светочувствительность ISO';
	$oProperty->tag_name = 'iso_sensitivity';
	$oProperty->type = 1;
	$oProperty->sorting = 140;
	$oShop_Item_Property_List->add($oProperty);

	$iso_sensitivity_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'ЖК экран (")';
	$oProperty->tag_name = 'lcd_screen';
	$oProperty->type = 1;
	$oProperty->sorting = 150;
	$oShop_Item_Property_List->add($oProperty);

	$lcd_screen_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Карты памяти';
	$oProperty->tag_name = 'memory_cards';
	$oProperty->type = 1;
	$oProperty->sorting = 160;
	$oShop_Item_Property_List->add($oProperty);

	$memory_cards_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Дисплей (")';
	$oProperty->tag_name = 'display';
	$oProperty->type = 1;
	$oProperty->sorting = 170;
	$oShop_Item_Property_List->add($oProperty);

	$display_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Процессор';
	$oProperty->tag_name = 'processor';
	$oProperty->type = 1;
	$oProperty->sorting = 180;
	$oShop_Item_Property_List->add($oProperty);

	$processor_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Частота процессора (МГц)';
	$oProperty->tag_name = 'cpu_frequency';
	$oProperty->type = 1;
	$oProperty->sorting = 190;
	$oShop_Item_Property_List->add($oProperty);

	$cpu_frequency_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Оперативная память (Мб)';
	$oProperty->tag_name = 'memory';
	$oProperty->type = 1;
	$oProperty->sorting = 200;
	$oShop_Item_Property_List->add($oProperty);

	$memory_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Жесткий диск (Гб)';
	$oProperty->tag_name = 'hard_drive';
	$oProperty->type = 1;
	$oProperty->sorting = 210;
	$oShop_Item_Property_List->add($oProperty);

	$hard_drive_property_id = $oProperty->id;

	$oProperty = Core_Entity::factory('Property');

	$oProperty->name = 'Графический чипсет';
	$oProperty->tag_name = 'graphics_chipset';
	$oProperty->type = 1;
	$oProperty->sorting = 220;
	$oShop_Item_Property_List->add($oProperty);

	$graphics_chipset_property_id = $oProperty->id;

	// Добавляем скидку
	$oShop_Discount = Core_Entity::factory('Shop_Discount');

	$date_time = date('U');

	$oShop_Discount->name = 'Осенняя скидка';
	$oShop_Discount->start_datetime = date('Y-m-d H:i:s', $date_time);
	$oShop_Discount->end_datetime = date('Y-m-d H:i:s', ($date_time+60*60*24*31));
	$oShop_Discount->percent = 15;

	$oShop->add($oShop_Discount);

	$shop_discount_id = $oShop_Discount->id;

	// Добавляем группу товаров
	$oShop_Group = Core_Entity::factory('Shop_Group');
	$oShop_Group->name = 'Видеокамеры';
	$oShop_Group->siteuser_group_id = -1;
	$oShop_Group->path = 'videcam';
	$oShop_Group->sorting = 10;
	$oShop->add($oShop_Group);

	$videocamers_group_id = $oShop_Group->id;

	$Install_Controller->moveShopGroupImage($videocamers_group_id, 3, 594);

	// Доступность свойств товаров для группы 'Видеокамеры'
	$param = array();

	// Гарантия
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $videocamers_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $warranty_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Поддержка Full HD
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $videocamers_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $supports_full_hd_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Максимальное разрешение видео
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $videocamers_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $maximum_video_resolution_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Максимальное разрешение фотосъемки
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $videocamers_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $maximum_resolution_photography_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Zoom оптический/цифровой
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $videocamers_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $zoom_optical_digital_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Видоискатель
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $videocamers_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $viewfinder_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Максимальное время работы от аккумулятора (ч)
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $videocamers_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $maximum_battery_life_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $videocamers_group_id;
	$oShop_Item->name = 'Samsung SMX-C20';
	$oShop_Item->marking = 'SMX-C20BP';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 5100;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	$Install_Controller->moveShopItemImage($shop_item_id, 3, 167);

	// Применяем скидку к товару
	$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
	$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
	$oShop_Item->add($oShop_Item_Discount);

	// Задаем значения доп. свойств товара

	// Значение доп. свойства "Гарантия"
	$oProperty = Core_Entity::factory('Property')->find($warranty_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('12');
	$oValue->save();

	// Значение доп. свойства "Поддержка Full HD"
	$oProperty = Core_Entity::factory('Property')->find($supports_full_hd_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1');
	$oValue->save();

	// Значение доп. свойства "Максимальное разрешение видео"
	$oProperty = Core_Entity::factory('Property')->find($maximum_video_resolution_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('720x576');
	$oValue->save();

	// Значение доп. свойства "Максимальное разрешение фотосъемки"
	$oProperty = Core_Entity::factory('Property')->find($maximum_resolution_photography_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1600x1200');
	$oValue->save();

	// Значение доп. свойства "Zoom оптический/цифровой"
	$oProperty = Core_Entity::factory('Property')->find($zoom_optical_digital_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('10x/1200x');
	$oValue->save();

	// Значение доп. свойства "Видоискатель"
	$oProperty = Core_Entity::factory('Property')->find($viewfinder_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue(0);
	$oValue->save();

	// Значение доп. свойства "Максимальное время работы от аккумулятора (ч)"
	$oProperty = Core_Entity::factory('Property')->find($maximum_battery_life_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('2.4');
	$oValue->save();

	// Добавляем группу товаров
	$oShop_Group = Core_Entity::factory('Shop_Group');
	$oShop_Group->name = 'Фотоаппараты';
	$oShop_Group->siteuser_group_id = -1;
	$oShop_Group->path = 'cameras';
	$oShop_Group->sorting = 20;
	$oShop->add($oShop_Group);

	$cameras_group_id = $oShop_Group->id;

	$Install_Controller->moveShopGroupImage($cameras_group_id, 3, 595);

	// Доступность свойств товаров для группы 'Фотоаппараты'

	// Гарантия
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $cameras_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $warranty_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Максимальное разрешение видео
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $cameras_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $maximum_video_resolution_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);


	// Максимальное разрешение фотосъемки
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $cameras_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $maximum_resolution_photography_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Количество пикселей (млн.)
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $cameras_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $number_pixels_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Светочувствительность ISO
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $cameras_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $iso_sensitivity_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// ЖК экран (")
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $cameras_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $lcd_screen_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Карты памяти
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $cameras_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $memory_cards_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $cameras_group_id;
	$oShop_Item->name = 'Canon PowerShot A480';
	$oShop_Item->text = '<strong>Простота – прежде всего</strong> <br />Фотоаппарат PowerShot A480 – воплощение удобства. Благодаря новому оптимизированному расположению кнопок, наличию отдельных кнопок масштабирования, переключения режимов и воспроизведения, а также усовершенствованному пользовательскому интерфейсу снимать этой камерой невероятно просто. Модель PowerShot A480 настолько компактна, что Вы можете брать её с собой куда угодно и делать превосходные фотографии, как только увидите <nobr>что-то</nobr> интересное. <br /> <br /><strong>Высокое разрешение и универсальный зум</strong> <br />Камера PowerShot A480 оснащена 10,0-мегапиксельным датчиком изображения, позволяющим снимать любые сцены с высокой детализацией и печатать фотографии даже на бумаге формата A3+. Кроме того, 3,3-кратный оптический зум позволяет быстро и гибко выстраивать кадр, что незаменимо при повседневной съёмке. <br /> <br /><strong>Технологии Canon: как делать отличные снимки, не прилагая усилий</strong> <br />Сердце PowerShot A480 – процессор обработки изображений DIGIC III, созданный Canon. DIGIC III – это исключительное качество изображения, сверхнизкий уровень шумов, превосходная цветопередача и малое время отклика, а также собственная технология распознавания лиц, разработанная компанией Canon. Благодаря технологии распознавания лиц создавать великолепные снимки людей стало проще, чем когда бы то ни было. Она обнаруживает до девяти лиц в кадре и выполняет соответствующую фокусировку, настройку экспозиции, вспышки и баланса белого, обеспечивая естественные тона кожи на фотографиях. <br />Технология распознавания движения, регистрирующая информацию об изменении сцены, сохраняет резкость изображения при движении объектов в кадре и при тряске камеры. Кроме того, оптимизируется чувствительность ISO, что позволяет свести к минимуму размытие изображения и получать снимки наивысшего качества. <br /> <br /><strong>Съёмка в любой ситуации</strong> <br />Набор из 15 предустановленных режимов позволяет пользователю любого уровня мгновенно выбрать настройки, подходящие для той или иной сцены. <br />В частности, имеются режимы «Дети и животные», «Закат» и «Помещение». Поддерживается запись видеороликов с частотой кадров 30 кадров/с, после чего их можно размещать в Интернете или демонстрировать на ярком ЖК-экране с диагональю 2,5 дюйма. PowerShot A480 работает от батареек типа AA, которые повсеместно есть в продаже, а значит, при необходимости Вы всегда сумеете быстро заменить элементы питания. <br /> <br />«И школьник, и пенсионер – человек любого возраста получит настоящее удовольствие от съёмки фотоаппаратом PowerShot A480, – говорит Могенс Йенсен (Mogens Jensen), глава европейского подразделения Canon Consumer Imaging. – Эта камера каждому даст возможность почувствовать себя настоящим фотографом».';
	$oShop_Item->marking = '3475B002';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 2270;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	// Копируем изображение товара
	$Install_Controller->moveShopItemImage($shop_item_id, 3, 164);

	// Применяем скидку к товару
	$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
	$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
	$oShop_Item->add($oShop_Item_Discount);

	// Задаем значения доп. свойств товара

	// Значение доп. свойства "Гарантия"
	$oProperty = Core_Entity::factory('Property')->find($warranty_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('24');
	$oValue->save();

	// Максимальное разрешение видео
	$oProperty = Core_Entity::factory('Property')->find($maximum_video_resolution_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('640x480');
	$oValue->save();

	// Максимальное разрешение фотосъемки
	$oProperty = Core_Entity::factory('Property')->find($maximum_resolution_photography_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('3648x2736');
	$oValue->save();

	// Количество пикселей (млн.)
	$oProperty = Core_Entity::factory('Property')->find($number_pixels_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('10');
	$oValue->save();

	// Светочувствительность ISO
	$oProperty = Core_Entity::factory('Property')->find($iso_sensitivity_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('80 - 1600');
	$oValue->save();

	// ЖК экран (")
	$oProperty = Core_Entity::factory('Property')->find($lcd_screen_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('2.5');
	$oValue->save();

	// Карты памяти
	$oProperty = Core_Entity::factory('Property')->find($memory_cards_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('SD, SDHC, MMCPlus, HC MMCPlus');
	$oValue->save();

	// Добавляем группу товаров
	$oShop_Group = Core_Entity::factory('Shop_Group');
	$oShop_Group->name = 'Телевизоры';
	$oShop_Group->siteuser_group_id = -1;
	$oShop_Group->path = 'lcd-tv';
	$oShop_Group->sorting = 30;
	$oShop->add($oShop_Group);

	$lcd_tv_group_id = $oShop_Group->id;

	$Install_Controller->moveShopGroupImage($lcd_tv_group_id, 3, 596);

	// Доступность свойств товаров для группы 'Телевизоры'
	// Диагональ (")
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $lcd_tv_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $diagonal_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Формат экрана
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $lcd_tv_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $screen_format_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Разрешение
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $lcd_tv_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $resolution_format_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Поддержка телевизионных стандартов
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $lcd_tv_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $tv_standards_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Мощность звука
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $lcd_tv_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $sound_power_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Гарантия (мес.)
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $lcd_tv_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $warranty_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Добавляем товары в группу

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $lcd_tv_group_id;
	$oShop_Item->name = 'Supra STV-LC1515W';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 5300;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	// Копируем изображение товара
	$Install_Controller->moveShopItemImage($shop_item_id, 3, 165);

	// Применяем скидку к товару
	$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
	$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
	$oShop_Item->add($oShop_Item_Discount);

	// Задаем значения доп. свойств товара

	// Значение доп. свойства "Диагональ"
	$oProperty = Core_Entity::factory('Property')->find($diagonal_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('15');
	$oValue->save();

	// Значение доп. свойства "Формат экрана"
	$oProperty = Core_Entity::factory('Property')->find($screen_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('16:9');
	$oValue->save();

	// Значение доп. свойства "Разрешение"
	$oProperty = Core_Entity::factory('Property')->find($resolution_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1366x768');
	$oValue->save();

	// Значение доп. свойства "Поддержка телевизионных стандартов"
	$oProperty = Core_Entity::factory('Property')->find($tv_standards_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('PAL, SECAM, NTSC');
	$oValue->save();

	// Значение доп. свойства "Мощность звука"
	$oProperty = Core_Entity::factory('Property')->find($sound_power_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('6 Вт (2x3 Вт)');
	$oValue->save();

	// Значение доп. свойства "Гарантия (мес.)"
	$oProperty = Core_Entity::factory('Property')->find($warranty_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('12');
	$oValue->save();

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $lcd_tv_group_id;
	$oShop_Item->name = 'Erisson 15LJ18';
	$oShop_Item->marking = 'UP-N155-JP01';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 5430;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	// Копируем изображение товара
	$Install_Controller->moveShopItemImage($shop_item_id, 3, 168);

	// Применяем скидку к товару
	$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
	$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
	$oShop_Item->add($oShop_Item_Discount);

	// Задаем значения доп. свойств товара

	// Значение доп. свойства "Диагональ"
	$oProperty = Core_Entity::factory('Property')->find($diagonal_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('15');
	$oValue->save();

	// Значение доп. свойства "Формат экрана"
	$oProperty = Core_Entity::factory('Property')->find($screen_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('16:9');
	$oValue->save();

	// Значение доп. свойства "Разрешение"
	$oProperty = Core_Entity::factory('Property')->find($resolution_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1024x768');
	$oValue->save();

	// Значение доп. свойства "Поддержка телевизионных стандартов"
	$oProperty = Core_Entity::factory('Property')->find($tv_standards_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('PAL, SECAM, NTSC');
	$oValue->save();

	// Значение доп. свойства "Мощность звука"
	$oProperty = Core_Entity::factory('Property')->find($sound_power_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('4 Вт (2х2 Вт)');
	$oValue->save();

	// Значение доп. свойства "Гарантия (мес.)"
	$oProperty = Core_Entity::factory('Property')->find($warranty_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('12');
	$oValue->save();

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $lcd_tv_group_id;
	$oShop_Item->name = 'Vestel 16850';
	$oShop_Item->marking = 'VE-N165-JF01';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 5500;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	// Копируем изображение товара
	$Install_Controller->moveShopItemImage($shop_item_id, 3, 169);

	// Применяем скидку к товару
	$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
	$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
	$oShop_Item->add($oShop_Item_Discount);

	// Задаем значения доп. свойств товара

	// Значение доп. свойства "Диагональ"
	$oProperty = Core_Entity::factory('Property')->find($diagonal_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('16');
	$oValue->save();

	// Значение доп. свойства "Формат экрана"
	$oProperty = Core_Entity::factory('Property')->find($screen_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('16:9');
	$oValue->save();

	// Значение доп. свойства "Разрешение"
	$oProperty = Core_Entity::factory('Property')->find($resolution_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1366x768');
	$oValue->save();

	// Значение доп. свойства "Поддержка телевизионных стандартов"
	$oProperty = Core_Entity::factory('Property')->find($tv_standards_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('PAL, SECAM, NTSC');
	$oValue->save();

	// Значение доп. свойства "Мощность звука"
	$oProperty = Core_Entity::factory('Property')->find($sound_power_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('5 Вт (2x2.5 Вт)');
	$oValue->save();

	// Значение доп. свойства "Гарантия (мес.)"
	$oProperty = Core_Entity::factory('Property')->find($warranty_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('24');
	$oValue->save();

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $lcd_tv_group_id;
	$oShop_Item->name = 'Mystery MTV-1907W';
	$oShop_Item->marking = 'ME-N190-HT05';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 7600;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	// Копируем изображение товара
	$Install_Controller->moveShopItemImage($shop_item_id, 3, 170);

	// Применяем скидку к товару
	$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
	$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
	$oShop_Item->add($oShop_Item_Discount);

	// Задаем значения доп. свойств товара
	// Значение доп. свойства "Диагональ"
	$oProperty = Core_Entity::factory('Property')->find($diagonal_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('19');
	$oValue->save();

	// Значение доп. свойства "Формат экрана"
	$oProperty = Core_Entity::factory('Property')->find($screen_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('16:9');
	$oValue->save();

	// Значение доп. свойства "Разрешение"
	$oProperty = Core_Entity::factory('Property')->find($resolution_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1366x768');
	$oValue->save();

	// Значение доп. свойства "Поддержка телевизионных стандартов"
	$oProperty = Core_Entity::factory('Property')->find($tv_standards_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('PAL, SECAM, NTSC');
	$oValue->save();

	// Значение доп. свойства "Мощность звука"
	$oProperty = Core_Entity::factory('Property')->find($sound_power_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('3 Вт (2х1.5 Вт)');
	$oValue->save();

	// Значение доп. свойства "Гарантия (мес.)"
	$oProperty = Core_Entity::factory('Property')->find($warranty_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('12');
	$oValue->save();

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $lcd_tv_group_id;
	$oShop_Item->name = 'Mystery MTV-2208W';
	$oShop_Item->marking = 'ME-N190-HT05';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 84530;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	// Копируем изображение товара
	$Install_Controller->moveShopItemImage($shop_item_id, 3, 171);

	// Задаем значения доп. свойств товара
	// Значение доп. свойства "Диагональ"
	$oProperty = Core_Entity::factory('Property')->find($diagonal_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('22');
	$oValue->save();

	// Значение доп. свойства "Формат экрана"
	$oProperty = Core_Entity::factory('Property')->find($screen_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('16:9');
	$oValue->save();

	// Значение доп. свойства "Разрешение"
	$oProperty = Core_Entity::factory('Property')->find($resolution_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1366x768');
	$oValue->save();

	// Значение доп. свойства "Поддержка телевизионных стандартов"
	$oProperty = Core_Entity::factory('Property')->find($tv_standards_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('PAL, SECAM, NTSC');
	$oValue->save();

	// Значение доп. свойства "Мощность звука"
	$oProperty = Core_Entity::factory('Property')->find($sound_power_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('6 Вт (2x3 Вт)');
	$oValue->save();

	// Значение доп. свойства "Гарантия (мес.)"
	$oProperty = Core_Entity::factory('Property')->find($warranty_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('12');
	$oValue->save();

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $lcd_tv_group_id;
	$oShop_Item->name = 'Acer AT2055';
	$oShop_Item->marking = 'ME-N190-HT05';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 8740;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	// Копируем изображение товара
	$Install_Controller->moveShopItemImage($shop_item_id, 3, 172);

	// Применяем скидку к товару
	$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
	$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
	$oShop_Item->add($oShop_Item_Discount);

	// Задаем значения доп. свойств товара
	// Значение доп. свойства "Диагональ"
	$oProperty = Core_Entity::factory('Property')->find($diagonal_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('20');
	$oValue->save();

	// Значение доп. свойства "Формат экрана"
	$oProperty = Core_Entity::factory('Property')->find($screen_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('16:9');
	$oValue->save();

	// Значение доп. свойства "Разрешение"
	$oProperty = Core_Entity::factory('Property')->find($resolution_format_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1600x900');
	$oValue->save();

	// Значение доп. свойства "Поддержка телевизионных стандартов"
	$oProperty = Core_Entity::factory('Property')->find($tv_standards_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('PAL, SECAM, NTSC');
	$oValue->save();

	// Значение доп. свойства "Мощность звука"
	$oProperty = Core_Entity::factory('Property')->find($sound_power_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('10 Вт (2х5 Вт)');
	$oValue->save();

	// Значение доп. свойства "Гарантия (мес.)"
	$oProperty = Core_Entity::factory('Property')->find($warranty_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('24');
	$oValue->save();

	// Ноутбуки
	$oShop_Group = Core_Entity::factory('Shop_Group');
	$oShop_Group->name = 'Ноутбуки';
	$oShop_Group->siteuser_group_id = -1;
	$oShop_Group->path = 'notebooks';
	$oShop_Group->sorting = 40;
	$oShop->add($oShop_Group);

	$notebook_group_id = $oShop_Group->id;

	$Install_Controller->moveShopGroupImage($notebook_group_id, 3, 597);

	// Доступность свойств товаров для группы 'Ноутбуки'

	// Дисплей (")
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $notebook_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $display_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Процессор
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $notebook_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $processor_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Частота процессора (МГц)
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $notebook_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $cpu_frequency_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Оперативная память (Мб)
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $notebook_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $memory_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Жесткий диск (Гб)
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $notebook_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $hard_drive_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Графический чипсет
	$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
	$oShop_Item_Property_For_Group->shop_group_id = $notebook_group_id;
	$oShop_Item_Property_For_Group->shop_item_property_id = Core_Entity::factory('Property', $graphics_chipset_property_id)->Shop_Item_Property->id;
	$oShop->add($oShop_Item_Property_For_Group);

	// Добавляем товар
	$oShop_Item = Core_Entity::factory('Shop_Item');
	$oShop_Item->shop_group_id = $notebook_group_id;
	$oShop_Item->name = 'Samsung N145';
	$oShop_Item->marking = 'ME-N190-HT05';
	$oShop_Item->shop_currency_id = 1;
	$oShop_Item->price = 10230;
	$oShop_Item->siteuser_group_id = -1;
	$oShop_Item->yandex_market = 0;
	$oShop_Item->rambler_pokupki = 0;
	$oShop->add($oShop_Item);

	$shop_item_id = $oShop_Item->id;

	// Копируем изображение товара
	$Install_Controller->moveShopItemImage($shop_item_id, 3, 166);

	// Применяем скидку к товару
	$oShop_Item_Discount = Core_Entity::factory('Shop_Item_Discount');
	$oShop_Item_Discount->shop_discount_id = $shop_discount_id;
	$oShop_Item->add($oShop_Item_Discount);

	// Задаем значения доп. свойств товара

	// Значение доп. свойства 'Дисплей (")'
	$oProperty = Core_Entity::factory('Property')->find($display_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('10.1');
	$oValue->save();

	// Значение доп. свойства 'Процессор'
	$oProperty = Core_Entity::factory('Property')->find($processor_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('Atom N450');
	$oValue->save();

	// Значение доп. свойства 'Частота процессора (МГц)'
	$oProperty = Core_Entity::factory('Property')->find($cpu_frequency_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1667');
	$oValue->save();

	// Значение доп. свойства "Оперативная память (Мб)"
	$oProperty = Core_Entity::factory('Property')->find($memory_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('1024');
	$oValue->save();

	// Значение доп. свойства "Жесткий диск (Гб)"
	$oProperty = Core_Entity::factory('Property')->find($hard_drive_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('160');
	$oValue->save();

	// Значение доп. свойства "Графический чипсет"
	$oProperty = Core_Entity::factory('Property')->find($graphics_chipset_property_id);

	$oValue = $oProperty->createNewValue($shop_item_id);
	$oValue->setValue('Intel GMA X3150');
	$oValue->save();

	// Телефоны
	$oShop_Group = Core_Entity::factory('Shop_Group');
	$oShop_Group->name = 'Телефоны';
	$oShop_Group->siteuser_group_id = -1;
	$oShop_Group->path = 'phones';
	$oShop_Group->sorting = 50;
	$oShop->add($oShop_Group);

	$phones_group_id = $oShop_Group->id;

	$Install_Controller->moveShopGroupImage($phones_group_id, 3, 598);

	// Принтеры
	$oShop_Group = Core_Entity::factory('Shop_Group');
	$oShop_Group->name = 'Принтеры';
	$oShop_Group->siteuser_group_id = -1;
	$oShop_Group->path = 'printers';
	$oShop_Group->sorting = 60;
	$oShop->add($oShop_Group);

	$printers_group_id = $oShop_Group->id;

	$Install_Controller->moveShopGroupImage($printers_group_id, 3, 599);

	// DVD-плееры
	$oShop_Group = Core_Entity::factory('Shop_Group');
	$oShop_Group->name = 'DVD-плееры';
	$oShop_Group->siteuser_group_id = -1;
	$oShop_Group->path = 'dvd';
	$oShop_Group->sorting = 70;
	$oShop->add($oShop_Group);

	$dvd_group_id = $oShop_Group->id;

	$Install_Controller->moveShopGroupImage($dvd_group_id, 3, 600);

	// MP3-плееры
	$oShop_Group = Core_Entity::factory('Shop_Group');
	$oShop_Group->name = 'MP3-плееры';
	$oShop_Group->siteuser_group_id = -1;
	$oShop_Group->path = 'mp3';
	$oShop_Group->sorting = 80;
	$oShop->add($oShop_Group);

	$mp3_group_id = $oShop_Group->id;

	$Install_Controller->moveShopGroupImage($mp3_group_id, 3, 601);
}

if (Core::moduleIsActive('siteuser'))
{
	// Добавляем пользователя
	$oSiteuser = Core_Entity::factory('Siteuser');
	$oSiteuser->login = 'tygra';
	$oSiteuser->password = Core_Hash::instance()->hash('tygra') ;
	$oSiteuser->email = 'tygra@tygrasite.ru';
	$oSiteuser->name = 'Тигра';
	$oSiteuser->surname = 'Тигров';
	$oSiteuser->patronymic = 'Тигрович';
	$oSiteuser->company = 'Компания тигры';
	$oSiteuser->phone = '555555555';
	$oSiteuser->fax = '7777777777';
	$oSiteuser->website = 'www.tygrasite.ru';
	$oSiteuser->icq = '12312323';
	$oSiteuser->country = 'Россия';
	$oSiteuser->postcode = '543123';
	$oSiteuser->city = 'Москва';
	$oSiteuser->active = 1;
	$oSite->add($oSiteuser);
}

// Заменяем макросы в макете
$Install_Controller->replaceFile(CMS_FOLDER . "templates/template{$template_id}/template.htm", $aReplace);
$Install_Controller->replaceFile(CMS_FOLDER . "templates/template{$subtemplate_id1}/template.htm", $aReplace);
$Install_Controller->replaceFile(CMS_FOLDER . "templates/template{$subtemplate_id2}/template.htm", $aReplace);
