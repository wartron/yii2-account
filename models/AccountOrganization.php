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
class AccountOrganization extends ActiveRecord
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
        return '{{%account_organization}}';
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->hasOne($this->module->modelMap['Account'], ['id' => 'account_id']);
    }

}
