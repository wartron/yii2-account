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

/**
 * This is the model class for table "profile".
 *
 * @property integer $account_id
 * @property string  $name
 * @property string  $public_email
 * @property string  $gravatar_email
 * @property string  $gravatar_id
 * @property string  $location
 * @property string  $website
 * @property string  $bio
 * @property Account    $account
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com
 */
class Profile extends ActiveRecord
{
    /** @var \wartron\yii2account\Module */
    protected $module;

    /** @inheritdoc */
    public function init()
    {
        $this->module = Yii::$app->getModule('account');
    }

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%profile}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'bioString' => ['bio', 'string'],
            'publicEmailPattern' => ['public_email', 'email'],
            'gravatarEmailPattern' => ['gravatar_email', 'email'],
            'websiteUrl' => ['website', 'url'],
            'nameLength' => ['name', 'string', 'max' => 255],
            'publicEmailLength' => ['public_email', 'string', 'max' => 255],
            'gravatarEmailLength' => ['gravatar_email', 'string', 'max' => 255],
            'locationLength' => ['location', 'string', 'max' => 255],
            'websiteLength' => ['website', 'string', 'max' => 255],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'name'           => Yii::t('account', 'Name'),
            'public_email'   => Yii::t('account', 'Email (public)'),
            'gravatar_email' => Yii::t('account', 'Gravatar email'),
            'location'       => Yii::t('account', 'Location'),
            'website'        => Yii::t('account', 'Website'),
            'bio'            => Yii::t('account', 'Bio'),
        ];
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isAttributeChanged('gravatar_email')) {
                $this->setAttribute('gravatar_id', md5(strtolower($this->getAttribute('gravatar_email'))));
            }

            return true;
        }

        return false;
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getAccount()
    {
        return $this->hasOne($this->module->modelMap['Account'], ['id' => 'account_id']);
    }
}
