<?php

namespace wartron\yii2account\tests;

use AspectMock\Test as test;
use Codeception\Specify;
use wartron\yii2account\Finder;
use wartron\yii2account\models\LoginForm;
use wartron\yii2account\models\Account;
use Yii;
use yii\base\Security;
use yii\codeception\TestCase;

/**
 * Tests for a login form.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class LoginFormTest extends TestCase
{
    use Specify;

    /**
     * Tests validation rules for the model.
     */
    public function testLoginFormValidationRules()
    {
        $form = Yii::createObject(LoginForm::className());

        $this->specify('login is required', function () use ($form) {
            $form->setAttributes(['login' => '']);
            verify($form->validate())->false();
            verify($form->getErrors('login'))->contains('Login cannot be blank.');
        });

        $this->specify('password is required', function () use ($form) {
            $form->setAttributes(['password' => '']);
            verify($form->validate())->false();
            verify($form->getErrors('password'))->contains('Password cannot be blank.');
        });

        $this->specify('Account should exist in database', function () use ($form) {
            $finder = test::double(Finder::className(), ['findAccountByUsernameOrEmail' => null]);
            $form->setAttributes(['login' => 'tester', 'password' => 'qwerty']);
            verify($form->validate())->false();
            verify($form->getErrors('password'))->contains('Invalid login or password');
            $finder->verifyInvoked('findAccountByUsernameOrEmail');
        });

        $this->specify('password should be valid', function () use ($form) {
            test::double(Finder::className(), ['findAccountByUsernameOrEmail' => \Yii::createObject(Account::className())]);
            test::double(Security::className(), ['validatePassword' => false]);
            $form->setAttributes(['password' => 'qwerty']);
            verify($form->validate(['password']))->false();
            verify($form->getErrors('password'))->contains('Invalid login or password');
            test::double(Security::className(), ['validatePassword' => true]);
            verify($form->validate(['password']))->true();
        });

        $this->specify('Account may not be confirmed when enableUnconfirmedLogin is true', function () use ($form) {
            \Yii::$app->getModule('account')->enableUnconfirmedLogin = true;
            $account = \Yii::createObject(Account::className());
            test::double($account, ['getIsConfirmed' => true]);
            test::double(Finder::className(), ['findAccountByUsernameOrEmail' => $account]);
            verify($form->validate())->true();
            test::double($account, ['getIsConfirmed' => false]);
            verify($form->validate())->true();
        });

        $this->specify('Account should be confirmed when enableUnconfirmedLogin is true', function () use ($form) {
            \Yii::$app->getModule('account')->enableUnconfirmedLogin = false;
            verify($form->validate())->false();
            verify($form->getErrors('login'))->contains('You need to confirm your email address');
            $account = \Yii::createObject(Account::className());
            test::double($account, ['getIsConfirmed' => true]);
            test::double(Finder::className(), ['findAccountByUsernameOrEmail' => $account]);
            verify($form->validate())->true();
        });

        $this->specify('Account should not be blocked', function () use ($form) {
            $account = \Yii::createObject(Account::className());
            test::double($account, ['getIsBlocked' => true]);
            test::double(Finder::className(), ['findAccountByUsernameOrEmail' => $account]);
            verify($form->validate())->false();
            verify($form->getErrors('login'))->contains('Your account has been blocked');
        });
    }

    /**
     * Tests login method.
     */
    public function testLogin()
    {
        $account = \Yii::createObject(Account::className());
        test::double(Finder::className(), ['findAccountByUsernameOrEmail' => $account]);

        $form = Yii::createObject(LoginForm::className());
        $form->beforeValidate();
        test::double($form, ['validate' => false]);
        verify($form->login())->false();

        test::double($form, ['validate' => true]);
        test::double(\yii\web\User::className(), ['login' => false]);
        verify($form->login())->false();

        test::double(\yii\web\User::className(), ['login' => true]);
        verify($form->login())->true();
    }
}
