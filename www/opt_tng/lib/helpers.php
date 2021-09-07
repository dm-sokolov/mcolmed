<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 26.07.13
 * Time: 15:25
 * To change this template use File | Settings | File Templates.
 */
include_once('custom_opt.php');

//Функция, приводящая любой входящий домен к нормальному виду "http://www.xxx.yy/".
function normalize_domain($url, $no_http = 0, $no_www = 0, $no_slash = 0) {

    //Т.к. эта функция по нормализации только домена, а не целого урла, отсекаем гет-стринг, если находим.
    if (strstr($url, '?')) {
        $url = preg_replace('@\?(.*)$@i', '', $url);
    }

    //Если не находим части нормального домена, ебошим их вокруг него.
    if (!strstr($url, 'www.')) $url = 'www.' . $url;
    if (!strstr($url, 'http://')) $url = 'http://' . $url;
    if (substr($url, -1, 1) != '/') $url .= '/';

    //Но если функция вызвана с необязательными параметрами, отсекаем требуемые части снова.
    //Почему сначала ставить их, а потом отрезать? Да потому что это будет работать быстрее, чем если воротить вложенные условия.
    //Плюс, это проще читать.
    if ($no_http) $url = str_replace('http://', '', $url);
    if ($no_www) $url = str_replace('www.', '', $url);
    if ($no_slash) if (substr($url, -1, 1) == '/') $url = preg_replace('@/$@i', '', $url);

    return $url;

}

function vladson_crypt($str) {

    $keys = array(1, 2, 3);
    $str = str_split($str);
    foreach ($str as $key => $val) {
        array_push($keys, (array_sum($keys) & 255) * 2);
        if (end($keys) > 255)
            array_push($keys, array_pop($keys) - 255);
        array_shift($keys);
        $str[$key] = chr(ord($val) ^ end($keys));
    }
    return implode($str);

}

function get_your_ip() {
    if(!defined('YOUR_IP')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/opt_tng/your_ip.php');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $your_ip = curl_exec($ch);
        define('YOUR_IP', $your_ip);
        return $your_ip;
    } else {
        return YOUR_IP;
    }
}

function my_strtr($str, $replace_pairs, $third_arg=NULL) {
    foreach($replace_pairs as $from=>$to) {
        $str = str_replace($from, $to, $str);
    }
    return $str;
}