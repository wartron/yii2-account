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
 * ResendForm gets user email address and validates if user has already confirmed his account. If so, it shows error
 * message, otherwise it generates and sends new confirmation token to user.
 *
 * @property Account $account
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class ResendForm extends Model
{
    /** @var string */
    public $email;

    /** @var Account */
    private $_account;

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

    /**
     * @return Account
     */
    public function getAccount()
    {
        if ($this->_account === null) {
            $this->_account = $this->finder->findAccountByEmail($this->email);
        }

        return $this->_account;
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'emailRequired' => ['email', 'required'],
            'emailPattern' => ['email', 'email'],
            'emailExist' => ['email', 'exist', 'targetClass' => $this->module->modelMap['Account']],
            'emailConfirmed' => [
                'email',
                function () {
                    if ($this->account != null && $this->account->getIsConfirmed()) {
                        $this->addError('email', Yii::t('account', 'This account has already been confirmed'));
                    }
                }
            ],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('account', 'Email'),
        ];
    }

    /** @inheritdoc */
    public function formName()
    {
        return 'resend-form';
    }

    /**
     * Creates new confirmation token and sends it to the account.
     *
     * @return bool
     */
    public function resend()
    {
        if (!$this->validate()) {
            return false;
        }
        /** @var Token $token */
        $token = Yii::createObject([
            'class'   => Token::className(),
            'account_id' => $this->account->id,
            'type'    => Token::TYPE_CONFIRMATION,
        ]);
        $token->save(false);
        $this->mailer->sendConfirmationMessage($this->account, $token);
        Yii::$app->session->setFlash('info', Yii::t('account', 'A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.'));

        return true;
    }
}
