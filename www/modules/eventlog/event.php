<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Eventlog.
 *
 * @package HostCMS
 * @subpackage Eventlog
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Eventlog_Event
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $id = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $datetime = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $login = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $event = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $status = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $site = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $page = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $ip = NULL;

	/**
	 * List of images
	 * @var array
	 */
	static protected $_img = array(
		0 => 'bullet_black.gif',
		1 => 'bullet_green.gif',
		2 => 'bullet_orange.gif',
		3 => 'bullet_pink.gif',
		4 => 'bullet_red.gif',
	);

	/**
	 * Backend callback method
	 * @return string
	 */
	public function user()
	{
		ob_start();

		Core::factory('Core_Html_Entity_A')
			->href($this->page)
			->target('_blank')
			->value(htmlspecialchars(Core_Str::cut($this->page, 50)))
			->execute();

		Core::factory('Core_Html_Entity_Br')
			->execute();

		Core::factory('Core_Html_Entity_Span')
			->value(htmlspecialchars($this->login . ' [' . $this->ip . ']'))
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend function
	 * @param mixed $value value
	 * @param Admin_Form_Field $oAdmin_Form_Field field
	 * @return string
	 */
	static public function eventFilter($value, $oAdmin_Form_Field, $filterPrefix)
	{
		ob_start();

		Core::factory('Core_Html_Entity_Select')
			->options(
				array(
					-1 => Core::_('Eventlog.form_show_all_events'),
					0 => Core::_('Eventlog.form_show_neutral'),
					1 => Core::_('Eventlog.form_show_successful'),
					2 => Core::_('Eventlog.form_show_low_criticality'),
					3 => Core::_('Eventlog.form_show_middle_criticality'),
					4 => Core::_('Eventlog.form_show_highes_criticality')
				)
			)
			->value($value == '' ? -1 : intval($value))
			->name($filterPrefix . $oAdmin_Form_Field->id)
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend function
	 * @param date $date_from
	 * @param date $date_to
	 * @param Admin_Form_Field $oAdmin_Form_Field field
	 * @return string
	 */
	static public function datetimeFilter($date_from, $date_to, $oAdmin_Form_Field, $filterPrefix)
	{
		ob_start();

		Core::factory('Core_Html_Entity_Div')
			->class('input-group date')
			->add(
				Core::factory('Core_Html_Entity_Input')
					->value(htmlspecialchars($date_from))
					->name($filterPrefix . 'from_' . $oAdmin_Form_Field->id)
					->id($filterPrefix . 'from_' . $oAdmin_Form_Field->id)
					->size(8)
					->class('form-control input-sm')
			)
			->execute();

		$sCurrentLng = Core_I18n::instance()->getLng();

		Core::factory('Core_Html_Entity_Script')
			->value("(function($) {
				$('#{$filterPrefix}from_{$oAdmin_Form_Field->id}')
					.datetimepicker({locale: '{$sCurrentLng}', format: '" . Core::$mainConfig['datePickerFormat'] . "'})
					.on('dp.show', datetimepickerOnShow);
			})(jQuery);")
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function event()
	{
		$event = trim($this->event);

		if (strlen($event) != 0)
		{
			$img = isset(self::$_img[$this->status])
				? self::$_img[$this->status]
				: self::$_img[0];

			ob_start();

			Core::factory('Core_Html_Entity_Div')
				->class('modalwindow')
				->value(nl2br(htmlspecialchars($event) . PHP_EOL . PHP_EOL . '<b>' . htmlspecialchars($this->login) . '</b>, ' . htmlspecialchars(Core_Date::sql2datetime($this->datetime))))
				->execute();
			$content = ob_get_clean();

			ob_start();

			$text = Core_Str::cutWords(htmlspecialchars($event), 50);

			$height = floor(mb_strlen($text) * 0.6);
			$height < 150 && $height = 150;
			$height > 450 && $height = 450;

			Core::factory('Core_Html_Entity_Div')
				->style("background: url('/admin/images/{$img}') no-repeat 0px 50%; padding-left: 15px")
				->add(
					Core::factory('Core_Html_Entity_Span')
						->class('pointer')
						->onclick("$.showWindow('eventLog{$this->id}', '" . Core_Str::escapeJavascriptVariable($content) . "', {width: 650, height: {$height}, title: '" . Core::_('Eventlog.detailed_event_info') . "', Maximize: false})")
						->value(nl2br($text))
				)
				->execute();

			return ob_get_clean();
		}

		return '&nbsp;';
	}
}