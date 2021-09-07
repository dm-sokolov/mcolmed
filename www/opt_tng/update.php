<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 25.07.13
 * Time: 15:01
 * To change this template use File | Settings | File Templates.
 */
include_once('lib/helpers.php');
include_once('lib/PageCmd.php');
include_once('lib/HrefsRewrite.php');

$push = true;


if($_SERVER['REQUEST_METHOD'] == 'POST'  && $_SERVER['REMOTE_ADDR'] == '79.172.49.201') {
    foreach($_POST as $url=>$commands) {
        $cmd = new PageCmd(false, $url);
        $cmd->set($commands);
        $cmd->save();
    }
} else {

    if (isset($_GET['url'])) {
        $cmd = new PageCmd(rawurldecode($_GET['url']));
        if(isset($_GET['cmd']) && $_SERVER['REMOTE_ADDR'] == '79.172.49.201') {
            $cmd->set(rawurldecode($_GET['cmd']));
            $cmd->save();
        } else {
            $cmd->get();
            $cmd->save();
        }
    } else {
        for($i = 0; $i<2; $i++) {
            $project_folder = md5(normalize_domain(preg_replace('@:80$@i', '', $_SERVER['HTTP_HOST']), 1, 1, 1));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://opt.mediasite.ru/opt_tng/update/update_all.php?project_folder='.$project_folder);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $urls = unserialize(curl_exec($ch));
            foreach($urls as $url=>$commands) {
                $cmd = new PageCmd(false, $url);
                $cmd->set($commands);
                $cmd->save();
            }
        }
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST'  && $_SERVER['REMOTE_ADDR'] == '79.172.49.201') {
        foreach($_POST as $url=>$commands) {
            $cmd = new PageCmd(false, $url);
            $cmd->set($commands);
            $cmd->save();
        }
    }
}
echo 'update finished';