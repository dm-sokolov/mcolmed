<?php

/**
 * Redirects
 *
 * @version 1.35
 * @author Eugeny Panikarowsky - evgenii_panikaro@mail.ru
 * @copyright © 2018 Eugeny Panikarowsky
 *
*/

defined('HOSTCMS') || exit('HostCMS: access denied.');

	class Hostdev_Redirect_Controller_Launch {
		
		static private $_mask = false;

		static public function setParams() {

			$old_url = $_SERVER['REQUEST_URI'];
			
			$referer = Core_Array::get($_SERVER, 'HTTP_REFERER', '');
			$oRedirect = Core_Entity::factory('hostdev_redirect');
			$oRedirect->queryBuilder()
				->where('old_url', '=',$old_url)
				->where('active', '=', 1)
				->where('site_id', '=', CURRENT_SITE)
				->where('referer', '=', $referer);
			$oRedirect = $oRedirect->find();
			if (is_null($oRedirect->old_url)) {
				$oRedirect = Core_Entity::factory('hostdev_redirect');
				$oRedirect->queryBuilder()
					->where('old_url', '=',$old_url)
					->where('active', '=', 1)
					->where('site_id', '=', CURRENT_SITE);
				$oRedirect = $oRedirect->find();
				if (!is_null($oRedirect->old_url)) {
					return $oRedirect;
				}
			} else {
				return $oRedirect;
			}
			
			$oRedirects = Core_Entity::factory('hostdev_redirect');
			$oRedirects->queryBuilder()
				->where('old_url', 'LIKE', '%*')
				->where('active', '=', 1)
				->where('site_id', '=', CURRENT_SITE)
				->where('referer', '=', $referer);
			$aRedirects = $oRedirects->findAll();
			if (sizeof($aRedirects)) {
				foreach ($aRedirects as $oRedirect) {
					$url_row = str_replace('*', '',$oRedirect->old_url);
					$pos = strpos($old_url, $url_row);
					if ($pos === 0) {
						if (!$oRedirect->append) {
							self::$_mask = $url_row;
						}
						return $oRedirect;
					}
				}
			} else {
				$oRedirects = Core_Entity::factory('hostdev_redirect');
				$oRedirects->queryBuilder()
					->where('old_url', 'LIKE', '%*')
					->where('site_id', '=', CURRENT_SITE)
					->where('active', '=', 1);
				$aRedirects = $oRedirects->findAll();
				//echo sizeof($aRedirects);
				
				if (sizeof($aRedirects)) {
					foreach ($aRedirects as $oRedirect) {
						$url_row = str_replace('*', '',$oRedirect->old_url);
						$pos = strpos($old_url, $url_row);
						if ($pos === 0) {
							if (!$oRedirect->append) {
								self::$_mask = $url_row;
							}
						//	$new_url = str_replace($url_row, $oRedirect->new_url, $old_url);
							//$oRedirect->new_url = $new_url;
							return $oRedirect;
						}
					}
				}
				
			}
			return null;
		}

		static public function onBeforeshowBody($object, $args) {
			if (defined('CURRENT_SITE')) {
				ob_start();
			}
		}

		static public function onAftershowBody($object, $args) {
			if (!defined('CURRENT_SITE')) {
				return;
			}
			$sContent = ob_get_clean();

			$old_urls = $new_urls = array();

			$oRedirect = Core_Entity::factory('hostdev_redirect');
			$oRedirect->queryBuilder()->where('active', '=', 1)->where('site_id', '=', CURRENT_SITE )->asAssoc();
			$aRedirects = $oRedirect->findAll();
			foreach($aRedirects as $oRedirect) {
				$new_url = self::getNewUrl($oRedirect);
				if ($new_url) {
					$old_urls[] = 'href="'.$oRedirect->old_url.'"';
					$new_urls[] = 'href="'.$new_url.'"';
				}
			}
			if (!empty($old_urls) && !empty($new_urls)) {
				$sContent = str_replace($old_urls, $new_urls, $sContent);
			}
			echo $sContent;
		}

		static public function onAfterShowAction($object, $args) {
			if (!defined('CURRENT_SITE')) {
				return;
			}
			$old_urls = $new_urls = array();

			$oRedirect = Core_Entity::factory('hostdev_redirect');
			$oRedirect->queryBuilder()->where('active', '=', 1)->where('site_id', '=', CURRENT_SITE )->asAssoc();
			$aRedirects = $oRedirect->findAll();
			foreach($aRedirects as $oRedirect) {
				$new_url = self::getNewUrl($oRedirect);
				if ($new_url) {
					$old_urls[] = 'href="'.$oRedirect->old_url.'"';
					$new_urls[] = 'href="'.$new_url.'"';
				}
			}

			if (!empty($old_urls) && !empty($new_urls)) {
				$oCore_Response = &$args[0];
				ob_start();
				$oCore_Response->showBody();
				$sContent = ob_get_clean();
				$sContent = str_replace($old_urls, $new_urls, $sContent);
				$oCore_Response->clear();
				$oCore_Response->body($sContent);
			}
		}


		static public function onBeforeShowAction($object, $args) {
			if (!defined('CURRENT_SITE')) {
				return;
			}
			$old_path = self::setParams();

			if (is_null($old_path)) {
				return;
			}
			$path = self::getNewUrl($old_path);

			if ($path && $path != $old_path->old_url) {
				$oCore_Response = new Core_Response();
				$oCore_Response->status(301);
				$oCore_Response->header('Location', $path);
				$oCore_Response->compress();
				$oCore_Response->sendHeaders();
				$oCore_Response->showBody();
				exit();
			}

			//echo $object->_uri;
		}
		
		static public function getNewUrl($oRedirect) {

			$path = false;

				if (!is_null($oRedirect->new_url)) {
					switch($oRedirect->type) {
						case 0: {
							$path = $oRedirect->new_url;
							break;
						}
						case 1: {

							$oStructure = Core_Entity::factory('structure',$oRedirect->new_url);
							if (!is_null($oStructure)){
								$path = $oStructure->getPath();
							}
							break;
						}
						case 2: {
							$oInformationsystem = Core_Entity::factory('Informationsystem',$oRedirect->informationsystem_id);
							$path = $oInformationsystem->structure->getPath();
							if ($oRedirect->informationsystem_group_id) {
								$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group',$oRedirect->informationsystem_group_id);
								if (!is_null($oInformationsystem_Group)){
									if ($oInformationsystem_Group->getPath() != '/') {
										$path .= $oInformationsystem_Group->getPath();
									}
								}
							}
							break;
						}
						case 3: {
							if ($oRedirect->informationsystem_item_id && $oRedirect->informationsystem_id) {
								$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item',$oRedirect->informationsystem_item_id);
								if (!is_null($oInformationsystem_Item)){
									$path  = $oInformationsystem_Item->informationsystem->structure->getPath();
									$path .= $oInformationsystem_Item->getPath();
								}
							} else if ($oRedirect->informationsystem_id){
								$oInformationsystem = Core_Entity::factory('Informationsystem',$oRedirect->informationsystem_id);
								$path = $oInformationsystem->structure->getPath();
							}
							break;
						}
						case 4: {
							$oShop = Core_Entity::factory('Shop',$oRedirect->shop_id);
							$path = $oShop->structure->getPath();
							if ($oRedirect->shop_group_id) {
								$oShop_Group = Core_Entity::factory('Shop_Group',$oRedirect->shop_group_id);
								if (!is_null($oShop_Group)){
									if ($oShop_Group->getPath() != '/') {
										$path .= $oShop_Group->getPath();
									}
								}
							}
							break;
						}
						case 5: {
							if ($oRedirect->shop_item_id && $oRedirect->shop_id) {
								$oShop_Item = Core_Entity::factory('Shop_Item',$oRedirect->shop_item_id);
								if (!is_null($oShop_Item)){
									$path  = $oShop_Item->shop->structure->getPath();
									$path .= $oShop_Item->getPath();
								}
							} else if ($oRedirect->shop_id){
								$oShop = Core_Entity::factory('shop',$oRedirect->shop_id);
								$path = $oShop->structure->getPath();
							}
							break;
						}
					}
				}
			if (self::$_mask !== false) {
				$url_row = self::$_mask;
				$old_url = $_SERVER['REQUEST_URI'];
				$new_url = str_replace($url_row, $path, $old_url);
				return $new_url;
			}
			return $path;
		}
	}