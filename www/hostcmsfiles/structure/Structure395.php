<?php

	!Core_Auth::logged() && die();
	
	
	
	exit();

	$oRevisions = Core_Entity::factory('Revision');

	$oRevisions
		->queryBuilder()
			->where('model', '=', 'lib')
			->where('entity_id', '=', 1)
			->where('datetime', '>=', '2017-09-25 00:00:00')
			->orderBy('datetime','ASC')
	;

	$aRevisions = $oRevisions->findAll();

	foreach($aRevisions as $oRevision)
	{
		$aBackup = json_decode($oRevision->value, TRUE);
		echo "<b>" . strftime("%d.%m.%Y %H:%M:%S", Core_Date::sql2timestamp($oRevision->datetime)) . "</b>";

		echo("<pre>");
		print_r(htmlspecialchars($aBackup['lib_config']));
		echo("</pre>");

		echo("<hr/>");
	}

	