<?php

namespace MODX\GoogleLogin;

use Google\Client;
use MODX\Revolution\modX;

class Service
{
    public $modx = null;
    public $namespace = 'googlelogin';
    public $cache = null;
    public $options = [];
    public Client $client;

    public function __construct(modX &$modx, array $options = [])
    {
        $this->modx =& $modx;

        $corePath = $this->getOption(
            'core_path',
            $options,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/'.$this->namespace.'/'
        );
        $assetsPath = $this->getOption(
            'assets_path',
            $options,
            $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/'.$this->namespace.'/'
        );
        $assetsUrl = $this->getOption(
            'assets_url',
            $options,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/'.$this->namespace.'/'
        );

        /* loads some default paths for easier management */
        $this->options = array_merge([
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
        ], $options);

        $this->modx->lexicon->load($this->namespace.':default');
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = array(), $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options !== null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    public function loadClient()
    {
        $clientId = $this->getOption('client_id');
        $clientSecret = $this->getOption('client_secret');
        if (empty($clientId) || empty($clientSecret)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Google Login: Client ID and Client Secret are required.');
            return;
        }
        $redirectUri = $this->options['assetsUrl'].'callback.php';
        // check if redirectUri is absolute
        if (strpos($redirectUri, 'http') !== 0) {
            $redirectUri = rtrim($this->modx->getOption('site_url'), '/') . '/' . ltrim($redirectUri, '/');
        }
        $this->client = new Client();
        $this->client->setApplicationName($this->modx->getOption('site_name') . ' Google Login');
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri($redirectUri);
        $this->client->setScopes(['email', 'profile']);
        $this->client->setPrompt('select_account');
    }
}