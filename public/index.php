<?php
header('content-type:text/html;charset=utf-8');
ini_set('display_errors', 1);
define("APP_PATH", realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */
$app = new Yaf\Application(APP_PATH . "/conf/application.ini");
$app->bootstrap()->run();