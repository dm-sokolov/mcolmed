<?php
/**
* multiload get properties
* 
* @author KAD Systems (©) 2014	
* @date
*/

require_once('../../../bootstrap.php');

header('Content-Type: text/html; charset=UTF-8');

$module_name = "multiload";
Core_Auth::authorization($module_name);

$informationsystem_id = Core_Array::getGet('infsysid', 0);
$shop_id = Core_Array::getGet('shopid', 0);
$shop_group_id = Core_Array::getGet('shopgroup', 0);

$oMultiloadController = Multiload_Controller::instance();

if ($informationsystem_id)
{
	$props = $oMultiloadController->getInfomationSystemItemProperties($informationsystem_id);
}
if ($shop_id)
{
	$props = $oMultiloadController->getShopItemProperties($shop_id, $shop_group_id);
}
echo '<option value="0">..</option>';
foreach( $props as $id => $name)
{
	echo '<option value="'.$id.'">'.$name.'</option>';
}
?>