<?php

use wartron\yii2account\tests\FunctionalTester;
use tests\codeception\_pages\RecoveryPage;
use tests\codeception\_pages\LoginPage;
use yii\helpers\Html;
use yii\helpers\Url;
use wartron\yii2account\models\Account;
use wartron\yii2account\models\Token;

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that password recovery works');

$page = RecoveryPage::openBy($I);

$I->amGoingTo('try to request recovery token for unconfirmed account');
$account = $I->getFixture('account')->getModel('unconfirmed');
$page->recover($account->email);
$I->see('You need to confirm your email address');

$I->amGoingTo('try to request recovery token');
$account = $I->getFixture('account')->getModel('account');
$page->recover($account->email);
$I->see('An email has been sent with instructions for resetting your password');
$account = $I->grabRecord(Account::className(), ['email' => $account->email]);
$token = $I->grabRecord(Token::className(), ['account_id' => $account->id, 'type' => Token::TYPE_RECOVERY]);
$I->seeInEmail(Html::encode($token->getUrl()));
$I->seeInEmailRecipients($account->email);

$I->amGoingTo('reset password with invalid token');
$account = $I->getFixture('account')->getModel('account_with_expired_recovery_token');
$token = $I->grabRecord(Token::className(), ['account_id' => $account->id, 'type' => Token::TYPE_RECOVERY]);
$I->amOnPage(Url::toRoute(['/account/recovery/reset', 'id' => $account->id, 'code' => $token->code]));
$I->see('Recovery link is invalid or expired. Please try requesting a new one.');

$I->amGoingTo('reset password');
$account = $I->getFixture('account')->getModel('account_with_recovery_token');
$token = $I->grabRecord(Token::className(), ['account_id' => $account->id, 'type' => Token::TYPE_RECOVERY]);
$I->amOnPage(Url::toRoute(['/account/recovery/reset', 'id' => $account->id, 'code' => $token->code]));
$I->fillField('#recovery-form-password', 'newpass');
$I->click('Finish');
$I->see('Your password has been changed successfully.');

$page = LoginPage::openBy($I);
$page->login($account->email, 'qwerty');
$I->see('Invalid login or password');
$page->login($account->email, 'newpass');
$I->dontSee('Invalid login or password');
