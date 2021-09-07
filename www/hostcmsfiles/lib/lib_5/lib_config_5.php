<?php

// Page doesn't accept subpages, 404 error
$oCore_Page = Core_Page::instance();
if ($oCore_Page->structure->getPath() != Core::$url['path'])
{
        $oCore_Page->error404();
}

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
$title_ending = " в " .  $c_city_name . " - МЦ Олмед";
$description_ending = " в " .  $c_city_name . " МЦ Олмед ✔ Высококвалифицированные специалисты ✔ Комфортные условия ✔ Без очередей ✔ Запишитесь";
          $sTitle = $oCore_Page->structure->name;
          $sTitle = $sTitle . $title_ending;

          $sDescription = $oCore_Page->structure->name;
          $sDescription = $sDescription . $description_ending;


          $oCore_Page
               ->title($sTitle)
               ->description($sDescription);