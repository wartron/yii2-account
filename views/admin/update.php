<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use wartron\yii2account\models\Account;
use yii\bootstrap\Nav;
use yii\web\View;

/**
 * @var View 	$this
 * @var Account 	$user
 * @var string 	$content
 */

$this->title = Yii::t('account', 'Update user account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('account', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= $this->render('/_alert', [
    'module' => Yii::$app->getModule('account'),
]) ?>

<?= $this->render('_menu') ?>

<div class="row">
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Nav::widget([
                    'options' => [
                        'class' => 'nav-pills nav-stacked',
                    ],
                    'items' => [
                        ['label' => Yii::t('account', 'Account details'), 'url' => ['/account/admin/update', 'id' => $user->id]],
                        ['label' => Yii::t('account', 'Profile details'), 'url' => ['/account/admin/update-profile', 'id' => $user->id]],
                        ['label' => Yii::t('account', 'Information'), 'url' => ['/account/admin/info', 'id' => $user->id]],
                        [
                            'label' => Yii::t('account', 'Assignments'),
                            'url' => ['/account/admin/assignments', 'id' => $user->id],
                            'visible' => isset(Yii::$app->extensions['wartron/yii2-account-rbac-uuid']),
                        ],
                        '<hr>',
                        [
                            'label' => Yii::t('account', 'Confirm'),
                            'url'   => ['/account/admin/confirm', 'id' => $user->id],
                            'visible' => !$user->isConfirmed,
                            'linkOptions' => [
                                'class' => 'text-success',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('account', 'Are you sure you want to confirm this user?'),
                            ],
                        ],
                        [
                            'label' => Yii::t('account', 'Block'),
                            'url'   => ['/account/admin/block', 'id' => $user->id],
                            'visible' => !$user->isBlocked,
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('account', 'Are you sure you want to block this user?'),
                            ],
                        ],
                        [
                            'label' => Yii::t('account', 'Unblock'),
                            'url'   => ['/account/admin/block', 'id' => $user->id],
                            'visible' => $user->isBlocked,
                            'linkOptions' => [
                                'class' => 'text-success',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('account', 'Are you sure you want to unblock this user?'),
                            ],
                        ],
                        [
                            'label' => Yii::t('account', 'Delete'),
                            'url'   => ['/account/admin/delete', 'id' => $user->id],
                            'linkOptions' => [
                                'class' => 'text-danger',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('account', 'Are you sure you want to delete this user?'),
                            ],
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
