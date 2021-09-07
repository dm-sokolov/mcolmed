<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 25.07.13
 * Time: 14:28
 * To change this template use File | Settings | File Templates.
 */

include_once('lib/helpers.php');
include_once('lib/PageCmd.php');
include_once('lib/OptProcessor.php');
include_once('lib/HrefsRewrite.php');

global $opt_cmd;

$opt_processor = new OptProcessor($opt_cmd, ob_get_clean());

echo $opt_processor->process();