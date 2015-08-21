<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wartron\yii2account\models;

use wartron\yii2account\clients\ClientInterface;
use wartron\yii2account\Finder;
use wartron\yii2account\models\query\AccountNetworkQuery;
use wartron\yii2account\Module;
use Yii;
use yii\authclient\ClientInterface as BaseClientInterface;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property integer $id          Id
 * @property integer $user_id     User id, null if account is not bind to user
 * @property string  $provider    Name of service
 * @property string  $client_id   Account id
 * @property string  $data        Account properties returned by social network (json encoded)
 * @property string  $decodedData Json-decoded properties
 * @property string  $code
 * @property integer $created_at
 * @property string  $email
 * @property string  $username
 *
 * @property User    $user        User that this account is connected for.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class AccountNetwork extends ActiveRecord
{
    /** @var Module */
    protected $module;

    /** @var Finder */
    protected static $finder;

    /** @var */
    private $_data;

    /** @inheritdoc */
    public function init()
    {
        $this->module = Yii::$app->getModule('account');
    }

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%account_network}}';
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->hasOne($this->module->modelMap['Account'], ['id' => 'account_id']);
    }

    /**
     * @return bool Whether this social account is connected to account.
     */
    public function getIsConnected()
    {
        return $this->account_id != null;
    }

    /**
     * @return mixed Json decoded properties.
     */
    public function getDecodedData()
    {
        if ($this->_data == null) {
            $this->_data = json_decode($this->data);
        }

        return $this->_data;
    }

    /**
     * Returns connect url.
     * @return string
     */
    public function getConnectUrl()
    {
        $code = Yii::$app->security->generateRandomString();
        $this->updateAttributes(['code' => md5($code)]);

        return Url::to(['/account/registration/connect', 'code' => $code]);
    }

    public function connect(Account $account)
    {
        return $this->updateAttributes([
            'username' => null,
            'email'    => null,
            'code'     => null,
            'account_id'  => $account->id,
        ]);
    }

    /**
     * @return AccountQuery
     */
    public static function find()
    {
        return Yii::createObject(AccountNetworkQuery::className(), [get_called_class()]);
    }

    public static function create(BaseClientInterface $client)
    {
        /** @var Account $account */
        $accountNetwork = Yii::createObject([
            'class'      => static::className(),
            'provider'   => $client->getId(),
            'client_id'  => $client->getUserAttributes()['id'],
            'data'       => json_encode($client->getUserAttributes()),
        ]);

        if ($client instanceof ClientInterface) {
            $accountNetwork->setAttributes([
                'username' => $client->getUsername(),
                'email'    => $client->getEmail(),
            ], false);
        }

        if (($account = static::fetchAccount($accountNetwork)) instanceof Account) {
            $accountNetwork->account_id = $account->id;
        }

        $accountNetwork->save(false);

        return $accountNetwork;
    }

    /**
     * Tries to find an account and then connect that account with current account.
     *
     * @param BaseClientInterface $client
     */
    public static function connectWithAccount(BaseClientInterface $client)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('danger', Yii::t('account', 'Something went wrong'));

            return;
        }

        $accountNetwork = static::fetchAccountNetwork($client);

        if ($accountNetwork->account === null) {
            $accountNetwork->link('account', Yii::$app->user->identity);
            Yii::$app->session->setFlash('success', Yii::t('account', 'Your account has been connected'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('account', 'This account has already been connected to another user'));
        }
    }

    /**
     * Tries to find account, otherwise creates new account.
     *
     * @param BaseClientInterface $client
     *
     * @return Account
     * @throws \yii\base\InvalidConfigException
     */
    protected static function fetchAccountNetwork(BaseClientInterface $client)
    {
        $account = static::getFinder()->findAccountNetwork()->byClient($client)->one();

        if (null === $account) {
            $account = Yii::createObject([
                'class'      => static::className(),
                'provider'   => $client->getId(),
                'client_id'  => $client->getUserAttributes()['id'],
                'data'       => json_encode($client->getUserAttributes()),
            ]);
            $account->save(false);
        }

        return $account;
    }

    /**
     * Tries to find account or create a new one.
     *
     * @param Account $account
     *
     * @return User|bool False when can't create account.
     */
    protected static function fetchAccount(AccountNetwork $accountNetwork)
    {
        $account = static::getFinder()->findAccountByEmail($accountNetwork->email);

        if (null !== $account) {
            return $account;
        }

        $account = Yii::createObject([
            'class'    => Account::className(),
            'scenario' => 'connect',
            'username' => $accountNetwork->username,
            'email'    => $accountNetwork->email,
        ]);

        if (!$account->validate(['email'])) {
            $accountNetwork->email = null;
        }

        if (!$account->validate(['username'])) {
            $accountNetwork->username = null;
        }

        return $account->create() ? $account : false;
    }

    /**
     * @return Finder
     */
    protected static function getFinder()
    {
        if (static::$finder === null) {
            static::$finder = Yii::$container->get(Finder::className());
        }

        return static::$finder;
    }
}
