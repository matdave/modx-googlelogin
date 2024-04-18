<?php

namespace MODX\GoogleLogin\Event;

use MODX\GoogleLogin\Service;

use MODX\Revolution\modX;

abstract class Event
{
    /** @var modX */
    protected $modx;

    /** @var Service */
    protected $glog;

    /** @var array */
    protected $sp = [];

    public function __construct(Service &$glog, array $scriptProperties)
    {
        $this->glog =& $glog;
        $this->modx =& $this->glog->modx;
        $this->sp = $scriptProperties;
    }

    abstract public function run();

    protected function getOption($key, $default = null, $skipEmpty = false)
    {
        return $this->modx->getOption($key, $this->sp, $default, $skipEmpty);
    }
}
