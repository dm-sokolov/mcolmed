<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

 /**
* Переключатель панели администрирования
*
* Версия для HostCMS v.6x
* @author KAD
* http://www.artemkuts.ru/
* artem.kuts@gmail.com
*/
 
class Kad_Admin_Switcher
{
	private $_cAllowPanel;
	
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
	
	// Стиль блока в котором будет выведена кнопка
	public $style = "position:absolute; padding: 5px; z-index: 999; background-color: white; border: 1px solid #777; border-bottom-right-radius:10px; font-size: 8pt; opacity:0.5;";
	public $off = " - ";
	public $on = " + ";
	
	
	public function __construct()
	{
		$this->_cAllowPanel = Core_Entity::factory('constant')->getByName('ALLOW_PANEL');
		if ($this->_cAllowPanel->value != "false")
		{
			$this->_cAllowPanel->value = "false";
			$this->_cAllowPanel->save();
		}
	}
	
	public function execute()
	{
		if (isset($_SESSION['current_users_id']) && $_SESSION['current_users_id'] != 0)
		{
			$panelActive = !(Core_Array::getGet('control_panels', $this->_cAllowPanel->active));
			$oldActive = $this->_cAllowPanel->active;
			
			?>
				<div style="<?=$this->style?>">
			<?php
			
			if ($panelActive)
			{
				?>
					<a href="?control_panels=1"><?php echo $this->off?></a>
				<?php
				$this->_cAllowPanel->active = false;
			} else
			{
				?>
					<a href="?control_panels=0"><?php echo $this->on?></a>
				<?php
				$this->_cAllowPanel->active = true;
			}
			
			?>
				</div>
			<?php
			
			$this->_cAllowPanel->save();
			if ($this->_cAllowPanel->active != $oldActive)
			{
				?>
					<script>
						document.location.reload();
					</script>
				<?php
			}
		}
	}
}
