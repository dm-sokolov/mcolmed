<?php

$oInformationsystem = Core_Entity::factory('Informationsystem', Core_Array::get(Core_Page::instance()->libParams, 'informationsystemId'));

$Informationsystem_Controller_Show = new Informationsystem_Controller_Show($oInformationsystem);

$Informationsystem_Controller_Show
	->limit($oInformationsystem->items_on_page)
	->parseUrl();

// Текстовая информация для указания номера страницы, например "страница"
$pageName = Core_Array::get(Core_Page::instance()->libParams, 'page')
	? Core_Array::get(Core_Page::instance()->libParams, 'page')
	: 'страница';

// Разделитель в заголовке страницы
$pageSeparator = Core_Array::get(Core_Page::instance()->libParams, 'separator')
	? Core_Page::instance()->libParams['separator']
	: ' / ';

$aTitle = array();
$aDescription = array();
$aKeywords = array();

if (!is_null($Informationsystem_Controller_Show->tag) && Core::moduleIsActive('tag'))
{
	$oTag = Core_Entity::factory('Tag')->getByPath($Informationsystem_Controller_Show->tag);
	if ($oTag)
	{
		$aTitle[] = Core::_('Informationsystem.tag', $oTag->name);
		$aDescription[] = Core::_('Informationsystem.tag', $oTag->name);
		$aKeywords[] = Core::_('Informationsystem.tag', $oTag->name);
	}
}

if ($Informationsystem_Controller_Show->group && !$Informationsystem_Controller_Show->item)
{
	$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $Informationsystem_Controller_Show->group);

	
		$aTitle[] = $oInformationsystem_Group->seo_title != ''
			? $oInformationsystem_Group->seo_title
			: $oInformationsystem_Group->name;

		$aDescription[] = $oInformationsystem_Group->seo_description != ''
			? $oInformationsystem_Group->seo_description
			: $oInformationsystem_Group->name;

		$aKeywords[] = $oInformationsystem_Group->seo_keywords != ''
			? $oInformationsystem_Group->seo_keywords
			: $oInformationsystem_Group->name;

	
}

if ($Informationsystem_Controller_Show->item)
{
	$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $Informationsystem_Controller_Show->item);

	$aTitle[] = $oInformationsystem_Item->seo_title != ''
		? $oInformationsystem_Item->seo_title
		: $oInformationsystem_Item->name;

	$aDescription[] = $oInformationsystem_Item->seo_description != ''
		? $oInformationsystem_Item->seo_description
		: $oInformationsystem_Item->name;

	$aKeywords[] = $oInformationsystem_Item->seo_keywords != ''
		? $oInformationsystem_Item->seo_keywords
		: $oInformationsystem_Item->name;
}
if ($Informationsystem_Controller_Show->page)
{
	array_unshift($aTitle, $pageName . ' ' . ($Informationsystem_Controller_Show->page + 1));
}
if (count($aTitle))
{
	$aTitle = array_reverse($aTitle);
	$aDescription = array_reverse($aDescription);
	$aKeywords = array_reverse($aKeywords);

	Core_Page::instance()->title(implode($pageSeparator, $aTitle));
	Core_Page::instance()->description(implode($pageSeparator, $aDescription));
	Core_Page::instance()->keywords(implode($pageSeparator, $aKeywords));
}

$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $Informationsystem_Controller_Show->group);

if ( ($Informationsystem_Controller_Show->limit<$oInformationsystem_Group->informationsystem_items->getCountByActive(1)) ) {

if($Informationsystem_Controller_Show->group && !$Informationsystem_Controller_Show->item) {

    $links=$Informationsystem_Controller_Show->page +1; // Номер текущей страницы в пагинации
    $linki = ''; //Теги, которые пойдут в шаблон
    //Всего страниц
    $pagecnt = ceil(($oInformationsystem_Group->informationsystem_items->getCountByActive(1)) / ($Informationsystem_Controller_Show->limit));  
   
   
    $pattern = $oInformationsystem->Structure->getPath() . $oInformationsystem_Group->getPath(); //URL для группы информационной системы 
  $linki.='<link rel="canonical" href="' . $pattern . '" />';
   if ($links<$pagecnt){ 
     
       if($links != 1 && $links != 2) {
          $linki.=' <link rel="prev" href="'.$pattern.'page-'.($links-1).'/" />'; 
        } 
       if($links == 2) {
         $linki.='<link rel="prev" href="'.$pattern.'" />'; 
        }
     $linki.='<link rel="next" href="'.$pattern.'page-'.($links+1).'/" />'; 
    
  }
 else {
    if($links == 2) {
         $linki.='<link rel="prev" href="'.$pattern.'" />'; 
    }
   else {
    $linki.='<link rel="prev" href="'.$pattern.'page-'.($links-1).'/" />'; 
   }
  }
    
    Core_Registry::instance()->set('linki', $linki); //Передаем значение в шаблон
}    
}
Core_Page::instance()->object = $Informationsystem_Controller_Show;