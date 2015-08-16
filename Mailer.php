<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wartron\yii2account;

use wartron\yii2account\models\Token;
use wartron\yii2account\models\Account;
use Yii;
use yii\base\Component;

/**
 * Mailer.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Mailer extends Component
{
    /** @var string */
    public $viewPath = '@wartron/yii2account/views/mail';

    /** @var string|array Default: `Yii::$app->params['adminEmail']` OR `no-reply@example.com` */
    public $sender;

    /** @var string */
    public $welcomeSubject;

    /** @var string */
    public $confirmationSubject;

    /** @var string */
    public $reconfirmationSubject;

    /** @var string */
    public $recoverySubject;

    /** @var \wartron\yii2account\Module */
    protected $module;

    /** @inheritdoc */
    public function init()
    {
        $this->module = Yii::$app->getModule('account');
        parent::init();
    }

    /**
     * Sends an email to a user after registration.
     *
     * @param Account  $account
     * @param Token $token
     *
     * @return bool
     */
    public function sendWelcomeMessage(Account $account, Token $token = null)
    {
        return $this->sendMessage($account->email,
            $this->welcomeSubject,
            'welcome',
            ['user' => $account, 'token' => $token, 'module' => $this->module]
        );
    }

    /**
     * Sends an email to a user with confirmation link.
     *
     * @param Account $account
     * @param Token $token
     *
     * @return bool
     */
    public function sendConfirmationMessage(Account $account, Token $token)
    {
        return $this->sendMessage($account->email,
            $this->confirmationSubject,
            'confirmation',
            ['user' => $account, 'token' => $token]
        );
    }

    /**
     * Sends an email to a user with reconfirmation link.
     *
     * @param Account $account
     * @param Token $token
     *
     * @return bool
     */
    public function sendReconfirmationMessage(Account $account, Token $token)
    {
        if ($token->type == Token::TYPE_CONFIRM_NEW_EMAIL) {
            $email = $account->unconfirmed_email;
        } else {
            $email = $account->email;
        }

        return $this->sendMessage($email,
            $this->reconfirmationSubject,
            'reconfirmation',
            ['user' => $account, 'token' => $token]
        );
    }

    /**
     * Sends an email to a user with recovery link.
     *
     * @param Account $account
     * @param Token $token
     *
     * @return bool
     */
    public function sendRecoveryMessage(Account $account, Token $token)
    {
        return $this->sendMessage($account->email,
            $this->recoverySubject,
            'recovery',
            ['user' => $account, 'token' => $token]
        );
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array  $params
     *
     * @return bool
     */
    protected function sendMessage($to, $subject, $view, $params = [])
    {
        /** @var \yii\mail\BaseMailer $mailer */
        $mailer = Yii::$app->mailer;
        $mailer->viewPath = $this->viewPath;
        $mailer->getView()->theme = Yii::$app->view->theme;

        if ($this->sender === null) {
            $this->sender = isset(Yii::$app->params['adminEmail']) ? Yii::$app->params['adminEmail'] : 'no-reply@example.com';
        }

        return $mailer->compose(['html' => $view, 'text' => 'text/' . $view], $params)
            ->setTo($to)
            ->setFrom($this->sender)
            ->setSubject($subject)
            ->send();
    }
}
