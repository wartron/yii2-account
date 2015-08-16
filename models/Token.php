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

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Token Active Record model.
 *
 * @property integer $account_id
 * @property string  $code
 * @property integer $created_at
 * @property integer $type
 * @property string  $url
 * @property bool    $isExpired
 * @property Account    $account
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Token extends ActiveRecord
{
    const TYPE_CONFIRMATION      = 0;
    const TYPE_RECOVERY          = 1;
    const TYPE_CONFIRM_NEW_EMAIL = 2;
    const TYPE_CONFIRM_OLD_EMAIL = 3;

    /** @var \wartron\yii2account\Module */
    protected $module;

    /** @inheritdoc */
    public function init()
    {
        $this->module = Yii::$app->getModule('account');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne($this->module->modelMap['Account'], ['id' => 'account_id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
                $route = '/account/registration/confirm';
                break;
            case self::TYPE_RECOVERY:
                $route = '/account/recovery/reset';
                break;
            case self::TYPE_CONFIRM_NEW_EMAIL:
            case self::TYPE_CONFIRM_OLD_EMAIL:
                $route = '/account/settings/confirm';
                break;
            default:
                throw new \RuntimeException();
        }

        return Url::to([$route, 'id' => $this->account_id, 'code' => $this->code], true);
    }

    /**
     * @return bool Whether token has expired.
     */
    public function getIsExpired()
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
            case self::TYPE_CONFIRM_NEW_EMAIL:
            case self::TYPE_CONFIRM_OLD_EMAIL:
                $expirationTime = $this->module->confirmWithin;
                break;
            case self::TYPE_RECOVERY:
                $expirationTime = $this->module->recoverWithin;
                break;
            default:
                throw new \RuntimeException();
        }

        return ($this->created_at + $expirationTime) < time();
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->setAttribute('created_at', time());
            $this->setAttribute('code', Yii::$app->security->generateRandomString());
        }

        return parent::beforeSave($insert);
    }

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%token}}';
    }

    /** @inheritdoc */
    public static function primaryKey()
    {
        return ['account_id', 'code', 'type'];
    }
}
