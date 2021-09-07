<?php

/**
 * Система управления сайтом HostCMS v. 5.xx
 *
 * Copyright © 2005-2011 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 *
 * Класс модуля "Система сжатия (компрессия)".
 *
 * Файл: /modules/Compression/Compression.class.php
 *
 * @package HostCMS 5
 * @author Hostmake LLC
 * @version 5.x
 */
class compression
{
	protected $Compression_Controller = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->Compression_Controller = Compression_Controller::instance('http');
	}

	/**
	 * Запуск приема из стандартного потока содержимого для компрессии
	 * @return boolean
	 */
	function PageCompressionBegin()
	{
		if (defined('DISABLE_COMPRESSION') && DISABLE_COMPRESSION)
		{
			return FALSE;
		}

		// Начало буферизации выводимого контента
		define('COMPRESSION_BEGIN', TRUE);
		ob_start();
		ob_implicit_flush(0);

		return TRUE;
	}

	/**
	 * Получение типа сжатия gzip
	 * @return string возвращает строку или false
	 */
	function CheckTypeGzip()
	{
		return $this->Compression_Controller->getAcceptEncoding();
	}

	/**
	 * Вывод сжатых данных, размещенных в потоке
	 */
	function PageCompressionEnd()
	{
		if (!$this->Compression_Controller->compressionAllowed())
		{
			return FALSE;
		}

		$encoding = $this->CheckTypeGzip();

		if ($encoding)
		{
			$gzip_content = ob_get_clean();

			if (!empty($gzip_content))
			{
				header("Content-Encoding: {$encoding}");
				echo $this->Compression_Controller->compress($gzip_content);
			}
		}
		else
		{
			ob_end_flush();
		}
	}
}

if (-1977579255 & (~Core::convert64b32(Core_Array::get(Core::$config->get('core_hostcms'), 'hostcms'))))
{
	die();
}