<?php

namespace wartron\yii2account\tests;

use wartron\yii2account\models\ResendForm;
use tests\codeception\_fixtures\AccountFixture;
use yii\codeception\TestCase;

class ResendFormTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'account' => [
                'class' => AccountFixture::className(),
                'dataFile' => '@tests/codeception/_fixtures/data/init_account.php',
            ],
        ];
    }

    public function testValidateEmail()
    {
        $form = \Yii::createObject(ResendForm::className());
        $account = $this->getFixture('account')->getModel('account');
        $form->setAttributes([
            'email' => $account->email,
        ]);
        $this->assertFalse($form->validate());

        $form = \Yii::createObject(ResendForm::className());
        $account = $this->getFixture('account')->getModel('unconfirmed');
        $form->setAttributes([
            'email' => $account->email,
        ]);
        $this->assertTrue($form->validate());
    }
}
