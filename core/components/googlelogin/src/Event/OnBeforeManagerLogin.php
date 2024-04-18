<?php

namespace MODX\GoogleLogin\Event;

class OnBeforeManagerLogin extends Event
{
    public function run()
    {
        $this->glog->loadClient();
        if (empty($this->glog->client)) {
            return;
        }
        $this->modx->event->output(
            $this->glog->getOption('disable_regular_login') ?
                $this->modx->lexicon('googlelogin.disable_regular_login') :
                null
        );
    }
}