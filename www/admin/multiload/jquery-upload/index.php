<?php
/*
 * jQuery File Upload Plugin PHP Example
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require_once 'UploadHandler.php';

/**
 * Установить идентификатор сайта
 */
 
$module_name = "multiload";
require_once('../../../bootstrap.php');
error_reporting(E_ALL | E_STRICT);
Core_Auth::authorization($module_name);
$oSite = Core_Entity::factory('Site', CURRENT_SITE);
Core::initConstants($oSite);

$filename = $_FILES['files']['name'][0];
$filename = mb_convert_encoding($filename, 'utf8', mb_detect_encoding($filename));
$filename = trim($filename);
$uploadDir = CMS_FOLDER.'upload/multiload/';
$uploadUrl = '/upload/multiload/';
$filePath = $uploadDir.$filename;

$type = Core_Array::getPost('loadtype');
$entityNamePattern = Core_Array::getPost('itemname', '');
$entityName = $filename;

// имя элемента
if(mb_strpos($entityNamePattern, '$1') === FALSE && mb_strpos($entityNamePattern, '$2') === FALSE)
{
	$entityName = $filename;
}
else
{
	$extPos = mb_strrpos($filename, '.');
	$filenameWithoutExtension = ($extPos === FALSE) ? $filename : mb_substr($filename, 0, $extPos);
	
	$entityName = str_replace("$1", $filename, $entityNamePattern);
	$entityName = str_replace("$2", $filenameWithoutExtension, $entityName);
}

// Определяем расширение файла
$ext = Core_File::getExtension($filename);

// закачиваем
$uploadHandler = new UploadHandler(array('upload_dir' => $uploadDir, 'upload_url' => $uploadUrl));

if ($type == 2 && Core_Array::getPost('shop_id'))
{
	$shop_id = Core_Array::getPost('shop_id', 0);
	$shop_group_id = Core_Array::getPost('shop_group_id', 0);
	$shop_item_id = Core_Array::getPost('shopitem_id', 0);
	$property_id = Core_Array::getPost('property_id', 0);	
	
	$oShop = Core_Entity::factory('shop', $shop_id);

	if ($shop_item_id && $property_id)
	{
		// Загружаем в доп. свойство
		$oShop_Item = Core_Entity::factory("shop_item", $shop_item_id);
		$oShop_Item->createDir();
		
		$oProperty = Core_Entity::factory('Property', $property_id);
		
		$oPropertyValue = $oProperty->createNewValue($oShop_Item->id);
		$oPropertyValue->save();
		
		$large_image = 'property_value_' . $oPropertyValue->id . '.' . $ext;
		$small_image = 'small_' . $large_image;
					
		$oShop_Item->createDir();
		
		$param = array();

		// Путь к файлу-источнику большого изображения;
		$param['large_image_source'] = $filePath;

		// Оригинальное имя файла большого изображения
		$param['large_image_name'] = $large_image;
		
		// Оригинальное имя файла малого изображения
		$param['small_image_name'] = $small_image;

		// Путь к создаваемому файлу большого изображения;
		$param['large_image_target'] = $oShop_Item->getItemPath() . Core_File::convertFileNameToLocalEncoding($large_image);

		// Путь к создаваемому файлу малого изображения;
		$param['small_image_target'] = $oShop_Item->getItemPath() . Core_File::convertFileNameToLocalEncoding($small_image);

		// Использовать большое изображение для создания малого
		$param['create_small_image_from_large'] = TRUE;
		$param['watermark_file_path'] = $oShop->getWatermarkFilePath();
		$param['watermark_position_x'] = $oShop->watermark_default_position_x;
		$param['watermark_position_y'] = $oShop->watermark_default_position_y;
		$param['large_image_preserve_aspect_ratio'] = $oShop->preserve_aspect_ratio;
		$param['small_image_max_width'] = $oShop->image_small_max_width;
		$param['small_image_max_height'] = $oShop->image_small_max_height;
		$param['small_image_watermark'] = $oShop->watermark_default_use_small_image;
		$param['small_image_preserve_aspect_ratio'] = $oShop->preserve_aspect_ratio_small;
		$param['large_image_max_width'] = $oShop->image_large_max_width;
		$param['large_image_max_height'] = $oShop->image_large_max_height;
		$param['large_image_watermark'] = $oShop->watermark_default_use_large_image;

		$result = Core_File::adminUpload($param);
			
		$oPropertyValue->file = $large_image;
		$oPropertyValue->file_small = $small_image;
		$oPropertyValue->file_description = $filename;
		$oPropertyValue->save();
	}
}

if($type == 1 && Core_Array::getPost('informationsystem_id'))
{
	$informationsystem_id = Core_Array::getPost('informationsystem_id', 0);
	$informationsystem_group_id = Core_Array::getPost('informationsystem_group_id', 0);
	$informationsystem_item_id = Core_Array::getPost('informationsystemitem_id', 0);
	$property_id = Core_Array::getPost('property_id', 0);
			
	$oInformationSystem = Core_Entity::factory('Informationsystem', $informationsystem_id);
	
	if ($informationsystem_item_id && $property_id)
	{
		// Загружаем в доп. свойство
		$oInformationSystem_item = Core_Entity::factory("Informationsystem_item", $informationsystem_item_id);
		$oInformationSystem_item->createDir();
		
		$oProperty = Core_Entity::factory('Property', $property_id);
		
		$oPropertyValue = $oProperty->createNewValue($oInformationSystem_item->id);
		$oPropertyValue->save();
		
		$large_image = 'property_value_' . $oPropertyValue->id . '.' . $ext;
		$small_image = 'small_' . $large_image;
		
		$oInformationSystem_item->createDir();
					
		$param = array();

		// Путь к файлу-источнику большого изображения;
		$param['large_image_source'] = $filePath;

		// Оригинальное имя файла большого изображения
		$param['large_image_name'] = $large_image;
		
		// Оригинальное имя файла малого изображения
		$param['small_image_name'] = $small_image;

		// Путь к создаваемому файлу большого изображения;
		$param['large_image_target'] = $oInformationSystem_item->getItemPath() . Core_File::convertFileNameToLocalEncoding($large_image);

		// Путь к создаваемому файлу малого изображения;
		$param['small_image_target'] = $oInformationSystem_item->getItemPath() . Core_File::convertFileNameToLocalEncoding($small_image);

		// Использовать большое изображение для создания малого
		$param['create_small_image_from_large'] = TRUE;
		$param['watermark_file_path'] = $oInformationSystem->getWatermarkFilePath();
		$param['watermark_position_x'] = $oInformationSystem->watermark_default_position_x;
		$param['watermark_position_y'] = $oInformationSystem->watermark_default_position_y;
		$param['large_image_preserve_aspect_ratio'] = $oInformationSystem->preserve_aspect_ratio;
		$param['small_image_max_width'] = $oInformationSystem->image_small_max_width;
		$param['small_image_max_height'] = $oInformationSystem->image_small_max_height;
		$param['small_image_watermark'] = $oInformationSystem->watermark_default_use_small_image;
		$param['small_image_preserve_aspect_ratio'] = $oInformationSystem->preserve_aspect_ratio_small;
		$param['large_image_max_width'] = $oInformationSystem->image_large_max_width;
		$param['large_image_max_height'] = $oInformationSystem->image_large_max_height;
		$param['large_image_watermark'] = $oInformationSystem->watermark_default_use_large_image;

		$result = Core_File::adminUpload($param);
		
		$oPropertyValue->file = $large_image;
		$oPropertyValue->file_small = $small_image;
		$oPropertyValue->file_description = $filename;
		$oPropertyValue->save();
	}
	else
	{
		// Загружаем в инф. элемент
		// создаем инф. элемент
		$oInformationSystem_item = Core_Entity::factory("Informationsystem_item");
		$oInformationSystem_item->informationsystem_id = $informationsystem_id;
		$oInformationSystem_item->informationsystem_group_id = $informationsystem_group_id;
		$oInformationSystem_item->name = $entityName;
		$oInformationSystem_item->save();	

		$param = array();

		// Путь к файлу-источнику большого изображения;
		$param['large_image_source'] = $filePath;

		$large_image = 'information_items_' . $oInformationSystem_item->id . '.' . $ext;
		$small_image = 'small_' . $large_image;

		// Оригинальное имя файла большого изображения
		$param['large_image_name'] = $large_image;
		
		// Оригинальное имя файла малого изображения
		$param['small_image_name'] = $small_image;

		// Путь к создаваемому файлу большого изображения;
		$param['large_image_target'] = $oInformationSystem_item->getItemPath() . Core_File::convertFileNameToLocalEncoding($large_image);

		// Путь к создаваемому файлу малого изображения;
		$param['small_image_target'] = $oInformationSystem_item->getItemPath() . Core_File::convertFileNameToLocalEncoding($small_image);

		// Использовать большое изображение для создания малого
		$param['create_small_image_from_large'] = TRUE;
		$param['watermark_file_path'] = $oInformationSystem->getWatermarkFilePath();
		$param['watermark_position_x'] = $oInformationSystem->watermark_default_position_x;
		$param['watermark_position_y'] = $oInformationSystem->watermark_default_position_y;
		$param['large_image_preserve_aspect_ratio'] = $oInformationSystem->preserve_aspect_ratio;
		$param['small_image_max_width'] = $oInformationSystem->image_small_max_width;
		$param['small_image_max_height'] = $oInformationSystem->image_small_max_height;
		$param['small_image_watermark'] = $oInformationSystem->watermark_default_use_small_image;
		$param['small_image_preserve_aspect_ratio'] = $oInformationSystem->preserve_aspect_ratio_small;
		$param['large_image_max_width'] = $oInformationSystem->image_large_max_width;
		$param['large_image_max_height'] = $oInformationSystem->image_large_max_height;
		$param['large_image_watermark'] = $oInformationSystem->watermark_default_use_large_image;

		$oInformationSystem_item->createDir();

		$result = Core_File::adminUpload($param);

		if ($result['large_image'])
		{
			$oInformationSystem_item->image_large = $large_image;
			$oInformationSystem_item->setLargeImageSizes();
		}

		if ($result['small_image'])
		{
			$oInformationSystem_item->image_small = $small_image;
			$oInformationSystem_item->setSmallImageSizes();
		}

		$oInformationSystem_item->save();
	}
}

Core_File::delete($filePath);