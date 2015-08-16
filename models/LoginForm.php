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

use wartron\yii2account\Finder;
use wartron\yii2account\helpers\Password;
use Yii;
use yii\base\Model;

/**
 * LoginForm get user's login and password, validates them and logs the user in. If user has been blocked, it adds
 * an error to login form.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class LoginForm extends Model
{
    /** @var string User's email or username */
    public $login;

    /** @var string User's plain password */
    public $password;

    /** @var string Whether to remember the user */
    public $rememberMe = false;

    /** @var \wartron\yii2account\models\Account */
    protected $account;

    /** @var \wartron\yii2account\Module */
    protected $module;

    /** @var Finder */
    protected $finder;

    /**
     * @param Finder $finder
     * @param array  $config
     */
    public function __construct(Finder $finder, $config = [])
    {
        $this->finder = $finder;
        $this->module = Yii::$app->getModule('account');
        parent::__construct($config);
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'login'      => Yii::t('account', 'Login'),
            'password'   => Yii::t('account', 'Password'),
            'rememberMe' => Yii::t('account', 'Remember me next time'),
        ];
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'requiredFields' => [['login', 'password'], 'required'],
            'loginTrim' => ['login', 'trim'],
            'passwordValidate' => [
                'password',
                function ($attribute) {
                    if ($this->account === null || !Password::validate($this->password, $this->account->password_hash)) {
                        $this->addError($attribute, Yii::t('account', 'Invalid login or password'));
                    }
                }
            ],
            'confirmationValidate' => [
                'login',
                function ($attribute) {
                    if ($this->account !== null) {
                        $confirmationRequired = $this->module->enableConfirmation && !$this->module->enableUnconfirmedLogin;
                        if ($confirmationRequired && !$this->account->getIsConfirmed()) {
                            $this->addError($attribute, Yii::t('account', 'You need to confirm your email address'));
                        }
                        if ($this->account->getIsBlocked()) {
                            $this->addError($attribute, Yii::t('account', 'Your account has been blocked'));
                        }
                    }
                }
            ],
            'rememberMe' => ['rememberMe', 'boolean'],
        ];
    }

    /**
     * Validates form and logs the user in.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->getUser()->login($this->account, $this->rememberMe ? $this->module->rememberFor : 0);
        } else {
            return false;
        }
    }

    /** @inheritdoc */
    public function formName()
    {
        return 'login-form';
    }

    /** @inheritdoc */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->account = $this->finder->findAccountByUsernameOrEmail($this->login);

            return true;
        } else {
            return false;
        }
    }
}
