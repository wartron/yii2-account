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

use wartron\yii2account\helpers\Password;
use wartron\yii2account\Mailer;
use wartron\yii2account\Module;
use Yii;
use yii\base\Model;

/**
 * SettingsForm gets user's username, email and password and changes them.
 *
 * @property User $user
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class SettingsForm extends Model
{
    /** @var string */
    public $email;

    /** @var string */
    public $username;

    /** @var string */
    public $new_password;

    /** @var string */
    public $current_password;

    /** @var Module */
    protected $module;

    /** @var Mailer */
    protected $mailer;

    /** @var Account */
    private $_account;

    /** @return Account */
    public function getAccount()
    {
        if ($this->_account == null) {
            $this->_account = Yii::$app->user->identity;
        }

        return $this->_account;
    }

    /** @inheritdoc */
    public function __construct(Mailer $mailer, $config = [])
    {
        $this->mailer = $mailer;
        $this->module = Yii::$app->getModule('account');
        $this->setAttributes([
            'username' => $this->account->username,
            'email'    => $this->account->unconfirmed_email ?: $this->account->email,
        ], false);
        parent::__construct($config);
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'usernameRequired' => ['username', 'required'],
            'usernameTrim' => ['username', 'filter', 'filter' => 'trim'],
            'usernameLenth' => ['username', 'string', 'min' => 3, 'max' => 20],
            'usernamePattern' => ['username', 'match', 'pattern' => '/^[-a-zA-Z0-9_\.@]+$/'],
            'emailRequired' => ['email', 'required'],
            'emailTrim' => ['email', 'filter', 'filter' => 'trim'],
            'emailPattern' => ['email', 'email'],
            'emailUsernameUnique' => [['email', 'username'], 'unique', 'when' => function ($model, $attribute) {
                return $this->account->$attribute != $model->$attribute;
            }, 'targetClass' => $this->module->modelMap['Account']],
            'newPasswordLength' => ['new_password', 'string', 'min' => 6],
            'currentPasswordRequired' => ['current_password', 'required'],
            'currentPasswordValidate' => ['current_password', function ($attr) {
                if (!Password::validate($this->$attr, $this->account->password_hash)) {
                    $this->addError($attr, Yii::t('account', 'Current password is not valid'));
                }
            }],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email'            => Yii::t('account', 'Email'),
            'username'         => Yii::t('account', 'Username'),
            'new_password'     => Yii::t('account', 'New password'),
            'current_password' => Yii::t('account', 'Current password'),
        ];
    }

    /** @inheritdoc */
    public function formName()
    {
        return 'settings-form';
    }

    /**
     * Saves new account settings.
     *
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            $this->account->scenario = 'settings';
            $this->account->username = $this->username;
            $this->account->password = $this->new_password;
            if ($this->email == $this->account->email && $this->account->unconfirmed_email != null) {
                $this->account->unconfirmed_email = null;
            } elseif ($this->email != $this->account->email) {
                switch ($this->module->emailChangeStrategy) {
                    case Module::STRATEGY_INSECURE:
                        $this->insecureEmailChange();
                        break;
                    case Module::STRATEGY_DEFAULT:
                        $this->defaultEmailChange();
                        break;
                    case Module::STRATEGY_SECURE:
                        $this->secureEmailChange();
                        break;
                    default:
                        throw new \OutOfBoundsException('Invalid email changing strategy');
                }
            }

            return $this->account->save();
        }

        return false;
    }

    /**
     * Changes user's email address to given without any confirmation.
     */
    protected function insecureEmailChange()
    {
        $this->account->email = $this->email;
        Yii::$app->session->setFlash('success', Yii::t('account', 'Your email address has been changed'));
    }

    /**
     * Sends a confirmation message to user's email address with link to confirm changing of email.
     */
    protected function defaultEmailChange()
    {
        $this->account->unconfirmed_email = $this->email;
        /** @var Token $token */
        $token = Yii::createObject([
            'class'   => Token::className(),
            'account_id' => $this->account->id,
            'type'    => Token::TYPE_CONFIRM_NEW_EMAIL,
        ]);
        $token->save(false);
        $this->mailer->sendReconfirmationMessage($this->account, $token);
        Yii::$app->session->setFlash('info', Yii::t('account', 'A confirmation message has been sent to your new email address'));
    }

    /**
     * Sends a confirmation message to both old and new email addresses with link to confirm changing of email.
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected function secureEmailChange()
    {
        $this->defaultEmailChange();
        /** @var Token $token */
        $token = Yii::createObject([
            'class'   => Token::className(),
            'account_id' => $this->account->id,
            'type'    => Token::TYPE_CONFIRM_OLD_EMAIL,
        ]);
        $token->save(false);
        $this->mailer->sendReconfirmationMessage($this->account, $token);

        // unset flags if they exist
        $this->account->flags &= ~Account::NEW_EMAIL_CONFIRMED;
        $this->account->flags &= ~Account::OLD_EMAIL_CONFIRMED;
        $this->account->save(false);

        Yii::$app->session->setFlash('info', Yii::t('account', 'We have sent confirmation links to both old and new email addresses. You must click both links to complete your request'));
    }
}
