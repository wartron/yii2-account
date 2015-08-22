<?php

namespace wartron\yii2account\tests;

use Codeception\Specify;
use wartron\yii2account\models\Account;
use tests\codeception\_fixtures\AccountFixture;
use yii\codeception\TestCase;
use Yii;

/**
 * Test suite for account active record class.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class AccountTest extends TestCase
{
    use Specify;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'account' => [
                'class' => AccountFixture::className(),
                'dataFile' => '@tests/codeception/_fixtures/data/init_account.php',
            ],
        ];
    }

    public function testRegister()
    {
        $this->specify('account should be registered', function () {
            $account = new Account(['scenario' => 'register']);
            $account->username = 'tester';
            $account->email = 'tester@example.com';
            $account->password = 'tester';
            verify($account->register())->true();
            verify($account->username)->equals('tester');
            verify($account->email)->equals('tester@example.com');
            verify(Yii::$app->getSecurity()->validatePassword('tester', $account->password_hash))->true();
        });

        $this->specify('profile should be created after registration', function () {
            $account = new Account(['scenario' => 'register']);
            $account->username = 'john_doe';
            $account->email = 'john_doe@example.com';
            $account->password = 'qwerty';
            verify($account->register())->true();
        });
    }

    public function testBlocking()
    {
        $this->specify('account can be blocked and unblocked', function () {
            $account = $this->getFixture('account')->getModel('account');
            $authKey = $account->auth_key;
            verify('account is not blocked', $account->getIsBlocked())->false();
            $account->block();
            verify('account is blocked', $account->getIsBlocked())->true();
            verify('auth_key has been changed', $account->auth_key)->notEquals($authKey);
            $account->unblock();
            verify('account is unblocked', $account->getIsBlocked())->false();
        });
    }

    public function testenableConfirmation()
    {
        \Yii::$app->getModule('account')->enableConfirmation = true;

        $this->specify('should return correct account confirmation status', function () {
            $account = $this->getFixture('account')->getModel('account');
            verify('account is confirmed', $account->getIsConfirmed())->true();
            $account = $this->getFixture('account')->getModel('unconfirmed');
            verify('account is not confirmed', $account->getIsConfirmed())->false();
        });

        /*$this->specify('correct account confirmation url should be returned', function () {
            $account = Account::findOne(1);
            verify('url is null for confirmed user', $account->getConfirmationUrl())->null();
            $account = Account::findOne(2);
            $needle = \Yii::$app->getUrlManager()->createAbsoluteUrl(['/user/registration/confirm',
                'id' => $account->id,
                'token' => $account->confirmation_token
            ]);
            verify('url contains correct id and confirmation token for unconfirmed user', $account->getConfirmationUrl())->contains($needle);
        });

        $this->specify('confirmation token should become invalid after specified time', function () {
            \Yii::$app->getModule('account')->confirmWithin = $expirationTime = 86400;
            $account = new Account([
                'confirmation_token' => 'NNWJf_CoV8ocX3AsYK38CoOGkXUcpQK4',
                'confirmation_sent_at' => time()
            ]);
            verify($account->getIsConfirmationPeriodExpired())->false();
            $account = new Account([
                'confirmation_token'   => 'NNWJf_CoV8ocX3AsYK38CoOGkXUcpQK4',
                'confirmation_sent_at' => time() - $expirationTime - 1
            ]);
            verify($account->getIsConfirmationPeriodExpired())->true();
        });

        $this->specify('account should be confirmed by updating confirmed_at field', function () {
            $account = Account::findOne(2);
            verify($account->confirmed_at)->null();
            $account->confirm();
            verify($account->confirmed_at)->notNull();
        });*/
    }

/*    public function testEmailSettings()
    {
        $this->account = Account::findOne(1);
        $this->user->scenario = 'update_email';
        $this->user->unconfirmed_email = 'new_email@example.com';
        $this->user->current_password = 'qwerty';
        $this->user->updateEmail();

        $this->specify('email should be updated', function () {
            verify($this->user->email)->equals('new_email@example.com');
            verify($this->user->unconfirmed_email)->null();
        });

        \Yii::$app->getModule('account')->enableConfirmation = true;

        $this->specify('confirmation message should be sent if enableConfirmation is enabled', function () {
            $this->user->unconfirmed_email = 'another_email@example.com';
            $this->user->current_password = 'qwerty';
            $this->user->updateEmail();
            verify($this->user->email)->equals('new_email@example.com');
            verify($this->user->unconfirmed_email)->equals('another_email@example.com');
        });

        $this->specify('email reconfirmation should be reset', function () {
            $this->user->resetEmailUpdate();
            verify($this->user->email)->equals('new_email@example.com');
            verify($this->user->unconfirmed_email)->null();
            verify($this->user->confirmation_sent_at)->null();
            verify($this->user->confirmation_token)->null();
        });
    }

    public function testRecoverable()
    {
        $this->account = Account::findOne(1);
        $this->user->sendRecoveryMessage();

        $this->specify('correct account confirmation url should be returned', function () {
            $needle = \Yii::$app->getUrlManager()->createAbsoluteUrl(['/user/recovery/reset',
                'id' => $this->user->id,
                'token' => $this->user->recovery_token
            ]);
            verify($this->user->getRecoveryUrl())->contains($needle);
        });

        $this->specify('confirmation token should become invalid after specified time', function () {
            \Yii::$app->getModule('account')->recoverWithin = $expirationTime = 86400;
            $account = new Account([
                'recovery_token' => 'NNWJf_CoV8ocX3AsYK38CoOGkXUcpQK4',
                'recovery_sent_at' => time()
            ]);
            verify($account->getIsRecoveryPeriodExpired())->false();
            $account = new Account([
                'recovery_token'   => 'NNWJf_CoV8ocX3AsYK38CoOGkXUcpQK4',
                'recovery_sent_at' => time() - $expirationTime - 1
            ]);
            verify($account->getIsRecoveryPeriodExpired())->true();
        });
    }*/
}
