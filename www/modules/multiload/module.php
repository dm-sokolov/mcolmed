<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
* multiload module
*
* @author KAD Systems (©) 2014
* @date
*/

class MultiLoad_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '1.1.4';
	public $version_number = 7;
	private $_module_id = 2;
	private $_internal_name = "multiload";
	private $_client_name = "Мультизагрузка изображений";
	private $_admin_name = "Мультизагрузка";
	private $_module_path;

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2021-06-23';
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->menu = array(
			array(
				'sorting' => 260,
				'block' => 2,
				'ico' => 'fa fa-th-large',
				'name' => $this->_admin_name,
				'href' => "/admin/" . $this->_internal_name . "/index.php",
				'onclick' => "$.adminLoad({path: '/admin/" . $this->_internal_name . "/index.php'}); return false"
			)
		);

		$this->_module_path = CMS_FOLDER . "/modules/{$this->_internal_name}/";

		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/vendor/jquery.ui.widget.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/github/JavaScript-Templates/js/tmpl.min.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/github/JavaScript-Load-Image/js/load-image.all.min.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/github/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/github/Gallery/js/jquery.blueimp-gallery.min.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/jquery.iframe-transport.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/jquery.fileupload.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/jquery.fileupload-process.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/jquery.fileupload-image.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/jquery.fileupload-audio.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/jquery.fileupload-video.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/jquery.fileupload-validate.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/jquery.fileupload-ui.js");
		Core_Skin::instance()->addJs("/admin/{$this->_internal_name}/jquery-upload/js/main.js");
	}

	public function install()
	{
		// Импорт таблиц модуля
		$query = Core_File::read($this->_module_path . 'install.sql');

		// Выполняем запрос
		Sql_Controller::instance()->execute($query);

		Kad_Module_Controller::install();
		$oController = new Kad_Module_Controller($this->_module_id);

		// Задаем параметры
		$oController->set('version', $this->version);
		$oController->set('version_number', $this->version_number);
		$oController->set('internal_name', $this->_internal_name);
		$oController->set('client_name', $this->_client_name);
		$oController->set('admin_name', $this->_admin_name);
		$oController->set('module_id', $this->_module_id);

		// Устанавливаем наблюдатели
		$this->_injectObservers();

		Core_Message::show("Модуль успешно установлен!");
	}

	public function uninstall()
	{
		$query = Core_File::read($this->_module_path . 'uninstall.sql');

		// Выполняем запрос
		Sql_Controller::instance()->execute($query);

		Core_Message::show("Модуль успешно удален!");
	}

	/**
	 * Установка наблюдателей.
	 *
	 * @return void
	 */
	protected function _injectObservers()
	{
		$observersFile = 'modules/' . $this->_internal_name . '/observers.php';

		if (is_file(CMS_FOLDER . $observersFile))
		{
			$constring = '$fn = CMS_FOLDER . "' . $observersFile . '"; if (file_exists($fn)) require_once($fn);';

			$text = Core_File::read(CMS_FOLDER . 'bootstrap.php');
			if (strpos($text, $constring) == 0)
			{
				$fp = fopen( CMS_FOLDER . 'bootstrap.php', 'a');
				fwrite($fp, "\n\r" . $constring);
				fclose($fp);
			}
		}
	}
}