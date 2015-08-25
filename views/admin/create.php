<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Nav;
use yii\helpers\Html;

/**
 * @var yii\web\View 				$this
 * @var wartron\yii2account\models\User 	$user
 */

$this->title = Yii::t('account', 'Create a account account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('account', 'Accounts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$module = Yii::$app->getModule('account');

echo $this->render('/_alert', [
    'module' => $module,
]);

echo $this->render('_menu', [
    'module' => $module,
]);

?>

<div class="row">
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Nav::widget([
                    'options' => [
                        'class' => 'nav-pills nav-stacked',
                    ],
                    'items' => [
                        ['label' => Yii::t('account', 'Account details'), 'url' => ['/account/admin/create']],
                        ['label' => Yii::t('account', 'Profile details'), 'options' => [
                            'class' => 'disabled',
                            'onclick' => 'return false;',
                        ]],
                        ['label' => Yii::t('account', 'Information'), 'options' => [
                            'class' => 'disabled',
                            'onclick' => 'return false;',
                        ]],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="alert alert-info">
                    <?= Yii::t('account', 'Credentials will be sent to the account by email') ?>.
                    <?= Yii::t('account', 'A password will be generated automatically if not provided') ?>.
                </div>
                <?php $form = ActiveForm::begin([
                    'layout' => 'horizontal',
                    'enableAjaxValidation'   => true,
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-sm-9',
                        ],
                    ],
                ]); ?>

                <?= $this->render('_user', ['form' => $form, 'account' => $account]) ?>

                <div class="form-group">
                    <div class="col-lg-offset-3 col-lg-9">
                        <?= Html::submitButton(Yii::t('account', 'Save'), ['class' => 'btn btn-block btn-success']) ?>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
