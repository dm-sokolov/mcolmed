<?php

$oInformationsystem = Core_Entity::factory('Informationsystem', Core_Array::get(Core_Page::instance()->libParams, 'informationsystemId'));

$Informationsystem_Controller_Show = new Informationsystem_Controller_Show($oInformationsystem);

if(Core_Array::getRequest('getForm') && Core_Array::getRequest('_', FALSE))
{
	$xslId = intval(Core_Array::getRequest('xsl'));

	ob_start();

	if (!is_null(Core_Array::getRequest('submit_question')))
	{
		$Informationsystem_Controller_Show->cache(FALSE);

		$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
		$oInformationsystem_Item->informationsystem_group_id = 0;
		$oInformationsystem_Item->active = Core_Page::instance()->libParams['addedItemActive'];
		$oInformationsystem_Item->indexing = 0;
		$oInformationsystem_Item->path = '';

		$subject = nl2br(strip_tags(Core_Array::getRequest('subject')));
		$text = strip_tags(Core_Array::getRequest('text'));
		$oInformationsystem_Item->name = strlen($subject) > 0 ? $subject : '<Без темы>';
		$oInformationsystem_Item->description = nl2br($text);

		$author = strip_tags(Core_Array::getRequest('author'));
		$email = strip_tags(Core_Array::getRequest('email'));

		$oInformationsystemItems = $oInformationsystem->Informationsystem_Items;
		$oInformationsystemItems->queryBuilder()
			->where('ip', '=', Core_Array::get($_SERVER, 'REMOTE_ADDR'))
			->orderBy('id', 'DESC')
			->limit(1);

		$aLastInformationsystemItem = $oInformationsystemItems->findAll();

		if (!isset($aLastInformationsystemItem[0]) || time() < Core_Date::sql2timestamp($oInformationsystem_Item->datetime) + ADD_COMMENT_DELAY)
		{
			$oInformationsystem->add($oInformationsystem_Item);

			// Вставляем в дополнительные свойства автора
			$oProperty = Core_Entity::factory('Property')->find(Core_Page::instance()->libParams['authorPropertyId']);

			if (!is_null($oProperty->id) && $author)
			{
				$oValue = $oProperty->createNewValue($oInformationsystem_Item->id);
				$oValue->value = $author;
				$oValue->save();
			}

			// Вставляем в дополнительные свойства email
			$oProperty = Core_Entity::factory('Property')->find(Core_Page::instance()->libParams['emailPropertyId']);

			if (!is_null($oProperty->id) && $email)
			{
				$oValue = $oProperty->createNewValue($oInformationsystem_Item->id);
				$oValue->value = $email;
				$oValue->save();
			}

			ob_start();
			if ($oInformationsystem_Item->active == 0)
			{
				?>
					<h2 class="h2 main-title mt-0">Благодарим Вас, <?php echo $author?>!</h2>
					<p> Ваш отзыв отправлен! </p>
				<?php
			}
			else
			{
				?>
					<p>Благодарим Вас, <?php echo $author?>!
					<br />Ваш отзыв отправлен и опубликована!
					</p>
				<?php
			}

			$Informationsystem_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('message')->value(ob_get_clean())
			);

			// Отправка письма администратору
			$message = "Доброе время суток, уважаемый Администратор!\n\nНа сайт www.mcolmed.ru был оставлен отзыв:\n";
			$message .= "Автор: " . $author . "\n";
			$message .= "E-mail: " . $email . "\n";
			$message .= "Дата: " . Core_Date::sql2datetime($oInformationsystem_Item->datetime) . "\n";
			$message .= "Отзыв: " . $oInformationsystem_Item->description;

			$aFrom = array_map('trim', explode(',', Core_Page::instance()->libParams['adminEmail']));

			foreach($aFrom as $key => $sEmail)
			{
				// Delay 0.350s for second mail and others
				$key > 0 && usleep(350000);

				$oCore_Mail = Core_Mail::instance()
					->clear()
					->to($sEmail)
					->from($aFrom[0])
					->header('Reply-To', Core_Valid::email($email)
						? $email
						: $aFrom[0]
					)
					->subject('Добавление отзыва на сайте www.mcolmed.ru')
					->message($message)
					->contentType('text/plain')
					->send();
			}
			
			/*$Informationsystem_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error')->value('Введен неверный код подтверждения!')
			)
			->addEntity(
				Core::factory('Core_Xml_Entity')->name('adding_item')
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('author')->value($author)
				)->addEntity(
					Core::factory('Core_Xml_Entity')->name('email')->value($email)
				)->addEntity(
					Core::factory('Core_Xml_Entity')->name('subject')->value($subject)
				)->addEntity(
					Core::factory('Core_Xml_Entity')->name('text')->value($text)
				)
			);*/

		}
		else
		{
			$Informationsystem_Controller_Show->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('error')->value('Запись не может быть добавлена, т.к. прошло мало времени с момента Вашего последнего добавления вопроса!')
				);
		}
	}

	$Informationsystem_Controller_Show
		->xsl(Core_Entity::factory('Xsl', $xslId))
		->groupsMode('none')
		->limit(0)
		->show();

	Core::showJson(array('html' => ob_get_clean()));
}

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

$aTitle = array($oInformationsystem->name);
$aDescription = array($oInformationsystem->name);
$aKeywords = array($oInformationsystem->name);

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
	
if ($Informationsystem_Controller_Show->group == 0 && !$Informationsystem_Controller_Show->item) {

               array_push($aTitle,  "в " .  $c_city_name . " - МЦ Олмед");

              array_push($aDescription,  "в " .  $c_city_name . " МЦ Олмед ✔ Высококвалифицированные специалисты ✔ Комфортные условия ✔ Без очередей ✔ Запишитесь");
}

if ($Informationsystem_Controller_Show->group)
{
	$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $Informationsystem_Controller_Show->group);
	
	/*do {*/
		$aTitle[] = $oInformationsystem_Group->seo_title != ''
			? $oInformationsystem_Group->seo_title
			: $oInformationsystem_Group->name;
			
		$aDescription[] = $oInformationsystem_Group->seo_description != ''
			? $oInformationsystem_Group->seo_description
			: $oInformationsystem_Group->name;
			
		$aKeywords[] = $oInformationsystem_Group->seo_keywords != ''
			? $oInformationsystem_Group->seo_keywords
			: $oInformationsystem_Group->name;	
		
	} /*while($oInformationsystem_Group = $oInformationsystem_Group->getParent());
}*/

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
}else{$links=1;} // Страница 1

Core_Page::instance()->title(implode(' ', $aTitle));
        Core_Page::instance()->description(implode(' ', $aDescription));
	Core_Page::instance()->keywords(implode(' ', $aKeywords));

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