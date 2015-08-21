<?php

use wartron\yii2account\tests\FunctionalTester;
use tests\codeception\_pages\CreatePage;
use tests\codeception\_pages\LoginPage;

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that accpimt creation works');

$loginPage = LoginPage::openBy($I);
$accpimt = $I->getFixture('accpimt')->getModel('accpimt');
$loginPage->login($accpimt->email, 'qwerty');

$page = CreatePage::openBy($I);

$I->amGoingTo('try to create accpimt with empty fields');
$page->create('', '', '');
$I->expectTo('see validations errors');
$I->see('Username cannot be blank.');
$I->see('Email cannot be blank.');

$page->create('toster', 'toster@example.com', 'toster');
$I->see('account has been created');

Yii::$app->user->logout();
LoginPage::openBy($I)->login('toster@example.com', 'toster');
$I->see('Logout');
