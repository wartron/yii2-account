<?php

namespace wartron\yii2account\tests;

use Codeception\Specify;
use wartron\yii2account\helpers\Password;
use wartron\yii2account\models\RegistrationForm;
use wartron\yii2account\models\Token;
use wartron\yii2account\models\Account;
use tests\codeception\_fixtures\AccountFixture;
use yii\codeception\TestCase;

class RegistrationFormTest extends TestCase
{
    use Specify;

    /** @var RegistrationForm */
    protected $model;

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

    public function testValidationRules()
    {
        $this->model = new RegistrationForm();

        verify('username is required', $this->model->validate(['username']))->false();
        $toolongstring = function() { $string = ''; for($i = 0; $i <= 256; $i++) $string .= 'X'; return $string; };
        $this->model->username = $toolongstring();
        verify('username is too long', $this->model->validate(['username']))->false();
        $this->model->username = '!@# абв';
        verify('username contains invalid characters', $this->model->validate(['username']))->false();
        $this->model->username = 'account';
        verify('username is already using', $this->model->validate(['username']))->false();
        $this->model->username = 'perfect_name';
        verify('username is ok', $this->model->validate(['username']))->true();

        verify('email is required', $this->model->validate(['email']))->false();
        $this->model->email = 'not valid email';
        verify('email is not email', $this->model->validate(['email']))->false();
        $this->model->email = 'account@example.com';
        verify('email is already using', $this->model->validate(['email']))->false();
        $this->model->email = 'perfect@example.com';
        verify('email is ok', $this->model->validate(['email']))->true();

        verify('password is required', $this->model->validate(['password']))->false();
        $this->model->password = '12345';
        verify('password is too short', $this->model->validate(['password']))->false();
        $this->model->password = 'superSecretPa$$word';
        verify('password is ok', $this->model->validate(['password']))->true();
    }

    public function testRegister()
    {
        $this->model = new RegistrationForm();
        $this->model->setAttributes([
            'email'    => 'foobar@example.com',
            'username' => 'foobar',
            'password' => 'foobar',
        ]);

        /* @var account $account */
        verify($this->model->register())->true();

        $account = Account::findOne(['email' => 'foobar@example.com']);

        verify('$account is instance of Account', $account instanceof Account)->true();
        verify('email is valid', $account->email)->equals($this->model->email);
        verify('username is valid', $account->username)->equals($this->model->username);
        verify('password is valid', Password::validate($this->model->password, $account->password_hash))->true();

        $token = Token::findOne(['account_id' => $account->id, 'type' => Token::TYPE_CONFIRMATION]);
        verify($token)->notNull();

        $mock = $this->getMock(RegistrationForm::className(), ['validate']);
        $mock->expects($this->once())->method('validate')->will($this->returnValue(false));
        verify($mock->register())->false();
    }
}
