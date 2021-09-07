<?php
if (preg_match("/^\/price\/.+$/is",$_SERVER['REQUEST_URI'])){

    header('Location: /price/', true, '301');
    die();

}