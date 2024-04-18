<?php

namespace MODX\GoogleLogin\Event;

use MODX\Revolution\modX;

class OnManagerLoginFormRender extends Event
{
    public function run()
    {
        $this->glog->loadClient();
        if (empty($this->glog->client)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoogleLogin client not loaded');
            return;
        }
        $this->modx->controller->addLexiconTopic('googlelogin:default');
        $css = '<link href="'. $this->glog->options['cssUrl'] .'googlelogin.css" rel="stylesheet"/>';
        $message = '';
        if (isset($_GET['glog']) && in_array($_GET['glog'], ['success', 'fail'])) {
            $message = $this->modx->lexicon('googlelogin.glog_' . htmlentities($_GET['glog']));
        }
        if (isset($_GET['signup'])) {
            $message = $this->modx->lexicon('googlelogin.glog_signup');
        }
        $loginURL = $this->glog->client->createAuthUrl();
        $this->modx->event->_output = "$css $message <a href=$loginURL class=\"c-button google\" >".$this->modx->lexicon('googlelogin.login_with_google')."</a>";
    }
}