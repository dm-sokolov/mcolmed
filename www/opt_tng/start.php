<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 25.07.13
 * Time: 14:28
 * To change this template use File | Settings | File Templates.
 */
//test comment
include_once('lib/helpers.php');
include_once('lib/PageCmd.php');
include_once('lib/PreOpt.php');
include_once('lib/HrefsRewrite.php');

global $opt_cmd;

if(!isset($opt_cmd)) {
    $opt_cmd = new PageCmd(substr($_SERVER['REQUEST_URI'], 1));

    $pre_opt = new PreOpt($opt_cmd, function_exists('curl_init'));

    $pre_opt->process();

} else {
    $opt_cmd = new PageCmd(substr($_SERVER['REQUEST_URI'], 1));
}
ob_start();
