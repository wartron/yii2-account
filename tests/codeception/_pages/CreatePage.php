<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents admin create page.
 *
 * @property \FunctionalTester $actor
 */
class CreatePage extends BasePage
{
    /** @inheritdoc */
    public $route = '/account/admin/create';

    /**
     * @param $username
     * @param $email
     * @param $password
     */
    public function create($username, $email, $password)
    {
        $this->actor->fillField('#account-username', $username);
        $this->actor->fillField('#account-email', $email);
        $this->actor->fillField('#account-password', $password);
        $this->actor->click('Save');
    }
}
