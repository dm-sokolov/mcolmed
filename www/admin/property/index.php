<?php
/**
 * Properties.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

if (Core_Auth::logged())
{
	Core_Auth::checkBackendBlockedIp();

	if (!is_null(Core_Array::getGet('autocomplete'))
		&& !is_null(Core_Array::getGet('show_dir'))
		&& !is_null(Core_Array::getGet('queryString'))
	)
	{
		$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString')))));
		$modelName = strval(Core_Array::getGet('linkedObjectName'));
		$id = intval(Core_Array::getGet('linkedObjectId'));
		$linkedObject = Core_Entity::factory($modelName, $id);

		$aJSON = array();

		$aJSON = array(
			'id' => 0,
			'label' => Core::_('Property_Dir.root'),
		);

		// Ends on _Property_List
		if (preg_match('/_Property_List$/', $linkedObject))
		{
			if (strlen($sQuery))
			{
				$sQueryLike = '%' . str_replace(' ', '%', $sQuery) . '%';

				$oProperty_Dirs = $linkedObject->Property_Dirs;
				$oProperty_Dirs->queryBuilder()
					->where('property_dirs.name', 'LIKE', $sQueryLike)
					->limit(Core::$mainConfig['autocompleteItems']);

				$aProperty_Dirs = $oProperty_Dirs->findAll(FALSE);

				foreach ($aProperty_Dirs as $oProperty_Dir)
				{
					$aParentDirs = array();

					$aTmpDir = $oProperty_Dir;

					// Добавляем все директории от текущей до родителя.
					do {
						$aParentDirs[] = $aTmpDir->name;
					} while ($aTmpDir = $aTmpDir->getParent());

					$sParents = implode(' → ', array_reverse($aParentDirs));

					$aJSON[] = array(
						'id' => $oProperty_Dir->id,
						'label' => $sParents . ' [' . $oProperty_Dir->id . ']',
					);
				}
			}
		}
		else
		{
			$aJSON['error'] = 'Wrong linkedObjectName';
		}

		Core::showJson($aJSON);
	}
}