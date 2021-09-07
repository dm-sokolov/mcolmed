<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Yandex REST API https://tech.yandex.ru/disk/rest/
 *
 * @package HostCMS
 * @subpackage Cloud
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cloud_Handler_Yandex_Controller extends Cloud_Controller
{
	public function __construct(Cloud_Model $oCloud)
	{
		parent::__construct($oCloud);

		$aConfig = Core_Config::instance()->get('cloud_config', array());

		isset($aConfig['drivers'])
			&& $this->_config = Core_Array::get($aConfig['drivers'], 'yandex');

		$this->chunkSize = Core_Array::get($this->_config, 'chunk');

		if (strlen($this->_oCloud->access_token))
		{
			$AccessToken = json_decode($this->_oCloud->access_token);

			$this->_token = is_object($AccessToken) && isset($AccessToken->access_token)
				? $AccessToken->access_token
				: NULL;
		}
	}

	/**
	 * Get OAuth url
	 * @return string
	 */
	public function getOauthCodeUrl()
	{
		if ($this->_oCloud->key == '')
		{
			throw new Core_Exception("Invalid OAuth key");
		}

		return "https://oauth.yandex.ru/authorize?response_type=code&client_id={$this->_oCloud->key}";
	}

	/**
	 * Get access token
	 * @return string
	 */
	public function getAccessToken()
	{
		if ($this->_oCloud->key == '')
		{
			throw new Core_Exception("Invalid OAuth key", array(), 0, FALSE);
		}

		if ($this->_oCloud->secret == '')
		{
			throw new Core_Exception("Invalid OAuth secret", array(), 0, FALSE);
		}

		if ($this->_oCloud->code == '')
		{
			throw new Core_Exception("Invalid OAuth code", array(), 0, FALSE);
		}

		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('POST')
			->timeout($this->_timeout)
			->url("https://oauth.yandex.ru/token")
			->data('grant_type', 'authorization_code')
			->data('code', $this->_oCloud->code)
			->data('client_id', $this->_oCloud->key)
			->data('client_secret', $this->_oCloud->secret)
			->execute();

		$sAnswer = $Core_Http->getDecompressedBody();

		if ($sAnswer === FALSE)
		{
			throw new Core_Exception("Server response: false", array(), 0, FALSE);
		}

		$aAnswer = json_decode($sAnswer, TRUE);

		if (isset($aAnswer['error']))
		{
			throw new Core_Exception("Server response: {$aAnswer['error_description']}", array(), 0, FALSE);
		}

		return $sAnswer;
	}

	/**
	 * listDir Cache
	 * @var array
	 */
	static protected $_cache = array();

	/**
	 * Get directory list
	 * @return array
	 */
	public function listDir()
	{
		if ($this->_token)
		{
			$sDirectory = is_null($this->dirId)
				? $this->dir
				: urldecode(base64_decode($this->dirId));

			if (!isset(self::$_cache[$sDirectory]))
			{
				self::$_cache[$sDirectory] = array();

				$aParams = array('path' => $sDirectory);

				$sUrl = "https://cloud-api.yandex.net/v1/disk/resources?" . http_build_query($aParams);

				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('GET')
					->timeout($this->_timeout)
					->url($sUrl)
					//->additionalHeader('Accept:', '*/*') //fix curl connect to Yandex
					->additionalHeader("Authorization", "OAuth {$this->_token}")
					->execute();

				$oAnswer = json_decode($Core_Http->getDecompressedBody());

				if (!is_null($oAnswer) && property_exists($oAnswer, '_embedded') && property_exists($oAnswer->_embedded, 'items'))
				{
					foreach ($oAnswer->_embedded->items as $oObject)
					{
						$oCurrentObject = new stdClass();
						$oCurrentObject->id = base64_encode(urlencode($oObject->path));
						$oCurrentObject->is_dir = $oObject->type == 'dir';
						$oCurrentObject->bytes = 0;
						if (!$oCurrentObject->is_dir)
						{
							$oCurrentObject->bytes = $oObject->size;
							$oCurrentObject->datetime = Core_Date::timestamp2sql(Core_Date::date2timestamp($oObject->modified));
						}
						$oCurrentObject->path = $oObject->path;
						$oCurrentObject->name = $oObject->name;
						self::$_cache[$sDirectory][] = $oCurrentObject;
					}
				}
			}

			return self::$_cache[$sDirectory];
		}
		else
		{
			throw new Core_Exception("Invalid token", array(), 0, FALSE);
		}
	}

	/**
	 * Download file from cloud
	 * @param string $sFileName file name
	 * @param string $sTargetDir target file dir
	 */
	public function download($sFileName, $sTargetDir, $aParams = array())
	{
		if ($this->_token)
		{
			$sFileName = urlencode(base64_decode($sFileName));

			Core_Session::start();

			$aFileData = Core_Array::get($_SESSION, 'HOSTCMS_CLOUD_YANDEX_DOWNLOAD', array());

			if (!count($aFileData))
			{
				// получаем данные о файле
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('GET')
					->timeout($this->_timeout)
					->url("https://cloud-api.yandex.net/v1/disk/resources?path={$sFileName}")
					->additionalHeader("Authorization", "OAuth {$this->_token}")
					->execute();

				$aFileData = json_decode($Core_Http->getDecompressedBody(), TRUE);

				if (!is_null($aFileData))
				{
					$Core_Http = Core_Http::instance('curl')
						->clear()
						->method('GET')
						->timeout($this->_timeout)
						->url("https://cloud-api.yandex.net/v1/disk/resources/download?path={$sFileName}")
						->additionalHeader("Authorization", "OAuth {$this->_token}")
						->execute();

					$aFileData += json_decode($Core_Http->getDecompressedBody(), TRUE);

					$aFileData['range_from'] = 0;
					$aFileData['size'] == 0 && $aFileData['size'] = 1;
					$aFileData['range_to'] = $this->chunkSize > $aFileData['size']
						? ''
						: $this->chunkSize - 1;

					$iFlag = 0;
				}
				else
				{
					throw new Exception('File not found');
				}
			}
			else
			{
				$iFlag = FILE_APPEND;
			}

			$sBytesRange = $aFileData['range_from'] . "-" . $aFileData['range_to'];


			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('GET')
				->timeout($this->_timeout)
				->url($aFileData['href'])
				->additionalHeader("Authorization", "OAuth {$this->_token}")
				->additionalHeader("Range", "bytes={$sBytesRange}")
				->execute();

			$sRawFileData = $Core_Http->getDecompressedBody();

			Core_File::mkdir($sTargetDir, CHMOD, TRUE);

			$sTargetPath = rtrim($sTargetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $aFileData['name'];

			if (file_put_contents($sTargetPath, $sRawFileData, $iFlag) === FALSE)
			{
				throw new Exception('Can\'t write to file ' . $sTargetPath);
			}

			if ($aFileData['range_to'] == '')
			{
				// больше запросов не нужно
				$this->percent = 0;
				$aFileData = array();
			}
			else
			{
				$this->percent = $aFileData['range_to'] * 100 / $aFileData['size'];
				$aFileData['range_from'] = $aFileData['range_to'] + 1;
				$aFileData['range_to'] = $aFileData['range_to'] + $this->chunkSize;
				if ($aFileData['range_from'] + $this->chunkSize > $aFileData['size'])
				{
					$aFileData['range_to'] = '';
				}
			}

			Core_Array::set($_SESSION, 'HOSTCMS_CLOUD_YANDEX_DOWNLOAD', $aFileData);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Upload file into cloud
	 * @param string $sSourcePath file name
	 * @param string $sDestinationFileName
	 * @param array $aParams options, e.g. 'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
	 */
	public function upload($sSourcePath, $sDestinationFileName = NULL, $aParams = array())
	{
		// Yandex не поддерживает upload по частям:
		// http://habrahabr.ru/company/yandex/blog/227377/#comment_7714141
		// https://tech.yandex.ru/disk/api/reference/upload-docpage/

		if ($this->_token)
		{
			$this->dir = rtrim(str_replace('\\', '/', $this->dir), '/') . '/';

			$fileName = !is_null($sDestinationFileName)
				? $sDestinationFileName
				: basename($sSourcePath);

			$sTargetPath = urlencode($this->dir . $fileName);

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('GET')
				->timeout($this->_timeout)
				->url("https://cloud-api.yandex.net/v1/disk/resources/upload?path={$sTargetPath}&overwrite=true")
				->additionalHeader("Authorization", "OAuth {$this->_token}")
				->execute();

			$oFileData = json_decode($Core_Http->getDecompressedBody());

			if (isset($oFileData->error))
			{
				throw new Core_Exception($oFileData->message, array(), 0, FALSE);
			}

			$Core_Http = Core_Http::instance('curl')
				->clear()
				//->timeout(300)
				->timeout($this->_timeout)
				->method('PUT')
				->url($oFileData->href)
				// ->additionalHeader("Authorization", "OAuth {$this->_token}")
				;

			// Пример REST API приложения
			// https://habrahabr.ru/post/206752/

			if (file_exists($sSourcePath))
			{
				$fp = fopen($sSourcePath, 'rb');

				$aConfig = $Core_Http->getConfig();
				$aConfig['options'][CURLOPT_PUT] = TRUE;
				$aConfig['options'][CURLOPT_BINARYTRANSFER] = TRUE;
				$aConfig['options'][CURLOPT_INFILE] = $fp;
				$aConfig['options'][CURLOPT_INFILESIZE] = Core_File::filesize($sSourcePath);
				$Core_Http->config($aConfig);

				$Core_Http->execute();

				fclose($fp);

				$this->percent = 0;
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Ger breadcrumbs
	 * @return array
	 */
	public function getBreadCrumbs()
	{
		$aBreadCrumbs = array();

		$aBreadCrumbs[] = array(
			'name' => Core::_('cloud.myDisk'),
			'id' => base64_encode(urlencode('/'))
		);

		$sPath = is_null($this->dirId)
			? str_replace('\\', '/', $this->dir)
			: str_replace('disk:/', '', urldecode(base64_decode($this->dirId)));

		$aPath = explode('/', trim($sPath, '/'));

		for ($i = 0; $i < count($aPath); $i++)
		{
			$aBreadCrumbs[] = array(
				'name' => $aPath[$i],
				'id' => base64_encode(urlencode(implode('/', array_slice($aPath, 0, $i + 1))))
			);
		}

		return $aBreadCrumbs;
	}

	/**
	 * Delete file from cloud
	 * @param object $oObjectData file object
	 */
	public function delete($oObjectData)
	{
		if ($this->_token)
		{
			$sPath = urldecode(base64_decode($oObjectData->id));

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('DELETE')
				->timeout($this->_timeout)
				->url("https://cloud-api.yandex.net/v1/disk/resources?path={$sPath}")
				->additionalHeader("Authorization", "OAuth {$this->_token}")
				->execute();

			return TRUE;
		}

		return FALSE;
	}
}