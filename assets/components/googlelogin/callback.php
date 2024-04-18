<?php

require_once dirname(__DIR__, 3) . '/config.core.php';
require_once MODX_CORE_PATH . "vendor/autoload.php";

$modx = new MODX\Revolution\modX();
$modx->initialize('web');

$glog = new \MODX\GoogleLogin\Service($modx);
$callback = new \MODX\GoogleLogin\Callback\Callback($glog);

$callback->handleCallback();