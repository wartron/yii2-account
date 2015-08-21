<?php

use wartron\yii2account\tests\FunctionalTester;
use tests\codeception\_pages\UpdatePage;
use tests\codeception\_pages\LoginPage;

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that account update works');

$loginPage = LoginPage::openBy($I);
$account = $I->getFixture('account')->getModel('account');
$loginPage->login($account->email, 'qwerty');

$page = UpdatePage::openBy($I, ['id' => $account->id]);

$page->update('account', 'updated_user@example.com', 'new_pass');
$I->see('Account details have been updated');

Yii::$app->user->logout();
LoginPage::openBy($I)->login('updated_user@example.com', 'new_pass');
$I->see('Logout');
