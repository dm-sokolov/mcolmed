<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 12.08.13
 * Time: 17:37
 * To change this template use File | Settings | File Templates.
 */
include_once('lib/helpers.php');

if($_SERVER['REMOTE_ADDR'] != get_your_ip()) {

    include_once('lib/PageCmd.php');
    include_once('lib/PreOpt.php');
    include_once('lib/OptProcessor.php');
    include_once('lib/HrefsRewrite.php');

    $uri = substr($_SERVER['REQUEST_URI'], 1);
    $opt_cmd = new PageCmd($uri);
    $pre_opt = new PreOpt($opt_cmd, false);
    $pre_opt->process();

    if($uri != substr($_SERVER['REQUEST_URI'], 1)) {
        $uri = substr($_SERVER['REQUEST_URI'], 1);
        $opt_cmd = new PageCmd($uri);
    }

    $headers = array();

    foreach($_SERVER as $key => $value) {
        if(substr($key, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
        }
    }

    $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $ch = curl_init($url);
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
    }
    $cookie_text = '';
    $cookie_array = array();
    foreach($_COOKIE as $key=>$value) {
        $cookie_array[]=$key.'='.$value;
    }
    $cookie_text = implode('; ', $cookie_array);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie_text);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $result = curl_exec($ch);

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($result, 0, $header_size);
    $text = substr($result, $header_size);

    $headers = explode("\n", $headers);
    foreach($headers as $header) {
        $header = str_replace("\r", '', $header);
        if($header != '') {
            foreach(array(
                        'HTTP/',
                        'Date:',
                        'Content-Type:',
                        'Expires:',
                        'Cache-Control:',
                        'Pragma:',
                        'Set-Cookie:',
                        "Accept-Charset:",
                        'Location:',

            ) as $value) if(stripos($header, $value) !== FALSE) {
                header($header);
            }
        }
    }
    if($_SERVER['REMOTE_ADDR'] == '82.193.139.18') {
        header('Y-Optimized: opt_tng, random_string:'.md5(rand()));
    }

    $opt_processor = new OptProcessor($opt_cmd, $text);

    echo $opt_processor->process();
    die;
}