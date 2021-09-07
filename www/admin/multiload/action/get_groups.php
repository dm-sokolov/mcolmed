<?php
/**
* multiload get groups
* 
* @author KAD Systems (©) 2014	
* @date
*/

require_once('../../../bootstrap.php');

header('Content-Type: text/html; charset=UTF-8');

$module_name = "multiload";
Core_Auth::authorization($module_name);

$oMultiloadController = Multiload_Controller::instance();

if (Core_Array::getGet('infsysid'))
{
	$infsysid = Core_Array::getGet('infsysid', 0);
	$groups = $oMultiloadController->getInfomationSystemGroups($infsysid);
}

if (Core_Array::getGet('shopid'))
{
	$infsysid = Core_Array::getGet('shopid');
	$groups = $oMultiloadController->getShopGroups($infsysid);
}

echo '<option value="0">..</option>';
foreach( $groups as $id => $name)
{
	echo '<option value="'.$id.'">'.$name.'</option>';
}
?>