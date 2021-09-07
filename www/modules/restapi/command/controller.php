<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * REST API command controller.
 *
 * @package HostCMS
 * @subpackage Restapi
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Restapi_Command_Controller extends Core_Command_Controller
{
	/**
	 * Selected API version
	 * @var string
	 */
	public $version = NULL;

	/**
	 * Request path
	 * @var string
	 */
	public $path = NULL;

	/**
	 * GET limit
	 * @var int
	 */
	protected $_limit = 25;

	/**
	 * GET offset
	 * @var int
	 */
	protected $_offset = 0;

	/**
	 * Request mode, e.g. JSON, XML
	 * @var string|NULL
	 */
	protected $_mode = NULL;

	/**
	 * Error Message
	 * @var string|NULL
	 */
	protected $_error = NULL;

	/**
	 * HTTP code, default 200
	 * @var int
	 */
	protected $_statusCode = 200;

	/**
	 * REST API answer
	 * @var mixed
	 */
	protected $_answer = NULL;

	/**
	 * @var User_Model|NULL
	 */
	protected $_user = NULL;

	/**
	 * Module Config
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_config = Core_Config::instance()->get('restapi_config', array()) + array(
			'url' => '/api',
			'xmlRootNode' => 'request',
			'log' => TRUE,
		);
	}

	/**
	 * Core_Response
	 * @var Core_Response|NULL
	 */
	protected $_oCore_Response = NULL;

	/**
	 * Default controller action
	 * @return Core_Response
	 * @hostcms-event Restapi_Command_Controller.onBeforeShowAction
	 * @hostcms-event Restapi_Command_Controller.onAfterShowAction
	 */
	public function showAction()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowAction', $this);

		$this->_oCore_Response = new Core_Response();

		ob_start();

		switch ($this->version)
		{
			case '1':
			case '1.0':
				$this->_version1();
			break;
			default:
				$this->_error = 'Wrong version';
				$this->_statusCode = 400;
			break;
		}

		if (!is_null($this->_statusCode))
		{
			$this->_oCore_Response->status($this->_statusCode);
		}

		$messageContent = ob_get_clean();

		$this->_oCore_Response
			->header('Pragma', 'no-cache')
			->header('Cache-Control', 'private, no-cache')
			->header('Vary', 'Accept');

		$return = $this->_answer;

		if ($this->_statusCode >= 300)
		{
			$return['error']['code'] = $this->_statusCode;

			if ($this->_config['log'])
			{
				Core_Log::instance()->clear()
					->notify(FALSE)
					->status(Core_Log::$MESSAGE)
					->write(sprintf('REST API. CODE: %d, ERROR: %s', $this->_statusCode, $this->_error));
			}
		}

		if (!is_null($this->_error))
		{
			is_array($return)
				&& $return['error']['message'] = $this->_error;
		}

		if ($messageContent != '')
		{
			is_array($return)
				&& $return['error']['extraMessage'] = $messageContent;
		}

		switch ($this->_mode)
		{
			case 'json':
			default:
				$this->_oCore_Response
					->header('Content-Disposition', 'inline; filename="files.json"')
					->header('Content-Type', 'application/json; charset=utf-8');

				$aJson = NULL;
				if (is_array($return))
				{
					foreach ($return as $key => $tmp)
					{
						$aJson[$key] = $this->_entity2array($tmp);
					}
				}
				else
				{
					$aJson = $this->_entity2array($return);
				}

				$this->_oCore_Response->body(json_encode($aJson));
			break;
			case 'xml':
				$this->_oCore_Response->header('Content-Type', 'application/xml; charset=utf-8');

				$this->_oCore_Response->body(
					'<?xml version="1.0" encoding="UTF-8"?>' . "\r\n" .
					'<' . Core_Str::xml($this->_config['xmlRootNode']) . '>' . "\r\n"
				);

				$this->_oCore_Response->body($this->_entity2XML($return));

				$this->_oCore_Response->body(
					'</' . Core_Str::xml($this->_config['xmlRootNode']) . '>'
				);
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterShowAction', $this, array($this->_oCore_Response));

		return $this->_oCore_Response;
	}

	/**
	 * Convert $entity to array
	 * @var mixed $entity
	 * @return array
	 */
	protected function _entity2array($entity)
	{
		if (is_object($entity))
		{
			return $entity instanceof Core_ORM
				? $entity->toArray()
				: $entity;
		}

		return $entity;
	}

	/**
	 * Convert $entity to XML
	 * @var mixed $entity
	 * @return string
	 */
	protected function _entity2XML($entity)
	{
		if (is_object($entity) && $entity instanceof Core_ORM)
		{
			return $entity->getXml();
		}

		return is_array($entity)
			? Core_Xml::array2xml($entity)
			: NULL;
	}

	protected function _parsePathV1($aPath)
	{
		foreach ($aPath as $key => $path)
		{
			if ($key == 0)
			{
				$sSingularName = $this->_getClassName($path);

				if ($sSingularName === FALSE)
				{
					$this->_error = 'Wrong entity name';
					$this->_statusCode = 400;

					return FALSE;
				}

				$oPreviosEntity = Core_Entity::factory($sSingularName);
			}
			elseif (is_numeric($path) && $oPreviosEntity instanceof Core_ORM)
			{
				$oPreviosEntity = $oPreviosEntity->getById($path);

				// Entity exists
				if (is_null($oPreviosEntity))
				{
					$this->_error = 'Entity Not Found By PK';
					$this->_statusCode = 403;

					return FALSE;
				}

				// Check access
				if (!$this->_user->checkObjectAccess($oPreviosEntity))
				{
					$this->_error = sprintf('Entity %d. Access Forbidden.', $path);
					$this->_statusCode = 403;

					return FALSE;
				}

				$bFound = TRUE;
			}
			else
			{
				if (is_object($oPreviosEntity) && $oPreviosEntity instanceof Core_ORM && $oPreviosEntity->id)
				{
					$bFound = FALSE;

					try {
						if (isset($oPreviosEntity->$path))
						{
							$oPreviosEntity = $oPreviosEntity->$path;
						}
						elseif (method_exists($oPreviosEntity, $path) || method_exists($oPreviosEntity, 'isCallable') && $oPreviosEntity->isCallable($path))
						{
							$oPreviosEntity = $oPreviosEntity->$path();

							$bFound = TRUE;
						}
					}
					catch (Exception $e)
					{
						$this->_error = $e->getMessage();
						$this->_statusCode = 422;

						return FALSE;
					}
				}
				else
				{
					$this->_error = 'Parent Entity Not Found';
					$this->_statusCode = 403;

					return FALSE;
				}
			}
		}

		$mAnswer = NULL;

		if ($bFound)
		{
			$mAnswer = $oPreviosEntity;
		}
		else
		{
			// LIMIT
			$tmpLimit = Core_Array::getGet('limit');
			if (is_numeric($tmpLimit) && $tmpLimit > 0)
			{
				$this->_limit = intval($tmpLimit);
			}

			// OFFSET
			$tmpOffset = Core_Array::getGet('offset');
			if (is_numeric($tmpOffset) && $tmpOffset >= 0)
			{
				$this->_offset = intval($tmpOffset);
			}

			$oPreviosEntity->queryBuilder()
				->limit($this->_limit)
				->offset($this->_offset);

			// ORDER BY
			$aTmpOrderBy = Core_Array::getGet('orderBy');
			if (!is_null($aTmpOrderBy) && !is_array($aTmpOrderBy))
			{
				$aTmpOrderBy = array($aTmpOrderBy);
			}
			if (is_array($aTmpOrderBy))
			{
				foreach ($aTmpOrderBy as $tmpOrderBy)
				{
					$aTmpExplodeOrderBy = explode(' ', $tmpOrderBy);
					if (strlen($aTmpExplodeOrderBy[0]))
					{
						$orderBy = $aTmpExplodeOrderBy[0];

						$orderByDirection = isset($aTmpExplodeOrderBy[1])
							? $aTmpExplodeOrderBy[1]
							: 'ASC';

						$oPreviosEntity->queryBuilder()
							->orderBy($orderBy, $orderByDirection);
					}
				}
			}

			$aPredefinedFields = array(
				'limit',
				'offset',
				'orderBy',
			);

			// OTHER OPTIONS
			foreach ($_GET as $key => $value)
			{
				if (!in_array($key, $aPredefinedFields))
				{
					$oPreviosEntity->queryBuilder()
						->where($key, is_array($value) && count($value) ? 'IN' : '=', $value);
				}
			}

			try {
				$aResult = $oPreviosEntity->findAll(FALSE);
			}
			catch (Exception $e)
			{
				$this->_error = $e->getMessage();
				$this->_statusCode = 422;

				return FALSE;
			}

			$mAnswer = array();
			foreach ($aResult as $oEntity)
			{
				if ($this->_user->checkObjectAccess($oEntity))
				{
					$mAnswer[] = $oEntity;
				}
			}
		}

		return $mAnswer;
	}

	/**
	 * Verson 1.0
	 * @hostcms-event Restapi_Command_Controller.onBeforeAddNewEntity
	 * @hostcms-event Restapi_Command_Controller.onBeforeUpdateEntity
	 */
	protected function _version1()
	{
		// Check Authorization
		$this->_checkAuthorization();

		if (is_null($this->_user))
		{
			$this->_error = 'Authentication Required';
			$this->_statusCode = 401;

			return FALSE;
		}

		// Check Content-Type
		$this->_checkContentType();

		if (is_null($this->_mode))
		{
			$this->_error = 'Wrong Content-Type';
			$this->_statusCode = 400;

			return FALSE;
		}

		// Check main entity
		if (is_null($this->path))
		{
			$this->_error = 'Empty Request';
			$this->_statusCode = 400;

			return FALSE;
		}

		$oPreviosEntity = NULL;
		$bFound = FALSE;

		$aPath = explode('/', $this->path);

		$sMethod = Core_Array::get($_SERVER, 'REQUEST_METHOD');

		if ($this->_config['log'])
		{
			Core_Log::instance()->clear()
				->notify(FALSE)
				->status(Core_Log::$MESSAGE)
				->write(sprintf('REST API: method "%s", path: "%s"', $sMethod, $this->path));
		}

		switch ($sMethod)
		{
			// SELECT ITEMS
			case 'GET':
				$mAnswer = $this->_parsePathV1($aPath);

				if ($this->_statusCode == 200)
				{
					$this->_answer = $mAnswer;
				}
				else
				{
					return $mAnswer;
				}
			break;
			// CREATE NEW ITEM
			case 'POST':
				$iCount = count($aPath);
				$newEntity = NULL;

				foreach ($aPath as $key => $path)
				{
					// Last Item
					if ($key == $iCount - 1)
					{
						$sSingularName = $this->_getClassName($path);

						if ($sSingularName === FALSE)
						{
							return FALSE;
						}

						$newEntity = Core_Entity::factory($sSingularName);
					}
					elseif (is_numeric($path))
					{
						if (is_null($oPreviosEntity))
						{
							$this->_error = 'Entity Not Found';
							$this->_statusCode = 403;

							return FALSE;
						}

						$oPreviosEntity = $oPreviosEntity->getById($path);

						if (is_null($oPreviosEntity))
						{
							$this->_error = 'Entity Not Found By PK';
							$this->_statusCode = 403;

							return FALSE;
						}

						// Check access
						if (!$this->_user->checkObjectAccess($oPreviosEntity))
						{
							$this->_error = sprintf('Entity %d. Access Forbidden.', $path);
							$this->_statusCode = 403;

							return FALSE;
						}
					}
					else
					{
						$sSingularName = $this->_getClassName($path);

						if ($sSingularName === FALSE)
						{
							return FALSE;
						}

						$oPreviosEntity = Core_Entity::factory($sSingularName);
					}
				}

				if (is_null($newEntity))
				{
					$this->_error = 'Wrong New Entity Name';
					$this->_statusCode = 422;

					return FALSE;
				}

				$rawData = @file_get_contents("php://input");

				if (strlen($rawData) > 2)
				{
					try {
						$aJson = json_decode($rawData, TRUE);
						foreach ($aJson as $key => $value)
						{
							if (!is_array($value))
							{
								$newEntity->$key = $value;
							}
							else
							{
								$this->_error = sprintf('Wrong JSON data, field %s', htmlspecialchars($key));
								$this->_statusCode = 422;

								return FALSE;
							}
						}

						Core_Event::notify(get_class($this) . '.onBeforeAddNewEntity', $this, array($newEntity, $aJson));

						is_null($oPreviosEntity)
							? $newEntity->save()
							: $oPreviosEntity->add($newEntity);

						$this->_answer = $newEntity->getPrimaryKey();
					}
					catch (Exception $e)
					{
						$this->_error = $e->getMessage();
						$this->_statusCode = 422;

						return FALSE;
					}
				}
				else
				{
					$this->_error = 'Wrong POST data';
					$this->_statusCode = 422;

					return FALSE;
				}
			break;
			// EDIT ITEM
			case 'PUT':
				$sMethodName = NULL;
				foreach ($aPath as $key => $path)
				{
					if ($key == 0)
					{
						$sSingularName = $this->_getClassName($path);

						if ($sSingularName === FALSE)
						{
							return FALSE;
						}

						$oPreviosEntity = Core_Entity::factory($sSingularName);
					}
					elseif ($key == 1 && is_numeric($path))
					{
						$oPreviosEntity = $oPreviosEntity->getById($path);

						// Entity exists
						if (is_null($oPreviosEntity))
						{
							$this->_error = 'Entity Not Found By PK';
							$this->_statusCode = 403;

							return FALSE;
						}

						// Check access
						if (!$this->_user->checkObjectAccess($oPreviosEntity))
						{
							$this->_error = sprintf('Entity %d. Access Forbidden.', $path);
							$this->_statusCode = 403;

							return FALSE;
						}

						$bFound = TRUE;
					}
					elseif ($key == 2)
					{
						$sMethodName = $path;
					}
					else
					{
						$this->_error = sprintf('Unexpected %s', $path);
						$this->_statusCode = 422;

						return FALSE;
					}
				}

				if ($bFound)
				{
					$rawData = @file_get_contents("php://input");

					// CALL METHOD
					if ($sMethodName)
					{
						if (method_exists($oPreviosEntity, $sMethodName))
						{
							$args = array();
							if (strlen($rawData))
							{
								try {
									$aJson = json_decode($rawData, TRUE);

									if (is_array($aJson))
									{
										$args = $aJson;
									}
								}
								catch (Exception $e)
								{
									$this->_error = $e->getMessage();
									$this->_statusCode = 422;

									return FALSE;
								}
							}

							call_user_func_array(array($oPreviosEntity, $sMethodName), $args);

							$this->_statusCode = 201;
							$this->_answer = 'OK';
						}
						else
						{
							$this->_error = sprintf('Udefined method %s()', htmlspecialchars($sMethodName));
							$this->_statusCode = 422;

							return FALSE;
						}
					}
					elseif (strlen($rawData) > 2)
					{
						try {
							$aJson = json_decode($rawData, TRUE);
							foreach ($aJson as $key => $value)
							{
								if (!is_array($value))
								{
									$oPreviosEntity->$key = $value;
								}
								else
								{
									$this->_error = sprintf('Wrong JSON data, field %s', htmlspecialchars($key));
									$this->_statusCode = 422;

									return FALSE;
								}
							}

							Core_Event::notify(get_class($this) . '.onBeforeUpdateEntity', $this, array($oPreviosEntity, $aJson));

							$oPreviosEntity->save();

							// Created
							$this->_statusCode = 201; 
							$this->_answer = 'OK';
						}
						catch (Exception $e)
						{
							$this->_error = $e->getMessage();
							$this->_statusCode = 422;

							return FALSE;
						}
					}
					else
					{
						$this->_error = 'Wrong POST data';
						$this->_statusCode = 422;

						return FALSE;
					}
				}
			break;
			// DELETE ITEM
			case 'DELETE':
				$mAnswer = $this->_parsePathV1($aPath);

				if ($this->_statusCode == 200)
				{
					!is_array($mAnswer)
						&& $mAnswer = array($mAnswer);
				
					$i = 0;
				
					foreach ($mAnswer as $oEntity)
					{
						try {
							is_null($oEntity->getMarksDeleted())
								? $oEntity->delete()
								: $oEntity->markDeleted();
							$i++;
						}
						catch (Exception $e)
						{
							$this->_error = $e->getMessage();
							$this->_statusCode = 422;

							return FALSE;
						}
					}

					//$this->_statusCode = 204; // No Content
					$this->_answer = sprintf('Deleted %d item(s)', $i);
				}
				else
				{
					return $mAnswer;
				}
			break;
			case 'OPTIONS':
				$this->_oCore_Response->header('Allow', 'GET,POST,PUT,DELETE,OPTIONS');

				foreach ($aPath as $key => $path)
				{
					if ($key == 0)
					{
						$sSingularName = $this->_getClassName($path);

						if ($sSingularName === FALSE)
						{
							return FALSE;
						}

						$oPreviosEntity = Core_Entity::factory($sSingularName);
					}
					else
					{
						$this->_error = sprintf('Unexpected %s', $path);
						$this->_statusCode = 422;

						return FALSE;
					}
				}

				// Fields
				$this->_answer['fields'] = $oPreviosEntity->getTableColumns();

				// Methods
				//$this->_answer['methods'] = get_class_methods($oPreviosEntity);
				$oReflectionClass = new ReflectionClass($oPreviosEntity);

				$aMethods = $oReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
				foreach ($aMethods as $oMethod)
				{
					$this->_answer['methods'][] = $oMethod->name;
				}

				// Relations
				if ($oPreviosEntity instanceof Core_ORM)
				{
					$aRelations = $oPreviosEntity->getRelations();

					foreach ($aRelations as $tmpKey => $aRelation)
					{
						$this->_answer['relations'][] = $tmpKey;
					}
				}

			break;
			default:
				$this->_error = sprintf('Method "%s" Not Allowed', htmlspecialchars($sMethod));
				$this->_statusCode = 405;
			break;
		}
	}

	/**
	 * Get class name by $pluralName
	 * @param string $pluralName
	 * @return string|FALSE
	 */
	protected function _getClassName($pluralName)
	{
		//$oCore_Inflection_En = new Core_Inflection_En();
		//$sSingularName = $oCore_Inflection_En->singular($pluralName);
		$sSingularName = Core_Inflection::getSingular($pluralName);

		if (/*$sSingularName == $pluralName && !$oCore_Inflection_En->isPluralIrrigular($pluralName)
			|| */!class_exists(Core_Entity::getClassName($sSingularName)))
		{
			$this->_error = sprintf('Wrong Entity "%s"', $pluralName);
			$this->_statusCode = 422;

			return FALSE;
		}

		return $sSingularName;
	}

	/**
	 * Сheck Content Type by $_SERVER['CONTENT_TYPE']
	 * @return boolean
	 */
	protected function _checkContentType()
	{
		$contentType = Core_Array::get($_SERVER, 'CONTENT_TYPE', 'application/json; charset=utf-8');

		$aContentTypes = array_map('trim', explode(',', $contentType));

		foreach ($aContentTypes as $sContentType)
		{
			$sTmpContentType = array_map('trim', explode(';', $sContentType));

			switch ($sTmpContentType[0])
			{
				case 'application/json':
					$this->_mode = 'json';
				break 2;
				case 'application/xml':
					$this->_mode = 'xml';
				break 2;
			}
		}

		return !is_null($this->_mode);
	}

	/**
	 * Get Request Headers. Use apache_request_headers() or $_SERVER
	 * @return array
	 */
	protected function _getRequestHeaders()
	{
		// PHP 5.4.0: This function became available under FastCGI. Previously, it was supported when PHP was installed as an Apache module or by the NSAPI server module in Netscape/iPlanet/SunONE webservers.
		// PHP 7.3.0: This function became available in the FPM SAPI.
		// FPM available just from 7.3
		if (function_exists('apache_request_headers'))
		{
			$aHeaders = apache_request_headers();
		}
		else
		{
			$aHeaders = array();
			foreach($_SERVER as $key => $val)
			{
				if (substr($key, 0, 5) == 'HTTP_')
				{
					$aKey = array_map('ucfirst',
						explode('_', strtolower(substr($key, 5)))
					);
					$key = implode('-', $aKey);

					$aHeaders[$key] = $val;
				}
			}
		}

		// fix bug with adding REDIRECT_ while using "RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]"
		if (!isset($aHeaders['Authorization']))
		{
			foreach ($_SERVER as $key => $value)
			{
				if (preg_replace('/^(REDIRECT_)*/', '', $key) == 'HTTP_AUTHORIZATION')
				{
					$aHeaders['Authorization'] = $_SERVER[$key];
					break;
				}
			}
		}

		return $aHeaders;
	}

	/**
	 * Chech Authorization
	 */
	protected function _checkAuthorization()
	{
		$this->_user = NULL;

		$aHeaders = $this->_getRequestHeaders();
		if (isset($aHeaders['Authorization']))
		{
			if (preg_match('/Bearer ([0-9a-zA-Z]+)/', $aHeaders['Authorization'], $matches))
			{
				if (isset($matches[1]))
				{
					$sCurrentDate = Core_Date::timestamp2sql(time());

					$oRestapi_Tokens = Core_Entity::factory('Restapi_Token');
					$oRestapi_Tokens->queryBuilder()
						->where('token', '=', $matches[1])
						->where('active', '=', 1)
						->where('datetime', '<=', $sCurrentDate)
						->open()
							->where('expire', '=', '0000-00-00 00:00:00')
							->setOr()
							->where('expire', '>', $sCurrentDate)
						->close()
						->limit(1);

					$aRestapi_Tokens = $oRestapi_Tokens->findAll(FALSE);
					if (isset($aRestapi_Tokens[0]))
					{
						$oRestapi_Token = $aRestapi_Tokens[0];

						if (!$oRestapi_Token->https || Core::httpsUses())
						{
							if ($oRestapi_Token->user_id)
							{
								$oUser = $oRestapi_Token->User;
								if ($oUser->active && !$oUser->read_only && !$oUser->dismissed)
								{
									$this->_user = $oUser;
								}
							}
						}
					}
				}
			}
		}

		return !is_null($this->_user);
	}
}