<?php

namespace MODX\GoogleLogin\Callback;

use Google\Service\Oauth2;
use Google\Service\Oauth2\Userinfo;
use MODX\GoogleLogin\Service;
use MODX\Revolution\Mail\modMail;
use MODX\Revolution\Mail\modPHPMailer;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserGroup;
use MODX\Revolution\modUserGroupRole;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\modUserSetting;
use MODX\Revolution\modX;

class Callback
{
    protected Service $glog;
    protected array $token;
    public modX $modx;
    public function __construct($glog)
    {
        $this->glog = $glog;
        $this->modx = $glog->modx;
        $this->token = $_SESSION['glog_token'] ?? [];
        $this->glog->loadClient();
    }

    public function handleCallback(): void
    {
        if (empty($this->glog->client)) {
            $this->sendManager();
            return;
        }
        if (isset($_GET['code'])) {
            $token = $this->glog->client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (!isset($token['error'])) {
                $this->token = $_SESSION['glog_token'] = $this->glog->client->getAccessToken();
                $this->loginUser();
            }
        }
        if (!empty($this->token)) {
            $this->glog->client->setAccessToken($this->token);
            if ($this->glog->client->isAccessTokenExpired()) {
                $token = $this->glog->client->fetchAccessTokenWithRefreshToken($this->token['refresh_token']);
                if (!isset($token['error'])) {
                    $this->token = $_SESSION['glog_token'] = $this->glog->client->getAccessToken();
                    $this->loginUser();
                    $this->sendManager();
                }
            }
            $this->loginUser();
        }
        $this->sendManager();
    }

    private function sendManager($success = false, $params = []): void
    {
        $extParams = '';
        foreach ($params as $key => $value) {
            $extParams .= "&$key=$value";
        }
        $this->modx->sendRedirect($this->modx->getOption('manager_url'). '?glog=' . ($success ? 'success' : 'fail') . $extParams);
    }

    private function loginUser(): void
    {
        $oath = new Oauth2($this->glog->client);
        try {
            $user = $oath->userinfo->get();
        } catch (\Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'GoogleLogin: ' . $e->getMessage());
            $this->sendManager();
            return;
        }
        if (empty($user)) {
            $this->sendManager();
            return;
        }
        // search for user setting
        $userSetting = $this->modx->getObject(modUserSetting::class,
            ['key' => 'glog_id', 'value' => $user->id]
        );
        if (!empty($userSetting)) {
            $this->loginUserWithID($userSetting->get('user'));
            return;
        } elseif ($this->modx->user->isAuthenticated('mgr')) {
            $this->addUserSetting($this->modx->user->get('id'), $user->id);
            $this->sendManager(true);
            return;
        }
        if ($this->modx->getOption('googlelogin.allow_match_by_email', null, false)) {
            $userByEmail = $this->modx->getObject(modUserProfile::class, ['email' => $user->email]);
            if (!empty($userByEmail)) {
                $this->addUserSetting($userByEmail->get('internalKey'), $user->id);
                $this->loginUserWithID($userByEmail->get('internalKey'));
                return;
            }
        }
        if ($this->modx->getOption('googlelogin.allow_signup', null, false)) {
            $this->signupUser($user);
        }
        $this->sendManager();
    }

    private function loginUserWithID($id)
    {
        $user = $this->modx->getObject(modUser::class, $id);
        if (!empty($user)) {
            $this->loadUser($user);
        }
    }

    private function loadUser(modUser $user): void
    {
        $targets = explode(',', $this->modx->getOption('principal_targets', null,
        'MODX\\Revolution\\modAccessContext,MODX\\Revolution\\modAccessResourceGroup,MODX\\Revolution\\modAccessCategory,MODX\\Revolution\\Sources\\modAccessMediaSource,MODX\\Revolution\\modAccessNamespace'));
        array_walk($targets, 'trim');
        if ($user->get('active') === 0 || $user->get('blocked') === 1) {
            $this->sendManager();
            return;
        }
        $this->modx->user = $user;
        $this->modx->user->addSessionContext('mgr');
        $this->modx->user->loadAttributes($targets, 'mgr', true);
        $this->sendManager(true);
    }

    private function signupUser(Userinfo $user): void
    {
        $domains = $this->modx->getOption('googlelogin.allow_signup_domains', null, '');
        $domains = explode(',', $domains);
        array_walk($domains, 'trim');
        if (!empty($domains) && !in_array($user->hd, $domains)) {
            $this->sendManager();
            return;
        }
        $active = (int) $this->modx->getOption('googlelogin.allow_signup_active', null, 0);
        $defaultGroup = $this->modx->getOption('googlelogin.default_group', null, null);
        $defaultRole = $this->modx->getOption('googlelogin.default_role', null, 'Member');
        $groupID = 0;
        $roleID = 0;
        if (!empty($defaultGroup)) {
            $group = $this->modx->getObject(modUserGroup::class, ['name' => $defaultGroup]);
            if (!empty($group)) {
                $groupID = $group->get('id');
            }
        }
        if (!empty($groupID)) {
            $role = $this->modx->getObject(modUserGroupRole::class, ['name' => $defaultRole]);
            if (!empty($role)) {
                $roleID = $role->get('id');
            }
        }
        $newUser = $this->modx->newObject(modUser::class);
        $newUser->fromArray([
            'username' => $user->getEmail(),
            'active' => $active,
            'blocked' => 0,
            'remote_key' => $user->id,
            'primary_group' => $groupID,
        ]);
        $newUser->save();
        if ($groupID && $roleID) {
            $newUser->joinGroup($groupID, $roleID);
        }
        $this->addUserSetting($newUser->get('id'), $user->id);
        $this->addUserSetting($newUser->get('id'), $user->locale, 'manager_language');
        $this->modx->newObject(modUserProfile::class, [
            'internalKey' => $newUser->get('id'),
            'fullname' => $user->givenName . ' ' . $user->familyName,
            'email' => $user->getEmail(),
            'photo' => $user->picture,
        ])->save();
        $notify = $this->modx->getOption('googlelogin.allow_signup_notify', null, '');
        $notify = explode(',', $notify);
        array_walk($notify, 'trim');
        if (!empty($notify)) {
            $body = $this->modx->lexicon('googlelogin.email.body', [
                'site_name' => $this->modx->getOption('site_name'),
                'email' => $user->getEmail(),
            ]);
            $subject = $this->modx->lexicon('googlelogin.email.subject');
            $mail = new modPHPMailer($this->modx);
            $mail->set(modMail::MAIL_BODY, $body);
            $mail->set(modMail::MAIL_FROM, $this->modx->getOption('emailsender'));
            $mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('site_name'));
            $mail->set(modMail::MAIL_SUBJECT, $subject);
            foreach ($notify as $email) {
                $mail->address('to', $email);
            }
            $mail->address('reply-to', $this->modx->getOption('emailsender'));
            $mail->setHTML(true);
            if (!$mail->send()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . print_r($mail->mailer->ErrorInfo, true));
            }
            $mail->reset();
        }
        if ($active) {
            $this->loadUser($newUser);
        } else {
            $this->sendManager(true, ['signup' => '1']);
        }
    }

    private function addUserSetting(int $modxId, $value, $key = 'glog_id'): void
    {
        $setting = $this->modx->newObject(modUserSetting::class);
        $setting->set('user', $modxId);
        $setting->set('key', $key);
        $setting->set('value', $value);
        $setting->save();
    }
}