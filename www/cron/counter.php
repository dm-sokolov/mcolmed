<?php

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

$count = 4;

$oConstant = Core_Entity::factory('Constant')->getByName('COUNTER_MAN');
if($oConstant)
{
	$x = substr($oConstant->value, 1);
	$x = ($x + $count);
	if(strlen($x) < 7) $x = "0" . $x ;
	$oConstant->value = $x;
	$oConstant->save();
}
