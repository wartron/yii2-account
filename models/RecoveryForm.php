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
use wartron\yii2account\Mailer;
use Yii;
use yii\base\Model;

/**
 * Model for collecting data on password recovery.
 *
 * @property \wartron\yii2account\Module $module
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RecoveryForm extends Model
{
    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var User */
    protected $user;

    /** @var \wartron\yii2account\Module */
    protected $module;

    /** @var Mailer */
    protected $mailer;

    /** @var Finder */
    protected $finder;

    /**
     * @param Mailer $mailer
     * @param Finder $finder
     * @param array  $config
     */
    public function __construct(Mailer $mailer, Finder $finder, $config = [])
    {
        $this->module = Yii::$app->getModule('account');
        $this->mailer = $mailer;
        $this->finder = $finder;
        parent::__construct($config);
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email'    => Yii::t('account', 'Email'),
            'password' => Yii::t('account', 'Password'),
        ];
    }

    /** @inheritdoc */
    public function scenarios()
    {
        return [
            'request' => ['email'],
            'reset'   => ['password'],
        ];
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'emailTrim' => ['email', 'filter', 'filter' => 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern' => ['email', 'email'],
            'emailExist' => [
                'email',
                'exist',
                'targetClass' => $this->module->modelMap['Account'],
                'message' => Yii::t('account', 'There is no user with this email address'),
            ],
            'emailUnconfirmed' => [
                'email',
                function ($attribute) {
                    $this->user = $this->finder->findAccountByEmail($this->email);
                    if ($this->user !== null && $this->module->enableConfirmation && !$this->user->getIsConfirmed()) {
                        $this->addError($attribute, Yii::t('account', 'You need to confirm your email address'));
                    }
                }
            ],
            'passwordRequired' => ['password', 'required'],
            'passwordLength' => ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Sends recovery message.
     *
     * @return bool
     */
    public function sendRecoveryMessage()
    {
        if ($this->validate()) {
            /** @var Token $token */
            $token = Yii::createObject([
                'class'   => Token::className(),
                'account_id' => $this->user->id,
                'type'    => Token::TYPE_RECOVERY,
            ]);
            $token->save(false);
            $this->mailer->sendRecoveryMessage($this->user, $token);
            Yii::$app->session->setFlash('info', Yii::t('account', 'An email has been sent with instructions for resetting your password'));

            return true;
        }

        return false;
    }

    /**
     * Resets user's password.
     *
     * @param Token $token
     *
     * @return bool
     */
    public function resetPassword(Token $token)
    {
        if (!$this->validate() || $token->user === null) {
            return false;
        }

        if ($token->user->resetPassword($this->password)) {
            Yii::$app->session->setFlash('success', Yii::t('account', 'Your password has been changed successfully.'));
            $token->delete();
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('account', 'An error occurred and your password has not been changed. Please try again later.'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'recovery-form';
    }
}
