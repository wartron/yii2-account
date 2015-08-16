<?php

namespace wartron\yii2account\clients;

use yii\authclient\OAuth1;
use  wartron\yii2account\clients\ClientInterface;

/**
 * BitBucket allows authentication via BitBucket OAuth.
 *
 * In order to use BitBucket OAuth you must register your application at <https://bitbucket.org/account/>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'bitbucket' => [
 *                 'class' => 'app\bitbucket\BitBucket',
 *                 'consumerKey' => 'bitbucket_key',
 *                 'consumerSecret' => 'bitbucket_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://developer.github.com/v3/oauth/
 * @see https://github.com/settings/applications/new
 *
 * @author Will Wharton <w@wartron.com>
 * @since 2.0
 */
class BitBucket extends OAuth1 implements ClientInterface
{



   /**
     * @inheritdoc
     */
    public $authUrl = 'https://bitbucket.org/api/1.0/oauth/authenticate';
    /**
     * @inheritdoc
     */
    public $requestTokenUrl = 'https://bitbucket.org/api/1.0/oauth/request_token';
    /**
     * @inheritdoc
     */
    public $requestTokenMethod = 'POST';
    /**
     * @inheritdoc
     */
    public $accessTokenUrl = 'https://bitbucket.org/api/1.0/oauth/access_token';
    /**
     * @inheritdoc
     */
    public $accessTokenMethod = 'POST';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://bitbucket.org/api/1.0';



    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        return $this->api('user', 'GET');
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'bitbucket';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'BitBucket';
    }


    /** @inheritdoc */
    public function getEmail()
    {
        return isset($this->getUserAttributes()['email'])
            ? $this->getUserAttributes()['email']
            : null;
    }
    /** @inheritdoc */
    public function getUsername()
    {
        return isset($this->getUserAttributes()['login'])
            ? $this->getUserAttributes()['login']
            : null;
    }

}