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
use wartron\yii2account\Mailer;
use wartron\yii2account\Module;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\Application as WebApplication;
use yii\web\IdentityInterface;

/**
 * User ActiveRecord model.
 *
 * @property bool    $isAdmin
 * @property bool    $isBlocked
 * @property bool    $isConfirmed
 *
 * Database fields:
 * @property integer $id
 * @property string  $username
 * @property string  $email
 * @property string  $unconfirmed_email
 * @property string  $password_hash
 * @property string  $auth_key
 * @property integer $registration_ip
 * @property integer $confirmed_at
 * @property integer $blocked_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $flags
 * @property integer $type
 *
 * Defined relations:
 * @property AccountNetwork[] $networks
 * @property Profile   $profile
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Account extends ActiveRecord implements IdentityInterface
{
    const BEFORE_CREATE   = 'beforeCreate';
    const AFTER_CREATE    = 'afterCreate';
    const BEFORE_REGISTER = 'beforeRegister';
    const AFTER_REGISTER  = 'afterRegister';

    // following constants are used on secured email changing process
    const OLD_EMAIL_CONFIRMED = 0b1;
    const NEW_EMAIL_CONFIRMED = 0b10;

    const ACCOUNT_USER          = 1;
    const ACCOUNT_ORGANIZATION  = 2;

    /** @var string Plain password. Used for model validation. */
    public $password;

    /** @var \wartron\yii2account\Module */
    protected $module;

    /** @var Mailer */
    protected $mailer;

    /** @var Finder */
    protected $finder;

    /** @var Profile|null */
    private $_profile;

    /** @var string Default username regexp */
    public static $usernameRegexp = '/^[-a-zA-Z0-9_\.@]+$/';

    /** @inheritdoc */
    public function init()
    {
        $this->finder = Yii::$container->get(Finder::className());
        $this->mailer = Yii::$container->get(Mailer::className());
        $this->module = Yii::$app->getModule('account');
        parent::init();
    }

    /**
     * @return bool Whether the account is a organization or not.
     */
    public function getIsOrganization()
    {
        return $this->type == self::ACCOUNT_ORGANIZATION;
    }

    /**
     * @return bool Whether the account is a user or not.
     */
    public function getIsUser()
    {
        return $this->type == self::ACCOUNT_USERC;
    }

    /**
     * @return bool Whether the account is confirmed or not.
     */
    public function getIsConfirmed()
    {
        return $this->confirmed_at != null;
    }

    /**
     * @return bool Whether the account is blocked or not.
     */
    public function getIsBlocked()
    {
        return $this->blocked_at != null;
    }

    /**
     * @return bool Whether the account is an admin or not.
     */
    public function getIsAdmin()
    {
        return in_array($this->username, $this->module->admins);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne($this->module->modelMap['Profile'], ['account_id' => 'id']);
    }

    /**
     * @param Profile $profile
     */
    public function setProfile(Profile $profile)
    {
        $this->_profile = $profile;
    }

    /**
     * @return AccountNetwork[] Connected accounts ($provider => $account)
     */
    public function getNetworks()
    {
        $connected = [];
        $networks  = $this->hasMany($this->module->modelMap['AccountNetwork'], ['account_id' => 'id'])->all();

        /** @var AccountNetwork $account */
        foreach ($networks as $account) {
            $connected[$account->provider] = $account;
        }

        return $connected;
    }

    /**
     * @return AccountOrganization[] Connected accounts ($account => $account)
     */
    public function getOrganizations()
    {
        $connected = [];
        $organizations  = $this->hasMany($this->module->modelMap['AccountOrganization'], ['account_id' => 'id'])->all();

        /** @var AccountOrganization $account */
        foreach ($organizations as $account) {
            $connected[$account->organization] = $account;
        }

        return $connected;
    }

    /** @inheritdoc */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /** @inheritdoc */
    public function getAuthKey()
    {
        return $this->getAttribute('auth_key');
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username'          => Yii::t('account', 'Username'),
            'email'             => Yii::t('account', 'Email'),
            'registration_ip'   => Yii::t('account', 'Registration ip'),
            'unconfirmed_email' => Yii::t('account', 'New email'),
            'password'          => Yii::t('account', 'Password'),
            'created_at'        => Yii::t('account', 'Registration time'),
            'confirmed_at'      => Yii::t('account', 'Confirmation time'),
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            \wartron\yii2uuid\behaviors\UUIDBehavior::className(),
            TimestampBehavior::className(),
        ];
    }

    /** @inheritdoc */
    public function scenarios()
    {
        return [
            'register' => ['username', 'email', 'password'],
            'connect'  => ['username', 'email'],
            'create'   => ['username', 'email', 'password'],
            'update'   => ['username', 'email', 'password'],
            'settings' => ['username', 'email', 'password'],
        ];
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            // username rules
            'usernameRequired' => ['username', 'required', 'on' => ['register', 'create', 'connect', 'update']],
            'usernameMatch'    => ['username', 'match', 'pattern' => static::$usernameRegexp],
            'usernameLength'   => ['username', 'string', 'min' => 3, 'max' => 255],
            'usernameUnique'   => ['username', 'unique', 'message' => Yii::t('account', 'This username has already been taken')],
            'usernameTrim'     => ['username', 'trim'],

            // email rules
            'emailRequired' => ['email', 'required', 'on' => ['register', 'connect', 'create', 'update']],
            'emailPattern'  => ['email', 'email'],
            'emailLength'   => ['email', 'string', 'max' => 255],
            'emailUnique'   => ['email', 'unique', 'message' => Yii::t('account', 'This email address has already been taken')],
            'emailTrim'     => ['email', 'trim'],

            // password rules
            'passwordRequired' => ['password', 'required', 'on' => ['register']],
            'passwordLength'   => ['password', 'string', 'min' => 6, 'on' => ['register', 'create']],
        ];
    }

    /** @inheritdoc */
    public function validateAuthKey($authKey)
    {
        return $this->getAttribute('auth_key') === $authKey;
    }

    /**
     * Creates new account account. It generates password if it is not provided by account.
     *
     * @return bool
     */
    public function create()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing account');
        }

        $this->confirmed_at = time();
        $this->password = $this->password == null ? Password::generate(8) : $this->password;

        $this->trigger(self::BEFORE_CREATE);

        if (!$this->save()) {
            return false;
        }

        $this->mailer->sendWelcomeMessage($this);
        $this->trigger(self::AFTER_CREATE);

        return true;
    }

    /**
     * This method is used to register new account account. If Module::enableConfirmation is set true, this method
     * will generate new confirmation token and use mailer to send it to the account.
     *
     * @return bool
     */
    public function register()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing account');
        }

        $this->confirmed_at = $this->module->enableConfirmation ? null : time();
        $this->password     = $this->module->enableGeneratingPassword ? Password::generate(8) : $this->password;

        $this->trigger(self::BEFORE_REGISTER);

        if (!$this->save()) {
            return false;
        }

        if ($this->module->enableConfirmation) {
            /** @var Token $token */
            $token = Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
            $token->link('account', $this);
        }

        $this->mailer->sendWelcomeMessage($this, isset($token) ? $token : null);
        $this->trigger(self::AFTER_REGISTER);

        return true;
    }

    /**
     * Attempts account confirmation.
     *
     * @param string $code Confirmation code.
     *
     * @return boolean
     */
    public function attemptConfirmation($code)
    {
        $token = $this->finder->findTokenByParams($this->id, $code, Token::TYPE_CONFIRMATION);

        if ($token instanceof Token && !$token->isExpired) {
            $token->delete();
            if (($success = $this->confirm())) {
                Yii::$app->user->login($this, $this->module->rememberFor);
                $message = Yii::t('account', 'Thank you, registration is now complete.');
            } else {
                $message = Yii::t('account', 'Something went wrong and your account has not been confirmed.');
            }
        } else {
            $success = false;
            $message = Yii::t('account', 'The confirmation link is invalid or expired. Please try requesting a new one.');
        }

        Yii::$app->session->setFlash($success ? 'success' : 'danger', $message);

        return $success;
    }

    /**
     * This method attempts changing user email. If user's "unconfirmed_email" field is empty is returns false, else if
     * somebody already has email that equals user's "unconfirmed_email" it returns false, otherwise returns true and
     * updates user's password.
     *
     * @param string $code
     *
     * @return bool
     * @throws \Exception
     */
    public function attemptEmailChange($code)
    {
        // TODO refactor method

        /** @var Token $token */
        $token = $this->finder->findToken([
            'account_id' => $this->id,
            'code'    => $code,
        ])->andWhere(['in', 'type', [Token::TYPE_CONFIRM_NEW_EMAIL, Token::TYPE_CONFIRM_OLD_EMAIL]])->one();

        if (empty($this->unconfirmed_email) || $token === null || $token->isExpired) {
            Yii::$app->session->setFlash('danger', Yii::t('account', 'Your confirmation token is invalid or expired'));
        } else {
            $token->delete();

            if (empty($this->unconfirmed_email)) {
                Yii::$app->session->setFlash('danger', Yii::t('account', 'An error occurred processing your request'));
            } elseif ($this->finder->findUser(['email' => $this->unconfirmed_email])->exists() == false) {
                if ($this->module->emailChangeStrategy == Module::STRATEGY_SECURE) {
                    switch ($token->type) {
                        case Token::TYPE_CONFIRM_NEW_EMAIL:
                            $this->flags |= self::NEW_EMAIL_CONFIRMED;
                            Yii::$app->session->setFlash('success', Yii::t('account', 'Awesome, almost there. Now you need to click the confirmation link sent to your old email address'));
                            break;
                        case Token::TYPE_CONFIRM_OLD_EMAIL:
                            $this->flags |= self::OLD_EMAIL_CONFIRMED;
                            Yii::$app->session->setFlash('success', Yii::t('account', 'Awesome, almost there. Now you need to click the confirmation link sent to your new email address'));
                            break;
                    }
                }
                if ($this->module->emailChangeStrategy == Module::STRATEGY_DEFAULT || ($this->flags & self::NEW_EMAIL_CONFIRMED && $this->flags & self::OLD_EMAIL_CONFIRMED)) {
                    $this->email = $this->unconfirmed_email;
                    $this->unconfirmed_email = null;
                    Yii::$app->session->setFlash('success', Yii::t('account', 'Your email address has been changed'));
                }
                $this->save(false);
            }
        }
    }

    /**
     * Confirms the user by setting 'confirmed_at' field to current time.
     */
    public function confirm()
    {
        return (bool)$this->updateAttributes(['confirmed_at' => time()]);
    }

    /**
     * Resets password.
     *
     * @param string $password
     *
     * @return bool
     */
    public function resetPassword($password)
    {
        return (bool)$this->updateAttributes(['password_hash' => Password::hash($password)]);
    }

    /**
     * Blocks the user by setting 'blocked_at' field to current time and regenerates auth_key.
     */
    public function block()
    {
        return (bool)$this->updateAttributes([
            'blocked_at' => time(),
            'auth_key'   => Yii::$app->security->generateRandomString(),
        ]);
    }

    /**
     * UnBlocks the user by setting 'blocked_at' field to null.
     */
    public function unblock()
    {
        return (bool)$this->updateAttributes(['blocked_at' => null]);
    }

    /**
     * Generates new username based on email address, or creates new username
     * like "user1".
     */
    public function generateUsername()
    {
        // try to use name part of email
        $this->username = explode('@', $this->email)[0];
        if ($this->validate(['username'])) {
            return $this->username;
        }

        // generate username like "user1", "user2", etc...
        while (!$this->validate(['username'])) {
            $row = (new Query())
                ->from('{{%account}}')
                ->select('MAX(id) as id')
                ->one();

            $this->username = 'account' . ++$row['id'];
        }

        return $this->username;
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->setAttribute('auth_key', Yii::$app->security->generateRandomString());
            if (Yii::$app instanceof WebApplication) {
                $this->setAttribute('registration_ip', Yii::$app->request->userIP);
            }
        }

        if (!empty($this->password)) {
            $this->setAttribute('password_hash', Password::hash($this->password));
        }

        return parent::beforeSave($insert);
    }

    /** @inheritdoc */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            if ($this->_profile == null) {
                $this->_profile = Yii::createObject(Profile::className());
            }
            $this->_profile->link('account', $this);
        }
    }

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%account}}';
    }

    /** @inheritdoc */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /** @inheritdoc */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('Method "' . __CLASS__ . '::' . __METHOD__ . '" is not implemented.');
    }
}
