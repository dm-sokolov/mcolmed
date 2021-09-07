<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Counters.
 *
 * @package HostCMS
 * @subpackage Counter
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк"(Hostmake LLC), http://www.hostcms.ru
 */
class Counter_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'site',
		'referrer',
		'page',
		'cookies',
		'java',
		'colorDepth',
		'display',
		'js',
		'counterId',
		'updateCounter',
		'ip',
		'userAgent',
		'bBot',
		'bNewUser',
		'bNewSession',
		'sessionId',
		'siteuserId',
		'cleaningFrequency',
		'sessionLifeTime',
	);

	/**
	 * Config value
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->updateCounter = 1;
		$this->siteuserId = 0;
		$this->cleaningFrequency = defined('STAT_CLEARING_FREQUENCY') ? STAT_CLEARING_FREQUENCY : 1000;
		// Create new sessionId
		$this->bNewSession = FALSE;

		// Время, в течении которого сессия считается активной с момента последнего посещения
		$this->sessionLifeTime = 3600;

		$this->_config = Core::$config->get('counter_config') + array(
			'gethostbyaddr' => FALSE,
			'counters' => array(
				0 => array(
					'color_red' => 0xff,
					'color_green' => 0xff,
					'color_blue' => 0xff,
					'show' => 0,
					'name' => 'Invisible',
					'image_name' => '0.gif',
					'x' => 1,
					'y' => 1
				)
			)
		);
	}

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	* Отображение кода счетчика
	*
	* @param int $counterId тип выводимого счетчика (файл счетчика должен быть расположен в директории /counter/ и представлять собой изображение с именем {НОМЕР}.gif)
	* @param string $alias_name наименование домена сайта, например www.site.ru
	* @param string $get_update_counter переменная с информацией о необходимости обновления счетчика, не обязательный параметр, по умолчанию равен пустоте
	* @return Counter_Controller
	*
	* <code>
	* <?php
	* ob_start();
	* Counter_Controller::instance()->showCounterCode(1, Core_Entity::factory('Site', CURRENT_SITE)->getCurrentAlias()->name);
	* $sCode = ob_get_clean();
	* ?>
	* </code>
	*/
	public function showCounterCode($counterId, $alias_name, $get_update_counter = '')
	{
		if (isset($this->_config['counters'][$counterId]) && is_array($this->_config['counters'][$counterId]))
		{
			?><!--HostCMS counter--><a href="https://www.hostcms.ru" target="_blank"><img id="hcntr<?php echo $counterId?>" width="<?php echo $this->_config['counters'][$counterId]['x']?>" height="<?php echo $this->_config['counters'][$counterId]['y']?>" style="border:0" title="HostCMS Counter" alt="" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"/></a><?php
			Core::factory('Core_Html_Entity_Script')
				->value('(function(h,c,m,s){h.cookie="_hc_check=1; path=/";h.getElementById("hcntr' . $counterId .'").src="//' . $alias_name . '/counter/counter.php?r="+Math.random()+"&id=' . CURRENT_SITE . '&refer="+escape(h.referrer)+"&current_page="+escape(s.location.href)+"&cookie="+(h.cookie?"Y":"N")+"&java="+(m.javaEnabled()?"Y":"N")+"&screen="+c.width+\'x\'+c.height+"&counter=' . $counterId . $get_update_counter . '"})(document,screen,navigator,window)')
				->execute();

			Core::factory('Core_Html_Entity_Noscript')
				->add(
					Core::factory('Core_Html_Entity_A')
					->href('https://www.hostcms.ru')
					->add(Core::factory('Core_Html_Entity_Img')
						->src("//{$alias_name}/counter/counter.php?id=" . CURRENT_SITE . "&counter={$counterId}{$get_update_counter}")
						->alt('HostCMS Counter')
						->width($this->_config['counters'][$counterId]['x'])
						->height($this->_config['counters'][$counterId]['y']))
				)
				->execute();
			?><!--/HostCMS--><?php
		}

		return $this;
	}

	static public function getPrimaryKeyByDate($date)
	{
		if ($date != '0000-00-00')
		{
			$timestamp = Core_Date::sql2timestamp($date);
			return date('Y', $timestamp) * 1000 + date('m', $timestamp) * 50 + date('d', $timestamp);
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Построение счетчика
	 * @return Counter_Controller
	 * @hostcms-event Counter_Controller.onAfterUpdateCounter
	 */
	public function buildCounter()
	{
		Core_Session::close();

		$site_id = intval($this->site->id);

		// Текущая дата (год, месяц, день)
		$sCurrentDate = date("Y-m-d");

		// Проверка бот или не бот
		$this->bBot = self::checkBot($this->userAgent);

		// Проверяем, учитывать ли заходы с данного IP адреса
		$oIpaddress = Core::moduleIsActive('ipaddress')
			? Core_Entity::factory('Ipaddress')->getByIp($this->ip)
			: NULL;

		// Если стоит флаг Обновлять данные счетчика, обрабатываем данные, заносим их БД
		if ($this->updateCounter == 1 && (is_null($oIpaddress) || !$oIpaddress->no_statistic))
		{
			// Проверяем является ли данный ip уникальным для обновления данных счетчика
			$bNewIp = $this->bBot
				? FALSE
				: $this->ipIsNew(Core_Str::ip2hex($this->ip), $sCurrentDate, $site_id);

			// Проверяем наличие записи о текущей сессии в куках пользователя
			$iSessionId = intval(Core_Array::get($_COOKIE, '_hc_session'));
			if ($iSessionId)
			{
				$oCounter_Session = Core_Entity::factory('Counter_Session')->find($iSessionId);

				// Передана сессия и с момента последней активности не прошло $this->sessionLifeTime
				if (!is_null($oCounter_Session->id)
					&& Core_Date::sql2timestamp($oCounter_Session->last_active) > time() - $this->sessionLifeTime)
				{
					// есть текущая сессия
					$this->sessionId = $iSessionId;

					$oCounter_Session->last_active = Core_Date::timestamp2sql(time());
					$oCounter_Session->save();
				}
			}

			// Вставка новой сессии
			!$this->sessionId
				&& $this->sessionId = $this->insertSession();

			// Обновляем время сесии в куках
			setcookie('_hc_session', $this->sessionId, time() + $this->sessionLifeTime, '/');

			// Проверяем наличие id-пользователя в куках для определения нового пользователя
			// Учитываются данные за 7 дней
			if (Core_Array::get($_COOKIE, '_hc_nu'))
			{
				$this->bNewUser = FALSE;
			}
			else
			{
				// Проверяем является ли ip-адрес пользователя уникальным за текущий день
				// ip-адрес уникальный - пользователь новый
				$this->bNewUser = $bNewIp;
			}

			// 7-дневная метка нового пользователя
			/*!$this->bBot && */setcookie('_hc_nu', 1, time() + 604800, '/');

			if (Core::moduleIsActive('siteuser'))
			{
				$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			 	$this->siteuserId = !is_null($oSiteuser) ? $oSiteuser->id : 0;
			}

			// Определение поисковик это или нет, получение поискового запроса, поисковой системы
			$aSearchSystem = self::isSearchSystem($this->referrer);
			if (is_array($aSearchSystem) && count($aSearchSystem) > 0)
			{
				// Поисковая система
				$searchsystem = $aSearchSystem['search_system'];

				// Поисковая фраза
				$searchquery = $aSearchSystem['search_query'];
			}
			else
			{
				$searchquery = $searchsystem = NULL;
			}

			// Удалять Emoji
			$bRemoveEmoji = strtolower(Core_Array::get(Core_DataBase::instance()->getConfig(), 'charset')) != 'utf8mb4';

			$oCounter_Visit = Core_Entity::factory('Counter_Visit');
			$oCounter_Visit->site_id = $this->site->id;
			$oCounter_Visit->counter_session_id = $this->sessionId;

			if ($this->referrer == '')
			{
				$oCounter_Visit->counter_referrer_id = 0;
			}
			else
			{
				$inner = 0;

				$aSite_Aliases = $this->site->Site_Aliases->findAll();
				foreach ($aSite_Aliases as $oSite_Alias)
				{
					$sAlias = preg_quote($oSite_Alias->name, '#');
					$sAlias = str_replace('\*\.', '(?:[a-zA-Z0-9\.\-]*\.)?', $sAlias);
					if (preg_match('#^https?://' . $sAlias . '/#', $this->referrer, $matches))
					{
						$inner = 1;
						break;
					}
				}

				$oCounter_Visit->counter_referrer_id = $this->update('counter_referrers', array(
					'site_id' => $this->site->id,
					'date' => $sCurrentDate,
					'referrer' => $bRemoveEmoji ? Core_Str::removeEmoji($this->referrer) : $this->referrer,
					'inner' => $inner
				));
			}

			$oCounter_Visit->counter_page_id = $this->update('counter_pages', array(
				'site_id' => $this->site->id,
				'date' => $sCurrentDate,
				'page' => $bRemoveEmoji ? Core_Str::removeEmoji($this->page) : $this->page
			));

			$oCounter_Visit->counter_searchquery_id = is_null($searchsystem)
				? 0
				: $this->update('counter_searchqueries', array(
					'site_id' => $this->site->id,
					'date' => $sCurrentDate,
					'searchquery' => mb_substr($bRemoveEmoji ? Core_Str::removeEmoji($searchquery) : $searchquery, 0, 255),
					'searchsystem' => $searchsystem
				));

			$oCounter_Visit->new_user = intval($this->bNewUser);
			$oCounter_Visit->ip = Core_Str::ip2hex($this->ip);
			$oCounter_Visit->host = $this->_config['gethostbyaddr']
				? @gethostbyaddr($this->ip)
				: NULL;
			$oCounter_Visit->siteuser_id = $this->siteuserId;
			$oCounter_Visit->datetime = date('Y-m-d H:i:s');
			$oCounter_Visit->save();

			$iPrimaryKey = self::getPrimaryKeyByDate($sCurrentDate) . $site_id;

			// Если это не бот, обновляем данные
			if (!$this->bBot)
			{
				$sUpdate = "`hits` = `hits` + 1, `hosts` = `hosts` + " . intval($bNewIp) . ", " .
					"`sessions` = `sessions` + " . intval($this->bNewSession) . ", " .
					"`new_users` = `new_users` + " . intval($this->bNewUser);

				$sValues = "('{$iPrimaryKey}', '{$site_id}', '{$sCurrentDate}', 1, " . intval($bNewIp) . ", " . intval($this->bNewSession) . ", 0, " . intval($this->bNewUser) . ", 0)";
			}
			else
			{
				$sUpdate = "`bots` = `bots` + 1";
				$sValues = "('{$iPrimaryKey}', '{$site_id}', '{$sCurrentDate}', 0, 0, 0, 1, 0, 0)";
			}

			$oCore_Database = Core_DataBase::instance();

			$oCore_Database
				->setQueryType(2)
				->query("UPDATE `counters` SET {$sUpdate} WHERE `id` = {$iPrimaryKey}");

			$iAffectedRows = $oCore_Database->getAffectedRows();

			if ($iAffectedRows == 0)
			{
				$oCore_Database
					->setQueryType(2)
					->query("INSERT INTO `counters` (`id`, `site_id`, `date`, `hits`, `hosts`, `sessions`, `bots`, `new_users`, `sent`) " .
						"VALUES {$sValues} " .
						"ON DUPLICATE KEY UPDATE {$sUpdate}");
			}

			Core_Event::notify(get_class($this) . '.onAfterUpdateCounter', $this);
		}

		// Периодически очищаем таблицы с подробными данными статистики, подлежащими удалению
		if (rand(0, $this->cleaningFrequency / 2) == 0)
		{
			$period_storage = defined('STAT_PERIOD_STORAGE') ? STAT_PERIOD_STORAGE : 30;

			// Получаем дату, начиная с которой необходимо хранить статистику (тек. дата-кол-во дней хранения в сек.)
			$cleaningDate = date('Y-m-d', strtotime("-{$period_storage} day"));

			$iLimit = intval($this->cleaningFrequency);

			$oCore_Database = Core_DataBase::instance();

			// counter_sessions
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_sessions` WHERE `site_id` = '{$site_id}' AND `last_active` < '{$cleaningDate} 00:00:00' LIMIT {$iLimit}");

			// counter_pages
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_pages` WHERE `site_id` = '{$site_id}' AND `date` < '{$cleaningDate}' LIMIT {$iLimit}");

			// counter_browsers
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_browsers` WHERE `site_id` = '{$site_id}' AND `date` < '{$cleaningDate}' LIMIT {$iLimit}");

			// counter_devices
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_devices` WHERE `site_id` = '{$site_id}' AND `date` < '{$cleaningDate}' LIMIT {$iLimit}");

			// counter_displays
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_displays` WHERE `site_id` = '{$site_id}' AND `date` < '{$cleaningDate}' LIMIT {$iLimit}");

			// counter_oses
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_oses` WHERE `site_id` = '{$site_id}' AND `date` < '{$cleaningDate}' LIMIT {$iLimit}");

			// counter_referrers
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_referrers` WHERE `site_id` = '{$site_id}' AND `date` < '{$cleaningDate}' LIMIT {$iLimit}");

			// counter_searchqueries
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_searchqueries` WHERE `site_id` = '{$site_id}' AND `date` < '{$cleaningDate}' LIMIT {$iLimit}");

			// counter_useragents
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_useragents` WHERE `site_id` = '{$site_id}' AND `date` < '{$cleaningDate}' LIMIT {$iLimit}");

			// counter_visits
			$oCore_Database->setQueryType(3)
				->query("DELETE LOW_PRIORITY QUICK FROM `counter_visits` WHERE `site_id` = '{$site_id}' AND `datetime` < '{$cleaningDate} 00:00:00' LIMIT {$iLimit}");
		}

		/* Получаем данные для сайта за указанный день*/
		$oCounter = $this->getDayInformation($sCurrentDate);

		/*
		 * Данные за предыдущий день не были отправлены, при этом данные отправляются до 5 утра максимум.
		 * Время отправки определяется как остаток деления ID сайта на 5
		 */
		//if (!$oCounter->sent && date('G') >= $site_id % 5)
		if (!$oCounter->sent && date('i') >= $site_id % 5)
		{
			$oCounter->sent = 1;
			$oCounter->save();

			if ($this->site->send_attendance_report)
			{
				Core_Log::instance()->clear()
					->notify(FALSE)
					->status(Core_Log::$MESSAGE)
					->write(sprintf('Counter: Daily report sent, site "%d"', $site_id));

				$this->mailReport(date('Y-m-d', strtotime('-1 day')));
			}
		}

		// Если не бот, выводим счетчик
		if (!$this->bBot)
		{
			$aCounterConfig = Core_Array::get($this->_config['counters'], $this->counterId, array()) + array(
				'show' => 1, 'image_name' => '', 'color_red' => 0, 'color_blue' => 0, 'color_green' => 0,
			);

			// выводим счетчик
			if (!is_null($oCounter))
			{
				// Хиты
				$str = $oCounter->hits;
				$i1 = strlen($str);

				// Сессии
				$str2 = $oCounter->sessions;
				$i2 = strlen($str2);

				/* Всего сессий для сайта*/
				$str3 = $this->getAllSession($site_id);

				$i3 = strlen($str3);

				$file_name = CMS_FOLDER . 'counter/' . $aCounterConfig['image_name'];

				// Проверяем наличие файла с изображением для счетчика
				if (!is_file($file_name))
				{
					throw new Core_Exception('File %file does not exist', array(
						'%file' => $file_name
					));
				}

				$im = imagecreatefromgif($file_name);

				// Цвет текста
				$red = Core_Array::get($aCounterConfig, 'color_red');
				$blue = Core_Array::get($aCounterConfig, 'color_blue');
				$green = Core_Array::get($aCounterConfig, 'color_green');
				$color_text = imagecolorallocate($im, $red, $blue, $green);

				if ($aCounterConfig['show'] == 1)
				{
					$padding = 5;
					$y1 = (88 - $padding) - $i1*5;
					$y2 = (88 - $padding) - $i2*5;
					$y3 = (88 - $padding) - $i3*5;
					imagestring($im, 1, $y2, 2, $str2, $color_text); // вывод сессий
					imagestring($im, 1, $y1, 11, $str, $color_text); // вывод хитов
					imagestring($im, 1, $y3, 20, $str3, $color_text); // всего
				}

				header("Content-type: image/gif");
				imagegif ($im);
				imagedestroy($im);
			}
			else // Не выбрано ни одной записи о данных счетчика за текущий день
			{
				// Обновление не производилось
				if ($this->updateCounter == 0)
				{
					/* Вызываем этот же метод только с обновлением данных для счетчика (если метод вызван из админки, а за текущий день не было ни одного посетителя)*/
					$this
						->updateCounter(1)
						->buildCounter();
				}
			}
		}
		return $this;
	}

	/**
	* Определение наличия записи за сегодняшний день для переданного IP адреса для переданного сайта
	*
	* @param string $hexIp упакованный IP-адрес
	* @param string $sCurrentDate текущая дата
	* @param int $site_id идентификатор сайта
	* @return int 1 - новый, 0 - не новый
	* <code>
	* <?php
	* $hexIp = '192.168.0.1';
	* $sCurrentDate = date('Y-m-d');
	* $site_id = 1;
	*
	* $result = Counter_Controller::instance()->ipIsNew($hexIp, $sCurrentDate, $site_id);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function ipIsNew($hexIp, $sCurrentDate, $site_id)
	{
		$oCounter_Visit = Core_Entity::factory('Counter_Visit');
		$oCounter_Visit->queryBuilder()
			->clear()
			->where('site_id', '=', intval($site_id))
			->where('datetime', '>', $sCurrentDate . ' 00:00:00')
			->where('ip', '=', $hexIp)
			->limit(1);
		$oCounter_Visit = $oCounter_Visit->find();

		return intval(is_null($oCounter_Visit->id));
	}

	/**
	* Проверка user-agent на принадлежность к ботам
	*
	* @param string $agent user-agent
	* @return bool true, если это бот, false в противном случае
	*
	* <code>
	* <?php
	* $agent = 'YANDEX';
	*
	* $is_bot = Counter_Controller::checkBot($agent);
	*
	* // Распечатаем результат
	* var_dump($is_bot);
	* ?>
	* </code>
	*/
	static public function checkBot($agent)
	{
		return preg_match('/http|bot|spide|craw|yandex|seach|seek|site|sogou|yahoo|msnbot|google|bing/iu', $agent);
	}

	/**
	* Определение запроса из поисковой системы. Метод работает с поисковыми системами:
	* - Yandex
	* - Google
	* - Mail.Ru
	* - Aport
	* - Yahoo.com
	* - Metabot
	* - bing.com
	*
	* @param string $str адрес ссылающейся страницы
	* @return mixed поисковый запрос или FALSE
	* <code>
	* <?php
	* $str = 'http://yandex.ru/yandsearch?clid=13999&yasoft=barff&text=cms';
	*
	* $aSearchSystem = Counter_Controller::instance()->isSearchSystem($str);
	*
	* // Распечатаем результат
	* var_dump($aSearchSystem);
	* ?>
	* </code>
	*/
	static public function isSearchSystem($str)
	{
		$uri = @parse_url($str);

		// Хост поисковой системы
		$host = Core_Array::get($uri, 'host', '');

		// если нет запроса - значит не поисковая система
		if (!isset($uri['query']))
		{
			return FALSE;
		}

		mb_parse_str($uri['query'], $query);

		$aSearchSystem = array(
			'search_system' => $host
		);

		if ($host == "ya.ru" || $host == "yandex.ru" || $host ==" yandex.ua"
			|| preg_match("/ya.ru/", $host) || preg_match("/yandex.ru/", $host) || preg_match("/yandex.ua/", $host)
		)
		{
			$return = Core_Array::get($query, 'text');
		}
		elseif (preg_match("/rambler.ru/", $host))
		{
			$return = Core_Array::get($query, 'query');
		}
		elseif (preg_match('/^www\.google\./u',$host))
		{
			$return = Core_Array::get($query, 'q');
		}
		elseif (preg_match("/go.mail.ru/", $host))
		{
			//$return = @iconv('Windows-1251', 'UTF-8//IGNORE//TRANSLIT', Core_Array::get($query, 'q'));
			$return = Core_Array::get($query, 'q');
		}
		elseif (preg_match("/bing.com/", $host))
		{
			$return = Core_Array::get($query, 'q');
		}
		elseif (preg_match("/search.yahoo.com/", $host))
		{
			$return = @iconv('Windows-1251', 'UTF-8//IGNORE//TRANSLIT', Core_Array::get($query, 'p'));
		}
		elseif (preg_match("/webalta.ru/", $host))
		{
			$return = Core_Array::get($query, 'q');
		}
		elseif (preg_match("/metabot.ru/", $host))
		{
			$return = @iconv('Windows-1251', 'UTF-8//IGNORE//TRANSLIT', Core_Array::get($query, 'st'));
		}
		else
		{
			return FALSE;
		}

		$aSearchSystem['search_query'] = $return;

		return $aSearchSystem;
	}

	/**
	 * Get OS name
	 * @param string $userAgent User agent
	 * @return string
	 */
	static public function getOs($userAgent)
	{
		return Core_Browser::getOs($userAgent);
	}

	/**
	 * Get browser name
	 * @param string $userAgent User agent
	 * @return string
	 */
	static public function getBrowser($userAgent)
	{
		return Core_Browser::getBrowser($userAgent);
	}

	/**
	 * Get device type by User Agent
	 * @param string $userAgent
	 * @return 0 - desktop, 1 - tablet, 2 - phone, 3 - tv, 4 - watch
	 */
	static public function getDevice($userAgent)
	{
		return Core_Browser::getDevice($userAgent);
	}

	/**
	 * Get correspond Counter_Session or insert new one
	 * @return int sessionId
	 */
	public function insertSession()
	{
		$date = date('Y-m-d');

		$oCounter_Session = Core_Entity::factory('Counter_Session');

		$oCounter_Session->counter_display_id = $this->display != ''
			? $this->update('counter_displays', array(
				'site_id' => $this->site->id,
				'date' => $date,
				'display' => mb_substr($this->display, 0, 11)
				))
			: 0;

		$oCounter_Session->counter_useragent_id = $this->update('counter_useragents', array(
			'site_id' => $this->site->id,
			'date' => $date,
			'useragent' => mb_substr(Core_Str::removeEmoji($this->userAgent), 0, 255),
			'crawler' => intval($this->bBot)
		));

		$oCounter_Session->counter_os_id = !$this->bBot
			? $this->update('counter_oses', array('site_id' => $this->site->id, 'date' => $date, 'os' => self::getOs($this->userAgent)))
			: 0;

		$oCounter_Session->counter_browser_id = !$this->bBot
			? $this->update('counter_browsers', array('site_id' => $this->site->id, 'date' => $date, 'browser' => self::getBrowser($this->userAgent)))
			: 0;

		$oCounter_Session->counter_device_id = !$this->bBot
			? $this->update('counter_devices', array('site_id' => $this->site->id, 'date' => $date, 'device' => self::getDevice($this->userAgent)))
			: 0;

		$oCounter_Session->site_id = $this->site->id;
		$oCounter_Session->last_active = Core_Date::timestamp2sql(time());
		$oCounter_Session->save();

		// Флаг добавления новой сессии - истина
		$this->bNewSession = TRUE;

		return $oCounter_Session->id;
	}

	/**
	 * Update row. If row does not exit, call insertOnUpdate()
	 * @param string $tableName
	 * @param array $aValues
	 */
	public function update($tableName, array $aValues)
	{
		$oCore_Database = Core_DataBase::instance();

		$iPrimaryKey = Core::crc32(implode('#', $aValues));

		$quotedTableName = $oCore_Database->quoteColumnName($tableName);
		$sQuery = "UPDATE {$quotedTableName} SET `count` = `count` + 1 WHERE `id` = {$iPrimaryKey}";

		$oCore_Database
			->setQueryType(2)
			->query($sQuery);

		$iAffectedRows = $oCore_Database->getAffectedRows();

		return $iAffectedRows == 0
			? $this->insertOnUpdate($tableName, $aValues)
			: $iPrimaryKey;
	}

	public function insertOnUpdate($tableName, array $aValues)
	{
		$oCore_Database = Core_DataBase::instance();

		$iPrimaryKey = Core::crc32(implode('#', $aValues));

		// Quote VALUES
		$aValues = array_map(array($oCore_Database, 'quote'), $aValues);

		// Quote Table Name
		$tableName = $oCore_Database->quoteColumnName($tableName);
		// Quote COLUMNS NAME
		$aTmpKeys = array_map(array($oCore_Database, 'quoteColumnName'), array_keys($aValues));

		$sQuery = "INSERT INTO {$tableName} (`id`, " . implode(', ', $aTmpKeys) . ", `count`) " .
			"VALUES ('{$iPrimaryKey}', " . implode(', ', $aValues) . ", 1) ";

		$sQuery .= "ON DUPLICATE KEY UPDATE `count` = `count` + 1";

		$oCore_Database
			->setQueryType(2)
			->query($sQuery);

		return $iPrimaryKey;
	}

	/**
	* Определение числа сессий для сайта за весь период подсчета статистики.
	*
	* @param int $site_id идентификатор сайта
	* @return int число сессий для сайта
	* <code>
	* <?php
	* $site_id = CURRENT_SITE;
	* $result = Counter_Controller::instance()->getAllSession($site_id);
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function getAllSession($site_id)
	{
		$site_id = intval($site_id);

		$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
		$inCache = $oCore_Cache->get($cacheKey = $site_id, $cacheName = 'counter_allSession');

		if (!is_null($inCache))
		{
			return $inCache;
		}

		$row = Core_QueryBuilder::select(array('SUM(sessions)', 'count'))
			->from('counters')
			->where('site_id', '=', $site_id)
			->groupBy('site_id')
			->execute()
			->asAssoc()
			->current();

		$oCore_Cache->set($cacheKey, $row['count'], $cacheName);

		return $row['count'];
	}

	/**
	* Получение данных посещаемости за определенный день. Метод использует кэш "COUNTER_DAY_INFORMATION"
	*
	* @param string $date дата в формате ГГГГ-ММ-ДД
	* @return mixed array с данными или false, если данные отсутствуют
	* <code>
	* <?php
	* $date = date('Y-m-d');
	* $oCounter = Counter_Controller::instance()->getDayInformation($date);
	*
	* // Распечатаем результат
	* if (is_null($oCounter))
	* {
	* 	echo "Данные за указанный период не найдены";
	* }
	* ?>
	* </code>
	*/
	public function getDayInformation($date)
	{
		return $this->site->Counters->getByDate($date);
	}

	/**
	* Отправка письма с отчетом администратору сайта
	*
	* @param string $date дата отчета
	* @return boolean false в случае ошибки, в случае успеной отправки метод не возвращет никаких значений
	* <code>
	* <?php
	* $site_id = 1;
	* $date = date('Y-m-d');
	* Counter_Controller::instance()->mailReport($site_id, $date);
	* ?>
	* </code>
	*/
	public function mailReport($date)
	{
		$oCounter = $this->getDayInformation($date);

		if (!is_null($oCounter))
		{
			$sessions = $oCounter->sessions;
			$hosts = $oCounter->hosts;
			$hits = $oCounter->hits;
			$new_users = $oCounter->new_users;
			$bots = $oCounter->bots;
		}
		else
		{
			$sessions = $hosts = $hits = $new_users = $bots = 0;
		}

		$site_name = $this->site->name;

		$iTimestamp = Core_Date::sql2timestamp($date);

		$mail_text = "Уважаемый администратор сайта!\r\n\r\n";
		$mail_text.= "Отчет о посещаемости сайта \"{$site_name}\" за " . Core_Date::timestamp2date($iTimestamp) . ":\r\n\r\n";
		$mail_text.= "---------------------------------------------------------------------\r\n";
		$mail_text.= chr(32).chr(32).chr(32)."Сессии".chr(32).chr(32).chr(32)."|".chr(32).chr(32).chr(32)."Хосты".chr(32).chr(32).chr(32)."|".chr(32).chr(32).chr(32)."Хиты".chr(32).chr(32).chr(32)."|".chr(32).chr(32).chr(32)."Новые посетители".chr(32).chr(32).chr(32)."|".chr(32).chr(32).chr(32)."Боты\r\n";
		$mail_text.= "---------------------------------------------------------------------\r\n";
		$mail_text.= $this->alignTextSpace(12,$sessions)."|".$this->alignTextSpace(11,$hosts)."|".$this->alignTextSpace(10,$hits)."|".$this->alignTextSpace(22,$new_users)."|".$this->alignTextSpace(10,$bots)."\r\n";
		$mail_text.= "---------------------------------------------------------------------\r\n";
		$mail_text.= "\r\n";

		// Количество строк в таблицах ежедневного письма-отчета о посещаемости сайта
		$iLimit = defined('COUNTER_NUM_MAIL_TABLE_ROW') ? intval(COUNTER_NUM_MAIL_TABLE_ROW) : 20;

		// Получаем дату вчерашнего дня
		$sDaySql = Core_Date::timestamp2sql(strtotime("-1 day", $iTimestamp));

		// Получаем начальную дату предшествующей недели
		$sWeekSql = Core_Date::timestamp2sql(strtotime("-1 week", $iTimestamp));

		// Получаем данные для сайта за указанный день
		$oCounter = $this->getDayInformation($sDaySql);

		if (!is_null($oCounter))
		{
			$sessions_y = $oCounter->sessions;
			$hosts_y = $oCounter->hosts;
			$hits_y = $oCounter->hits;
			$new_users_y = $oCounter->new_users;
			$bots_y = $oCounter->bots;

			// Считаем динамику измнений по сравнению с вчерашним днем
			// Если значение за вчерашний день = 0, делим на 1, т.к. на 0 делить нельзя
			$sessions_dinamic = round((($sessions - $sessions_y) / ($sessions_y ? $sessions_y : 1)) * 100) . "%";
			$hosts_dinamic = round(($hosts - $hosts_y) / ($hosts_y ? $hosts_y : 1) * 100) . "%";
			$hits_dinamic = round(($hits - $hits_y) / ($hits_y ? $hits_y : 1) * 100) . "%";
			$new_users_dinamic = round(($new_users - $new_users_y) / ($new_users_y ? $new_users_y : 1) * 100) . "%";
			$bots_dinamic = round(($bots - $bots_y) / ($bots_y ? $bots_y : 1) * 100) . "%";

			// Добавляем динамику изменений за вчерашний день к тексту письма
			$mail_text .= "Динамика изменений по сравнению с предыдущим днем (".Core_Date::timestamp2date(Core_Date::sql2timestamp($sDaySql)) . "):\r\n\r\n";
			$mail_text .= "---------------------------------------------------------------------\r\n";
			$mail_text .= "   Сессии   |   Хосты   |   Хиты   |   Новые посетители   |   Боты   \r\n";
			$mail_text .= "---------------------------------------------------------------------\r\n";
			$mail_text .= $this->alignTextSpace(12, ($sessions_dinamic > 0 ? '+' : '') . $sessions_dinamic)
				. "|" . $this->alignTextSpace(11, ($hosts_dinamic > 0 ? '+' : '') . $hosts_dinamic)
				. "|" . $this->alignTextSpace(10, ($hits_dinamic > 0 ? '+' : '') . $hits_dinamic)
				. "|" . $this->alignTextSpace(22, ($new_users_dinamic > 0 ? '+' : '') . $new_users_dinamic)
				. "|" . $this->alignTextSpace(10, ($bots_dinamic > 0 ? '+' : '') . $bots_dinamic) . "\r\n";
			$mail_text.= "---------------------------------------------------------------------\r\n";
			$mail_text.= "\r\n";
		}

		// Получаем среднее значение данных для предыдущей недели
		$oSelect = Core_QueryBuilder::select(
				array('avg(sessions)', 'avg_sessions'),
				array('avg(hosts)', 'avg_hosts'),
				array('avg(hits)', 'avg_hits'),
				array('avg(new_users)', 'avg_new_users'),
				array('avg(bots)', 'avg_bots'))
			->from('counters')
			->where('site_id', '=', $this->site->id)
			->where('date', 'BETWEEN', array($sWeekSql, $sDaySql))
			->groupBy('site_id')
			->execute();

		$row = $oSelect->asAssoc()->current();

		if ($row)
		{
			$sessions_avg = round($row['avg_sessions']);
			$hosts_avg = round($row['avg_hosts']);
			$hits_avg = round($row['avg_hits']);
			$new_users_avg = round($row['avg_new_users']);
			$bots_avg = round($row['avg_bots']);

			// Считаем динамику измнений по сравнению со средним значением за предыдущую неделю
			// Если среднее значение = 0, делим на 1, т.к. на 0 делить нельзя
			$sessions_dinamic_avg = round(($sessions - $sessions_avg) / ($sessions_avg ? $sessions_avg : 1) * 100) . "%";
			$hosts_dinamic_avg = round(($hosts - $hosts_avg) / ($hosts_avg ? $hosts_avg : 1) * 100) . "%";
			$hits_dinamic_avg = round(($hits - $hits_avg) / ($hits_avg ? $hits_avg : 1) * 100) . "%";
			$new_users_dinamic_avg = round(($new_users - $new_users_avg) / ($new_users_avg ? $new_users_avg : 1) * 100) . "%";
			$bots_dinamic_avg = round(($bots - $bots_avg) / ($bots_avg ? $bots_avg : 1) * 100) . "%";

			// Добавляем динамику изменений за вчерашний день к тексту письма
			$mail_text .= "Динамика изменений по сравнению со средним значением для предыдущей недели:\r\n\r\n";
			$mail_text .= "---------------------------------------------------------------------\r\n";
			$mail_text .= "   Сессии   |   Хосты   |   Хиты   |   Новые посетители   |   Боты   \r\n";
			$mail_text .= "---------------------------------------------------------------------\r\n";
			$mail_text.= $this->alignTextSpace(12, ($sessions_dinamic_avg > 0 ? '+' : '') . $sessions_dinamic_avg)
				. "|" . $this->alignTextSpace(11, ($hosts_dinamic_avg > 0 ? '+' : '') . $hosts_dinamic_avg)
				. "|" . $this->alignTextSpace(10, ($hits_dinamic_avg > 0 ? '+' : '') . $hits_dinamic_avg)
				. "|" . $this->alignTextSpace(22, ($new_users_dinamic_avg > 0 ? '+' : '') . $new_users_dinamic_avg)
				. "|" . $this->alignTextSpace(10, ($bots_dinamic_avg > 0 ? '+' : '') . $bots_dinamic_avg) . "\r\n";
			$mail_text.= "---------------------------------------------------------------------\r\n";
			$mail_text.= "\r\n";
		}

		// Получаем самые популярные страницы
		$oQueryBuilder = Core_QueryBuilder::select()
			->from('counter_pages')
			->where('site_id', '=', $this->site->id)
			->where('date', '=', $date)
			->orderBy('count', 'DESC')
			->limit($iLimit);

		$aSelect = $oQueryBuilder->execute()->asAssoc()->result();

		if (count($aSelect))
		{
			$mail_text .= "Популярные страницы сайта:\r\n\r\n";
			$mail_text .= "----------------------------------------------------\r\n";
			$mail_text .= " № | Количество |   Адрес страницы   \r\n";
			$mail_text .= "----------------------------------------------------\r\n";

			foreach ($aSelect as $key => $aRow)
			{
				$mail_text .= $this->alignTextSpace(2, $key + 1)
					. " |" . $this->alignTextSpace(12, $aRow['count'])
					. "|   " .  str_replace(' ', '+', $aRow['page']) . "\r\n";
			}

			$mail_text .= "----------------------------------------------------\r\n";
			$mail_text .= "\r\n";
		}


		// Получаем ссылающиеся страницы
		$oQueryBuilder = Core_QueryBuilder::select()
			->from('counter_referrers')
			->where('site_id', '=', $this->site->id)
			->where('date', '=', $date)
			->orderBy('count', 'DESC')
			->limit($iLimit);

		$aSelect = $oQueryBuilder->execute()->asAssoc()->result();

		if (count($aSelect))
		{
			$mail_text .= "Ссылающиеся страницы:\r\n\r\n";
			$mail_text .= "----------------------------------------------------\r\n";
			$mail_text .= " № | Количество |   Адрес страницы   \r\n";
			$mail_text .= "----------------------------------------------------\r\n";

			foreach ($aSelect as $key => $aRow)
			{
				$mail_text.= $this->alignTextSpace(2, $key + 1)
					. " |" . $this->alignTextSpace(12, $aRow['count'])
					. "|   " . str_replace(' ', '+', $aRow['referrer']) . "\r\n";
			}

			$mail_text.= "----------------------------------------------------\r\n";
			$mail_text.= "\r\n";
		}

		// Получаем поисковые запросы за предыдущий день
		$oQueryBuilder = Core_QueryBuilder::select()
			->from('counter_searchqueries')
			->where('site_id', '=', $this->site->id)
			->where('date', '=', $date)
			->where('searchquery', '!=', '')
			->orderBy('count', 'DESC')
			->limit($iLimit);

		$aSelect = $oQueryBuilder->execute()->asAssoc()->result();

		if (count($aSelect))
		{
			$mail_text.= "Самые популярные поисковые запросы:\r\n\r\n";
			$mail_text.= "----------------------------------------------------\r\n";
			$mail_text.= " № | Количество |   Источник   | Запрос \r\n";
			$mail_text.= "----------------------------------------------------\r\n";

			foreach ($aSelect as $key => $aRow)
			{
				$mail_text.= $this->alignTextSpace(2, $key + 1)
					. " |" . $this->alignTextSpace(12, $aRow['count'])
					. " |" . $this->alignTextSpace(14, $aRow['searchsystem'])
					. "| " . $aRow['searchquery'] . "\r\n";
			}

			$mail_text.= "----------------------------------------------------\r\n";
			$mail_text.= "\r\n";
		}

		// Получаем User Agents ботов
		$oQueryBuilder = Core_QueryBuilder::select()
			->from('counter_useragents')
			->where('site_id', '=', $this->site->id)
			->where('date', '=', $date)
			->where('crawler', '=', 1)
			->orderBy('count', 'DESC')
			->limit($iLimit);

		$aSelect = $oQueryBuilder->execute()->asAssoc()->result();

		if (count($aSelect))
		{
			$mail_text.= "Посещение сайта поисковыми ботами:\r\n\r\n";
			$mail_text.= "----------------------------------------------------\r\n";
			$mail_text.= " № | Количество |   Бот   \r\n";
			$mail_text.= "----------------------------------------------------\r\n";

			foreach ($aSelect as $key => $aRow)
			{
				$mail_text.= $this->alignTextSpace(2, $key + 1)
					. " |" . $this->alignTextSpace(12, $aRow['count'])
					. "|   " . $aRow['useragent'] . "\r\n";
			}

			$mail_text.= "----------------------------------------------------\r\n";
			$mail_text.= "\r\n";
		}

		$mail_text.="\r\n---\r\nСистема управления сайтом HostCMS,\r\nhttp://www.hostcms.ru/\r\n";

		Core_Mail::instance()
			->to($this->site->admin_email)
			->from($this->site->getFirstEmail())
			->subject(Core::_('Counter.subject', $site_name))
			->message($mail_text)
			->contentType('text/plain')
			->header('X-HostCMS-Reason', 'Counter')
			->header('Precedence', 'bulk')
			->messageId()
			->send();
	}

	/**
	* Выравнивание текста по центру с помощью пробелов
	*
	* @param int $count_all общее количество символов
	* @param strlen $text текст
	* @return string строка с выровненным текстом
	* <code>
	* <?php
	* $count_all = 31;
	* $text = 'Тестовый текст для выравнивания';
	*
	* $result = Counter_Controller::instance()->alignTextSpace($count_all, $text);
	*
	* // Распечатаем результат
	* echo $result;
	* ?>
	* </code>
	*/
	public function alignTextSpace($count_all, $text)
	{
		// Получаем разницу между длиной текста и общим кол-ом символов
		$count = $count_all - mb_strlen($text);

		// Если общее кол-во больше - выравниваем текст
		if ($count > 0)
		{
			$text = str_pad($text, $count_all - round($count / 2), ' ', STR_PAD_LEFT);
			$text = str_pad($text, $count_all);
		}

		return $text;
	}
}