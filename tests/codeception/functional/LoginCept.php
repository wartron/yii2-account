<?php

use wartron\yii2account\tests\FunctionalTester;
use tests\codeception\_pages\LoginPage;

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that login works');

$page = LoginPage::openBy($I);

$I->amGoingTo('try to login with empty credentials');
$page->login('', '');
$I->expectTo('see validations errors');
$I->see('Login cannot be blank.');
$I->see('Password cannot be blank.');

$I->amGoingTo('try to login with unconfirmed account');
$account = $I->getFixture('account')->getModel('unconfirmed');
$page->login($account->email, 'qwerty');
$I->see('You need to confirm your email address');

$I->amGoingTo('try to login with blocked account');
$account = $I->getFixture('account')->getModel('blocked');
$page->login($account->email, 'qwerty');
$I->see('Your account has been blocked');

$I->amGoingTo('try to login with wrong credentials');
$account = $I->getFixture('account')->getModel('account');
$page->login($account->email, 'wrong');
$I->expectTo('see validations errors');
$I->see('Invalid login or password');

$I->amGoingTo('try to login with correct credentials');
$page->login($account->email, 'qwerty');
$I->dontSee('Login');
$I->see('Logout');
