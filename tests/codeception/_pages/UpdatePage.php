<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents admin update page.
 *
 * @property \FunctionalTester $actor
 */
class UpdatePage extends BasePage
{
    /** @inheritdoc */
    public $route = '/account/admin/update';

    /**
     * @param $username
     * @param $email
     * @param $password
     */
    public function update($username, $email, $password = null)
    {
        $this->actor->fillField('#account-username', $username);
        $this->actor->fillField('#account-email', $email);
        $this->actor->fillField('#account-password', $password);
        $this->actor->click('Update');
    }
}
