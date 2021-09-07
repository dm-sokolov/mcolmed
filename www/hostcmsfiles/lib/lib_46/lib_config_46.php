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

$c_city_id = Core_Entity::factory ('Site', CURRENT_SITE)->id;
$c_city_name = "";
switch($c_city_id) {
  case 1: 
          $c_city_name = "Екатеринбурге";
          break;
 case 8: 
          $c_city_name = "Нижней Туре";
          break;
 case 7: 
          $c_city_name = "Североуральске";
          break;
 case 6: 
          $c_city_name = "Краснотурьинске";
          break;
 case 5: 
          $c_city_name = "Серове";
          break;
 case 4: 
          $c_city_name = "Нижнем Тагиле";
          break;
case 13: 
          $c_city_name = "Асбесте";
          break;
}

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

if ($Informationsystem_Controller_Show->group == 0 && !$Informationsystem_Controller_Show->item) {

$aTitle[] = $oInformationsystem->name;
               array_push($aTitle,  "в " .  $c_city_name . " - МЦ Олмед");
		$aDescription[] =  $oInformationsystem->name;
              array_push($aDescription,  "в " .  $c_city_name . " МЦ Олмед ✔ Высококвалифицированные специалисты ✔ Комфортные условия ✔ Без очередей ✔ Запишитесь");
}

if ($Informationsystem_Controller_Show->group && !$Informationsystem_Controller_Show->item)
{
	$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $Informationsystem_Controller_Show->group);

if ($oInformationsystem_Group->seo_title != '') {
$aTitle[] = $oInformationsystem_Group->seo_title;
} else {
	        array_push($aTitle,  $oInformationsystem->name);
		$aTitle[] = /*$oInformationsystem_Group->seo_title != ''
			? $oInformationsystem_Group->seo_title
			:*/ $oInformationsystem_Group->name;
                array_push($aTitle,  "в " .  $c_city_name . " - МЦ Олмед");
}
if ($oInformationsystem_Group->seo_description != '') {
$aDescription[] = $oInformationsystem_Group->seo_description;
} else {
	       array_push($aDescription,  $oInformationsystem->name);
               $aDescription[] = /*$oInformationsystem_Group->seo_description != ''
			? $oInformationsystem_Group->seo_description
			:*/ $oInformationsystem_Group->name;
               array_push($aDescription,  "в " .  $c_city_name . " МЦ Олмед ✔ Высококвалифицированные специалисты ✔ Комфортные условия ✔ Без очередей ✔ Запишитесь");
} 
		$aKeywords[] = $oInformationsystem_Group->seo_keywords != ''
			? $oInformationsystem_Group->seo_keywords
			: $oInformationsystem_Group->name;

	
}

if ($Informationsystem_Controller_Show->item)
{
        $oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $Informationsystem_Controller_Show->group);
	$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $Informationsystem_Controller_Show->item);
        if($oInformationsystem_Item->seo_title != '') {
           $aTitle[]  = $oInformationsystem_Item->seo_title;
       } else {
        array_push($aTitle,  $oInformationsystem->name);
        if($oInformationsystem_Group->id) {
        array_push($aTitle,  $oInformationsystem_Group->name);
        }
	$aTitle[] = /*$oInformationsystem_Item->seo_title != ''
		? $oInformationsystem_Item->seo_title
		:*/  $oInformationsystem_Item->name;
        array_push($aTitle,  "в " .  $c_city_name . " - МЦ Олмед");
}
if ($oInformationsystem_Item->seo_description != '') {
    $aDescription[] =  $oInformationsystem_Item->seo_description;
} else {
        array_push($aDescription,  $oInformationsystem->name);
         if($oInformationsystem_Group->id) {
        array_push($aDescription,  $oInformationsystem_Group->name);
        }
	$aDescription[] = /*$oInformationsystem_Item->seo_description != ''
		? $oInformationsystem_Item->seo_description
		:*/ $oInformationsystem_Item->name;
       array_push($aDescription,  "в " .  $c_city_name .  " МЦ Олмед ✔ Высококвалифицированные специалисты ✔ Комфортные условия ✔ Без очередей ✔ Запишитесь");
}
	$aKeywords[] = $oInformationsystem_Item->seo_keywords != ''
		? $oInformationsystem_Item->seo_keywords
		: $oInformationsystem_Item->name;
}

if ($Informationsystem_Controller_Show->page)
{
	array_unshift($aTitle, $pageName . ' ' . ($Informationsystem_Controller_Show->page + 1));
}
$links=0; // Переменная для номера текущей страницы в пагинации
if (count($aTitle))
{
 if ($Informationsystem_Controller_Show->page){ 
        $links=$Informationsystem_Controller_Show->page+1; // Номер текущей страницы в пагинации
        }

	//$aTitle = array_reverse($aTitle);
        //$aDescription = array_reverse($aDescription);
	//$aKeywords = array_reverse($aKeywords);
        //Core_Page::instance()->title(implode($pageSeparator, $aTitle));
        //Core_Page::instance()->description(implode($pageSeparator, $aDescription));
	//Core_Page::instance()->keywords(implode($pageSeparator, $aKeywords));
    
        
        Core_Page::instance()->title(implode(' ', $aTitle));
        Core_Page::instance()->description(implode(' ', $aDescription));
	Core_Page::instance()->keywords(implode(' ', $aKeywords));
       
}
else{$links=1;} // Страница 1

if ($links>0){ // Если находимся на странице с пагинацией
   $flag_canonic = 0;
    $pattern=$Informationsystem_Controller_Show->pattern; 
    $pattern=substr($pattern, 0, strpos($pattern, '({')); //Вытаскиваем URL для информационной системы 
    $linki='';
    if ($Informationsystem_Controller_Show->offset+$Informationsystem_Controller_Show->limit<$oInformationsystem->informationsystem_items->getCountByActive(1)){ 
    if($flag_canonic == 0){
$linki='<link rel="canonical" href="' . $pattern . '/" />';
$flag_canonic = 1;
}
      $linki.='<link rel="next" href="'.$pattern.'/page-'.($links+1).'/">'; //Если не последняя страница в пагинации - добавляем ссылку на следующую
    }
    
    if ($links>2){ $linki.=' <link rel="prev" href="'.$pattern.'/page-'.($links-1).'/">'; 
 if($flag_canonic == 0){
$linki='<link rel="canonical" href="' . $pattern . '/" />';
$flag_canonic = 1;
}
} //Добавляем ссылку на предыдущую страницу
       elseif ($links>1){ $linki.=' <link rel="prev" href="'.$pattern.'">'; 
if($flag_canonic == 0){
$linki.='<link rel="canonical" href="' . $pattern . '/" />';
$flag_canonic = 1;
}
} //Если текущая страница 2 - то в качестве предыдущей выводим главную страницу ИС
    
    Core_Registry::instance()->set('linki', $linki); //Передаем значение в шаблон
    }

Core_Page::instance()->object = $Informationsystem_Controller_Show;