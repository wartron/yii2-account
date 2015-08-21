<?php

namespace wartron\yii2account\tests;

use AspectMock\Test as test;
use Codeception\Specify;
use wartron\yii2account\Finder;
use wartron\yii2account\Mailer;
use wartron\yii2account\models\RecoveryForm;
use wartron\yii2account\models\Token;
use wartron\yii2account\models\Account;
use Yii;
use yii\codeception\TestCase;
use yii\db\ActiveQuery;

/**
 * Tests for a recovery form.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RecoveryFormTest extends TestCase
{
    use Specify;

    /**
     * Tests recovery request form.
     */
    public function testRecoveryRequest()
    {
        $mailer = test::double(Mailer::className(), ['sendRecoveryMessage' => true]);

        $form = Yii::createObject([
            'class'    => RecoveryForm::className(),
            'scenario' => 'request',
        ]);

        $this->specify('form is not valid when email is empty', function () use ($form) {
            $form->setAttributes(['email' => '']);
            verify($form->validate())->false();
            verify($form->getErrors('email'))->contains('Email cannot be blank.');
        });

        $this->specify('form is not valid when email is incorrect', function () use ($form) {
            $form->setAttributes(['email' => 'foobar']);
            verify($form->validate())->false();
            verify($form->getErrors('email'))->contains('Email is not a valid email address.');
        });

        $this->specify('form is not valid when user does not exist', function () use ($form) {
            test::double(ActiveQuery::className(), ['exists' => false]);
            $form->setAttributes(['email' => 'foobar@example.com']);
            verify($form->validate())->false();
            verify($form->getErrors('email'))->contains('There is no user with this email address');
            test::double(ActiveQuery::className(), ['exists' => true]);
        });

        $this->specify('form is not valid when user is not confirmed', function () use ($form) {
            $account = \Yii::createObject(Account::className());
            test::double($account, ['getIsConfirmed' => false]);
            test::double(Finder::className(), ['findAccountByEmail' => $account]);
            $form->setAttributes(['email' => 'foobar@example.com']);
            verify($form->validate())->false();
            verify($form->getErrors('email'))->contains('You need to confirm your email address');
            test::double($account, ['getIsConfirmed' => true]);
            verify($form->validate())->true();
        });

        $this->specify('sendRecoveryMessage return true if validation succeeded', function () use ($form, $mailer) {
            test::double($form, ['validate' => true]);
            $token = test::double(Token::className(), ['save' => true]);
            $account = \Yii::createObject(['class' => Account::className(), 'id' => 1]);
            test::double(Finder::className(), ['findAccountByEmail' => $account]);
            verify($form->sendRecoveryMessage())->true();
            $token->verifyInvoked('save');
            verify(\Yii::$app->session->getFlash('info'))
                ->equals('An email has been sent with instructions for resetting your password');
            $mailer->verifyInvoked('sendRecoveryMessage');
        });
    }

    /**
     * Tests resetting of password.
     */
    public function testPasswordReset()
    {
        $form = Yii::createObject([
            'class'    => RecoveryForm::className(),
            'scenario' => 'reset',
        ]);

        $this->specify('password is required', function () use ($form) {
            $form->setAttributes(['password' => '']);
            verify($form->validate())->false();
            verify($form->getErrors('password'))->contains('Password cannot be blank.');
        });

        $account  = Yii::createObject(Account::className());
        $umock = test::double($account, ['resetPassword' => true]);
        $token = Yii::createObject(Token::className());
        $tmock = test::double($token, ['delete' => true, 'getUser' => $account]);

        $this->specify('return false if validation fails', function () use ($form) {
            $token = Yii::createObject(Token::className());
            $mock = test::double($form, ['validate' => false]);
            verify($form->resetPassword($token))->false();
            $mock->verifyInvoked('validate');
            test::double($form, ['validate' => true]);
        });

        $this->specify('return false if token is invalid', function () use ($form) {
            $token = Yii::createObject(Token::className());
            $tmock = test::double($token, ['getUser' => null]);
            verify($form->resetPassword($token))->false();
            $tmock->verifyInvoked('getUser');
        });

        $this->specify('method sets correct flash message', function () use ($form) {
            $account  = Yii::createObject(Account::className());
            $umock = test::double($account, ['resetPassword' => true]);
            $token = Yii::createObject(Token::className());
            $tmock = test::double($token, ['delete' => true, 'getUser' => $account]);
            verify($form->resetPassword($token))->true();
            verify(\Yii::$app->session->getFlash('success'))
                ->equals('Your password has been changed successfully.');
            $umock->verifyInvoked('resetPassword');
            $tmock->verifyInvoked('delete');
            test::double($account, ['resetPassword' => false]);
            verify($form->resetPassword($token))->true();
            verify(\Yii::$app->session->getFlash('danger'))
                ->equals('An error occurred and your password has not been changed. Please try again later.');
        });
    }
}
