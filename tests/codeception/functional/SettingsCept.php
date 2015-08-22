<?php

use wartron\yii2account\tests\FunctionalTester;
use wartron\yii2account\models\Token;
use wartron\yii2account\models\Account;
use tests\codeception\_pages\LoginPage;
use tests\codeception\_pages\SettingsPage;
use yii\helpers\Html;

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that account settings page work');

$page = LoginPage::openBy($I);
$account = $I->getFixture('account')->getModel('account');
$page->login($account->username, 'qwerty');

$page = SettingsPage::openBy($I);

$I->amGoingTo('check that current password is required and must be valid');
$page->update($account->email, $account->username, 'wrong');
$I->see('Current password is not valid');

$I->amGoingTo('check that email is changing properly');
$page->update('new_user@example.com', $account->username, 'qwerty');
$I->seeRecord(Account::className(), ['email' => $account->email, 'unconfirmed_email' => 'new_user@example.com']);
$I->see('A confirmation message has been sent to your new email address');
$account  = $I->grabRecord(Account::className(), ['id' => $account->id]);
$token = $I->grabRecord(Token::className(), ['account_id' => $account->id, 'type' => Token::TYPE_CONFIRM_NEW_EMAIL]);
$I->seeInEmail(Html::encode($token->getUrl()));
$I->seeInEmailRecipients($account->unconfirmed_email);

Yii::$app->user->logout();

$I->amGoingTo('log in using new email address before clicking the confirmation link');
$page = LoginPage::openBy($I);
$page->login('new_user@example.com', 'qwerty');
$I->see('Invalid login or password');

$I->amGoingTo('log in using new email address after clicking the confirmation link');
$account->attemptEmailChange($token->code);
$page->login('new_user@example.com', 'qwerty');
$I->see('Logout');
$I->seeRecord(Account::className(), [
    'id' => 1,
    'email' => 'new_user@example.com',
    'unconfirmed_email' => null,
]);

$I->amGoingTo('reset email changing process');
$page = SettingsPage::openBy($I);
$page->update('user@example.com', $account->username, 'qwerty');
$I->see('A confirmation message has been sent to your new email address');
$I->seeRecord(Account::className(), [
    'id'    => 1,
    'email' => 'new_user@example.com',
    'unconfirmed_email' => 'user@example.com',
]);
$page->update('new_user@example.com', $account->username, 'qwerty');
$I->see('Your account details have been updated');
$I->seeRecord(Account::className(), [
    'id'    => 1,
    'email' => 'new_user@example.com',
    'unconfirmed_email' => null,
]);
$I->amGoingTo('change username and password');
$page->update('new_user@example.com', 'nickname', 'qwerty', '123654');
$I->see('Your account details have been updated');
$I->seeRecord(Account::className(), [
    'username' => 'nickname',
    'email'    => 'new_user@example.com',
]);

Yii::$app->user->logout();

$I->amGoingTo('login with new credentials');
$page = LoginPage::openBy($I);
$page->login('nickname', '123654');
$I->see('Logout');
