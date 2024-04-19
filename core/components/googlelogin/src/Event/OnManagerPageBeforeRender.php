<?php

namespace MODX\GoogleLogin\Event;

use MODX\Revolution\modUser;
use MODX\Revolution\modUserSetting;
use MODX\Revolution\modX;

class OnManagerPageBeforeRender extends Event
{
    public function run()
    {
        // System Wide
        $controller = $this->sp['controller'];
        $action = $controller->config['controller'] ?? null;
        /** @var modUser $user */
        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            return false;
        }
        $userSettings = $user->getSettings();
        $glogId = $userSettings['glog_id'] ?? null;
        $this->modx->controller->addJavascript($this->glog->getOption('jsUrl') . 'googlelogin.js');
        if ($this->glog->getOption('disable_regular_login') && empty($glogId)) {
            $this->modx->controller->addLexiconTopic('googlelogin:default');
            $this->modx->controller->addLastJavascript($this->glog->getOption('jsUrl') . 'warning.helper.js');
        }
        if ($action === 'security/profile') {
            if ($_GET['glog'] === 'disconnect') {
                $glogSetting = $this->modx->getObject(modUserSetting::class, [
                    'key' => 'glog_id',
                    'user' => $user->id,
                ]);
                if ($glogSetting) {
                    $glogSetting->remove();
                }
                $this->modx->sendRedirect($this->modx->getOption('manager_url') . '?a=security/profile');
            }
            $this->modx->controller->addLexiconTopic('googlelogin:default');
            $loginUrl = null;
            $disconnectUrl = null;
            if (empty($glogId)) {
                $this->glog->loadClient();
                if (empty($this->glog->client)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, 'GoogleLogin client not loaded');
                    return;
                }
                $loginUrl = $this->glog->client->createAuthUrl();
            } else {
                $disconnectUrl = $this->modx->getOption('manager_url') . '?a=security/profile&glog=disconnect';
            }
            $this->modx->controller->addHtml('<script type="text/javascript">
            Ext.onReady(function() {
                googlelogin.config = ' . $this->modx->toJSON([
                'glogId' => $glogId,
                'loginUrl' => $loginUrl,
                'disconnectUrl' => $disconnectUrl,
            ]) . ';
            });
            </script>');
            $this->modx->controller->addLastJavascript($this->glog->getOption('jsUrl') . 'profile.helper.js');
        }
    }
}
