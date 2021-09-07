<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Eventlog Dataset.
 *
 * @package HostCMS
 * @subpackage Eventlog
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Eventlog_Dataset extends Admin_Form_Dataset
{
	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Event date
	 * @var string
	 */
	protected $_date = NULL;

	/**
	 * Log file name
	 * @var string
	 */
	protected $_fileName = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_date = date('Y-m-d');
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		if (is_null($this->_count))
		{
			foreach ($this->_conditions as $condition)
			{
				foreach ($condition as $operator => $args)
				{
					if ($args[0] == 'datetime')
					{
						$this->_date = is_array($args[2]) ? $args[2][0] : $args[2];
					}
				}
			}

			$this->_fileName = Core_Log::instance()->getLogName($this->_date);

			$this->_getEvents();

			$this->_count = count($this->_objects);
		}

		return $this->_count;
	}

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		!is_array($this->_objects) && $this->_getEvents();

		return array_slice($this->_objects, $this->_offset, $this->_limit);
	}

	/**
	 * Load data
	 * @return self
	 */
	protected function _getEvents()
	{
		$status = NULL;

		foreach ($this->_conditions as $condition)
		{
			foreach ($condition as $operator => $args)
			{
				if ($args[0] == 'event')
				{
					$status = $args[2];
				}
			}
		}

		$this->_objects = array();

		if (is_file($this->_fileName))
		{
			if ($fp = fopen($this->_fileName, 'r'))
			{
				$i = 0;

				// Обрабатываем данные из csv-файла и получаем двумерный массив данных:
				// [0]-дата/время, [1]-имя пользователя, [2]-события, [3]-статус события, [4]-сайт, [5]-страница

				while (!feof($fp))
				{
					$event = fgetcsv($fp, 256000);

					// Проверяем на пустоту значение первого элемента и количество элементов массива должно быть > 3
					if (empty($event[0]) || count($event) < 4)
					{
						continue;
					}

					if (is_null($status) || $status == $event[3] || $status == -1)
					{
						if (isset($event[5]))
						{
							$Eventlog_Event = $this->_newObject();

							$Eventlog_Event->datetime = $event[0];
							$Eventlog_Event->login = $event[1];
							$Eventlog_Event->event = @iconv("UTF-8", "UTF-8//IGNORE//TRANSLIT", $event[2]);
							$Eventlog_Event->status = $event[3];
							$Eventlog_Event->site = $event[4];
							$Eventlog_Event->page = $event[5];
							$Eventlog_Event->ip = $event[6];
							$Eventlog_Event->id = $i;
							$this->_objects[$i] = $Eventlog_Event;
						}

						$i++;
					}
				}

				fclose($fp);
			}
		}

		return $this;
	}

	/**
	 * Get new object
	 * @return object
	 */
	protected function _newObject()
	{
		return new Eventlog_Event();
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return $this->_newObject();
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		return $this->_objects[$primaryKey];
	}
}