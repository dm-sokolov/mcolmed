<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 11.10.13
 * Time: 16:54
 * To change this template use File | Settings | File Templates.
 */

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://opt.mediasite.ru/opt_tng/update/upgrade.php');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $files = unserialize(curl_exec($ch));
} else {
    if($_SERVER['REMOTE_ADDR'] == '79.172.49.201') {
        $files = $_POST;
    } else {
        die();
    }
}

foreach($files as $path=>$contents) {
    file_put_contents($path, $contents);
}

echo 'upgrade finished';