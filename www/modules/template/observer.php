<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

class Template_Observer
{
	static public function onBeforeShowAction($controller)
	{
		$config = Core::$config->get('site_config');

		Core_Page::instance()->addAllowedProperty('config');

		Core_Page::instance()
			->config($config[CURRENT_SITE]);
	}

	static public function onBeforeSetTemplate($controller)
	{
		$libParams = Core_Page::instance()->libParams;

		if(CURRENT_SITE > 1)
		{
			switch (CURRENT_STRUCTURE_ID)
			{
				case 158: // Услуги
				case 195: // Услуги
				case 291: // Услуги
				case 345: // Услуги

					$template_id = 98; // Основной [rightsidebar]

					$libParams['informationsystemXsl'] = 'СписокУслугИнфосистемыNEW';
					$libParams['informationsystemItemXsl'] = 'ЕдиницыУслугиNEW';

					break;
				case 150: // Новости
				case 187: // Новости
				case 317: // Новости
				case 371: // Новости

					$template_id = 98; // Основной [rightsidebar]

					$libParams['informationsystemXsl'] = 'СписокЭлементовИнфосистемыNEW';
					$libParams['informationsystemItemXsl'] = 'ВыводЕдиницыИнформационнойСистемыNEW';

					break;
				case 250: // СМИ о нас
				case 267: // СМИ о нас
				case 304: // СМИ о нас
				case 358: // СМИ о нас

					$template_id = 98; // Основной [rightsidebar]

					$libParams['informationsystemXsl'] = 'СписокВидеоИнфосистемыNEW';

					break;
				case 154: // Контакты
				case 191: // Контакты
				case 287: // Контакты
				case 341: // Контакты

					$template_id = 99; // Контакты

					break;
				case 159: // Наши врачи
				case 196: // Наши врачи
				case 292: // Наши врачи
				case 346: // Наши врачи

					$template_id = 98; // Основной [rightsidebar]

					$libParams['informationsystemXsl'] = 'СписокСотрудниковNEW';
					$libParams['informationsystemItemXsl'] = 'ЕдиницаСотрудникаNEW';

					break;
				case 160: // Прайс-лист
				case 197: // Прайс-лист
				case 293: // Прайс-лист
				case 347: // Прайс-лист

					$template_id = 98; // Основной [rightsidebar]

					$libParams['informationsystemXsl'] = 'СписокПрайсNEW';

					break;
				case 242: // Полезная информация
				case 229: // Полезная информация
				case 305: // Полезная информация
				case 359: // Полезная информация

					$template_id = 98; // Основной [rightsidebar]

					$libParams['informationsystemXsl'] = 'СписокЭлементовИнфосистемыNEW';

					break;
				case 172: // Ошибка 404
				case 209: // Ошибка 404
				case 173: // Карта сайта
				case 210: // Карта сайта
				case 178: // О компании
				case 215: // О компании
				case 325: // О компании
				case 379: // О компании
				case 241: // Подарочный сертификат
				case 238: // Подарочный сертификат
				case 240: // Страховые компании
				case 179: // Поиск
				case 216: // Поиск
				case 166: // Пациентам
				case 203: // Пациентам
				case 246: // Подготовка к УЗИ
				case 243: // Мифы и реальности флебологии
				case 391: // Расписание врачей
				case 392: // Расписание врачей
				case 400: // Политика обработки персональных данных
				case 401: // Политика обработки персональных данных

					$template_id = 98; // Основной [rightsidebar]

					break;
				case 273: // Фотогалерея

					$template_id = 98; // Основной [rightsidebar]

					$libParams['informationsystemXsl'] = 'СписокКартинокNEW';

					break;
				default:

					$template_id = 97; // Для главной страницы

					break;
			}

			$oTemplate = Core_Entity::factory('Template', $template_id);

			Core_Page::instance()
				->libParams($libParams)
				->template($oTemplate);
		}
	}
}