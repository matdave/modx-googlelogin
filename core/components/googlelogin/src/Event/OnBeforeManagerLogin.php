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
        if ($this->glog->getOption('disable_regular_login')) {
            $loginURL = $this->glog->client->createAuthUrl();
            if (!empty($loginURL)) {
                $this->modx->sendRedirect($loginURL);
            }
            $this->modx->event->output(
                    $this->modx->lexicon('googlelogin.disable_regular_login')
            );
        }
    }
}