<?php

use wartron\yii2account\tests\FunctionalTester;
use tests\codeception\_pages\ResendPage;

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that resending of confirmation tokens works');

$page = ResendPage::openBy($I);

$I->amGoingTo('try to resend token to non-existent account');
$page->resend('foo@example.com');
$I->see('Email is invalid');

$I->amGoingTo('try to resend token to already confirmed account');
$account = $I->getFixture('account')->getModel('account');
$page->resend($account->email);
$I->see('This account has already been confirmed');

$I->amGoingTo('try to resend token to unconfirmed account');
$account = $I->getFixture('account')->getModel('unconfirmed');
$page->resend($account->email);
$I->see('A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.');
