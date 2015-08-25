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
use wartron\yii2uuid\helpers\Uuid;

/**
 * @var View 	$this
 * @var Account 	$account
 * @var string 	$content
 */

$this->title = Yii::t('account', 'Update account') . " - " . $title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('account', 'Accounts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $account->username , 'url' => ['/account/admin/info', 'id' => Uuid::uuid2str($account->id)] ];
$this->params['breadcrumbs'][] = $this->title;

$module = Yii::$app->getModule('account');

echo $this->render('/_alert', [
    'module' => $module,
]);

echo $this->render('_menu', [
    'module' => $module,
]);

$items = [];


$items[] = [
    'label'     =>  Yii::t('account', 'Information'),
    'url'       =>  ['/account/admin/info', 'id' => Uuid::uuid2str($account->id)]
];
$items[] = [
    'label'     =>  Yii::t('account', 'Account details'),
    'url'       =>  ['/account/admin/update', 'id' => Uuid::uuid2str($account->id)]
];
$items[] = [
    'label'     =>  Yii::t('account', 'Profile details'),
    'url'       =>  ['/account/admin/update-profile', 'id' => Uuid::uuid2str($account->id)]
];

if( $module->hasRbac() && $module->can('backend-accounts-rbac') )
{
    $items[] = [
        'label'     =>  Yii::t('account', 'Assignments'),
        'url'       =>  ['/account/admin/assignments', 'id' => Uuid::uuid2str($account->id)],
    ];
}

if( $module->hasBilling() && $module->can('backend-accounts-billing') )
{
    $items[] = '<hr>';

    $items[] = [
        'label'     =>  Yii::t('account', 'Billing'),
        'url'       =>  ['/account/admin/billing', 'id' => Uuid::uuid2str($account->id)],
    ];
    $items[] = [
        'label'     =>  Yii::t('account', 'Payments'),
        'url'       =>  ['/account/admin/billing-payments', 'id' => Uuid::uuid2str($account->id)],
    ];
}

$items[] = '<hr>';

if( !$account->isConfirmed && $module->can('backend-accounts-confirm') )
{
    $items[] = [
        'label'         =>  Yii::t('account', 'Confirm'),
        'url'           =>  ['/account/admin/confirm', 'id' => Uuid::uuid2str($account->id)],
        'linkOptions'   =>  [
            'class'         =>  'text-success',
            'data-method'   =>  'post',
            'data-confirm'  =>  Yii::t('account', 'Are you sure you want to confirm this account?'),
        ],
    ];
}

if( !$account->isBlocked && $module->can('backend-accounts-block') )
{
    $items[] = [
        'label'         =>  Yii::t('account', 'Block'),
        'url'           =>  ['/account/admin/block', 'id' => Uuid::uuid2str($account->id)],
        'linkOptions'   =>  [
            'class'         =>  'text-danger',
            'data-method'   =>  'post',
            'data-confirm'  =>  Yii::t('account', 'Are you sure you want to block this account?'),
        ],
    ];
}

if( $account->isBlocked && $module->can('backend-accounts-block') )
{
    $items[] = [
        'label'         =>  Yii::t('account', 'Unblock'),
        'url'           =>  ['/account/admin/block', 'id' => Uuid::uuid2str($account->id)],
        'linkOptions'   =>  [
            'class'         =>  'text-success',
            'data-method'   =>  'post',
            'data-confirm'  =>  Yii::t('account', 'Are you sure you want to unblock this account?'),
        ],
    ];
}

if( $module->can('backend-accounts-delete') )
{
    $items[] = [
        'label'         =>  Yii::t('account', 'Delete'),
        'url'           =>  ['/account/admin/delete', 'id' => Uuid::uuid2str($account->id)],
        'linkOptions'   =>  [
            'class'         =>  'text-danger',
            'data-method'   =>  'post',
            'data-confirm'  =>  Yii::t('account', 'Are you sure you want to delete this account?'),
        ],
    ];
}




?>
<div class="row">
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Nav::widget([
                    'options' => [
                        'class' => 'nav-pills nav-stacked',
                    ],
                    'items' => $items,
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
