<?php
require_once("ipgeobase.php");

$gb = new IPGeoBase();
$data = array();

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$record = $gb->getRecord($ip);
$city = null;
if($record) {
  if(!empty($record['city'])) {
    $city = $record['city'];
  }
}
$data['city'] = $city;

header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
echo json_encode($data);
