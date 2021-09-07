<?php
/**
* Модуль мультизагрузки картинок
* Copyright © 2010-2011 ООО "Интернет-Эксперт" (Internet-Expert LLC), http://www.internet-expert.ru
*
* @author Internet-Expert LLC, Roman Kopylov, Alexey Vasiliev
*/
header('Content-Type: text/html; charset=UTF-8');

require_once('../../../main_classes.php');

// Проверка авторизации пользователя (для данного модуля)
if(!defined('IS_ADMIN_PART')){
    $admin = new Admin();
    $admin->admin_session_valid('iexmultiload');
}

$kernel = & singleton('kernel');

$kernel->LoadModules(!isset($_REQUEST['JsHttpRequest']));

$is = & singleton('InformationSystem');

if(isset($_GET['infsysid'])){
    $infsysid = to_int($_GET['infsysid']);
    $props = array();
    if($infsysid != 0){
        $res = $is->GetAllInformationItemsPropertys($infsysid);
		while ($row = mysql_fetch_assoc($res)) {
			if ($row['information_propertys_type'] == 2) {
				echo '<input type="checkbox" id="isprop'.$row['information_propertys_id'].'" class="isprop"/><label for="isprop'.$row['information_propertys_id'].'">'.$row['information_propertys_name'].'</label><br/>';
			}		
		}
    }
}

?>