<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Dropbox REST API https://www.dropbox.com/developers/core/docs
 *
 * @package HostCMS
 * @subpackage Cloud
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Cloud_Dropbox_Controller extends Cloud_Controller
{
	public function __construct(Cloud_Model $oCloud)
	{
		parent::__construct($oCloud);

		$aConfig = Core_Config::instance()->get('cloud_config', array());

		isset($aConfig['drivers'])
			&&  $this->_config = Core_Array::get($aConfig['drivers'], 'dropbox');

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

		return "https://www.dropbox.com/1/oauth2/authorize?response_type=code&client_id={$this->_oCloud->key}";
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
			->url('https://api.dropboxapi.com/1/oauth2/token')
			->data('grant_type', 'authorization_code')
			->data('code', $this->_oCloud->code)
			->data('client_id', $this->_oCloud->key)
			->data('client_secret', $this->_oCloud->secret)
			->execute();

		$sAnswer = $Core_Http->getBody();

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
		if (!is_null($this->_token))
		{
			//$sDirectory = '/';
			$sDirectory = '';

			if (is_null($this->dirId))
			{
				$sDirectory = $this->dir = trim(str_replace('\\', '/', $this->dir));
			}
			else
			{
				$sDirectory = base64_decode($this->dirId);
			}

			$sDirectory = rawurlencode(ltrim($sDirectory, '/'));

			if (!isset(self::$_cache[$sDirectory]))
			{
				self::$_cache[$sDirectory] = array();

				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('GET')
					->url("https://api.dropboxapi.com/1/metadata/auto/{$sDirectory}")
					->additionalHeader("Authorization", "Bearer {$this->_token}")
					->execute();

				$oAnswer = json_decode($Core_Http->getBody());

				if (property_exists($oAnswer, 'contents'))
				{
					foreach ($oAnswer->contents as $oObject)
					{
						$oCurrentObject = new stdClass();
						$oCurrentObject->id = base64_encode($oObject->path);
						$oCurrentObject->is_dir = $oObject->is_dir;
						$oCurrentObject->bytes = 0;
						if (!$oCurrentObject->is_dir)
						{
							$oCurrentObject->bytes = $oObject->bytes;
							$oCurrentObject->datetime = Core_Date::timestamp2sql(Core_Date::date2timestamp($oObject->modified));
						}
						$oCurrentObject->path = trim($oObject->path, "/");
						$oCurrentObject->name = basename($oObject->path);
						self::$_cache[$sDirectory][] = $oCurrentObject;
					}
				}
				elseif (property_exists($oAnswer, 'error'))
				{
					throw new Core_Exception($oAnswer->error, array(), 0, FALSE);
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
	 * @param string $sTargetPath target file path
	 */
	public function download($sFileName, $sTargetPath)
	{
		if (!is_null($this->_token))
		{
			$sFileName = base64_decode($sFileName);

			Core_Session::start();

			$aFileData = Core_Array::get($_SESSION, 'HOSTCMS_CLOUD_DROPBOX_DOWNLOAD', array());

			if (!count($aFileData))
			{
				// получаем данные о файле
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('GET')
					->url("https://api.dropboxapi.com/1/metadata/auto/{$sFileName}")
					->additionalHeader("Authorization", "Bearer {$this->_token}")
					->execute();

				$oAnswer = json_decode($Core_Http->getBody());

				$aFileData['size'] = $oAnswer->bytes;
				$aFileData['href'] = "https://content.dropboxapi.com/1/files/auto/{$sFileName}";
				$aFileData['range_from'] = 0;
				$aFileData['size'] == 0 && $aFileData['size'] = 1;

				$aFileData['range_to'] = $this->chunkSize > $aFileData['size']
					? ''
					: $this->chunkSize - 1;

				$iFlag = 0;
			}
			else
			{
				$iFlag = FILE_APPEND;
			}

			$sBytesRange = $aFileData['range_from'] . '-' . $aFileData['range_to'];

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('GET')
				->url($aFileData['href'])
				->additionalHeader("Authorization", "Bearer {$this->_token}")
				->additionalHeader("Range", "bytes={$sBytesRange}")
				->execute();

			$sRawFileData = $Core_Http->getBody();

			$sDirName = dirname($sTargetPath);
			Core_File::mkdir($sDirName, CHMOD, TRUE);

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
				$aFileData['range_to'] += $this->chunkSize;
				if ($aFileData['range_from'] + $this->chunkSize > $aFileData['size'])
				{
					$aFileData['range_to'] = '';
				}
			}

			Core_Array::set($_SESSION, 'HOSTCMS_CLOUD_DROPBOX_DOWNLOAD', $aFileData);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Upload file into cloud
	 * @param string $sSourceFileName file name
	 * @param string $sTargetDirectory target file directory
	 */
	public function upload($sSourceFileName, $sTargetDirectory)
	{
		if (!is_null($this->_token))
		{
			$this->dir = trim(str_replace('\\', '/', $this->dir), '/');

			$this->dir != '' && $this->dir = '/' . $this->dir . '/';

			$sTargetPath = $this->dir . $sSourceFileName;

			Core_Session::start();

			$aFileData = Core_Array::get($_SESSION, 'HOSTCMS_CLOUD_DROPBOX_UPLOAD', array());

			if (!count($aFileData))
			{
				$aFileData['size'] = filesize($sTargetDirectory . $sSourceFileName);
				$aFileData['size'] == 0 && $aFileData['size'] = 1;
				$aFileData['href'] = "https://content.dropboxapi.com/1/chunked_upload";
				$aFileData['href_ext'] = '';
				$aFileData['offset'] = 0;
			}

			$rFilePointer = fopen($sTargetDirectory . $sSourceFileName, "rb");

			fseek($rFilePointer, $aFileData['offset'], SEEK_SET);

			$sRawFileData = fread($rFilePointer, $this->chunkSize);

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->timeout(300)
				->method('PUT')
				->url($aFileData['href'] . '?' . $aFileData['href_ext'] . "offset={$aFileData['offset']}")
				->additionalHeader('Authorization', "Bearer {$this->_token}")
				->additionalHeader('Content-Type', 'application/octet-stream')
				->rawData($sRawFileData)
				->execute();

			$oAnswer = json_decode($Core_Http->getBody());

			if (feof($rFilePointer))
			{
				// больше запросов не нужно
				$this->percent = 0;
				$aFileData = array();

				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('POST')
					->url("https://content.dropboxapi.com/1/commit_chunked_upload/auto/{$this->dir}{$sSourceFileName}?upload_id={$oAnswer->upload_id}")
					->additionalHeader("Authorization", "Bearer {$this->_token}")
					->execute();
			}
			else
			{
				$aFileData['href_ext'] = "upload_id={$oAnswer->upload_id}&";
				$aFileData['offset'] = ftell($rFilePointer);
				$this->percent = $aFileData['offset'] * 100 / $aFileData['size'];
			}

			fclose($rFilePointer);

			Core_Array::set($_SESSION, 'HOSTCMS_CLOUD_DROPBOX_UPLOAD', $aFileData);

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
			'id' => base64_encode('/')
		);

		$sPath = is_null($this->dirId)
			? str_replace('\\', '/', $this->dir)
			: urldecode(base64_decode($this->dirId));

		$aPath = explode('/', trim($sPath, '/'));

		for ($i = 0; $i < count($aPath); $i++)
		{
			$aBreadCrumbs[] = array(
				'name' => $aPath[$i],
				'id' => base64_encode(implode('/', array_slice($aPath, 0, $i + 1)))
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
		if (!is_null($this->_token))
		{
			$sPath = urldecode(base64_decode($oObjectData->id));

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('POST')
				->url("https://api.dropbox.com/1/fileops/delete?root=auto&path={$sPath}")
				->additionalHeader("Authorization", "Bearer {$this->_token}")
				->execute();

			return TRUE;
		}

		return FALSE;
	}
}