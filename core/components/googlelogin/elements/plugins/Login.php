<?php

use xPDO\xPDO;

$scriptProperties = $scriptProperties ?? [];

$glog = new MODX\GoogleLogin\Service($modx, $scriptProperties);

$className = "\\MODX\\GoogleLogin\\Event\\{$modx->event->name}";
if (class_exists($className)) {
    /** @var \MODX\GoogleLogin\Event\Event $event */
    $event = new $className($glog, $scriptProperties);
    $event->run();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}
return;