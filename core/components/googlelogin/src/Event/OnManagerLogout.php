<?php

namespace MODX\GoogleLogin\Event;

class OnManagerLogout extends Event
{
    public function run()
    {
        if(isset($_SESSION['glog_token'])) {
            $this->glog->loadClient();
            if (empty($this->glog->client)) {
                return;
            }
            $this->glog->client->setAccessToken($_SESSION['glog_token']);
            $this->glog->client->revokeToken();
            unset($_SESSION['glog_token']);
        }
    }
}