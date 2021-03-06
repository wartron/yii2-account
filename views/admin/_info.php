<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

/**
 * @var yii\web\View
 * @var wartron\yii2account\models\User
 */

use yii\grid\GridView;
use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\data\ArrayDataProvider;


$module = Yii::$app->getModule('account');


$this->beginContent('@wartron/yii2account/views/admin/update.php', [
    'title'     =>  'Information',
    'account'   =>  $account,
]);

echo "<h4>Account</h4>";

echo DetailView::widget([
    'model'         =>  $account,
    'attributes'    =>  [
        'id:hex',
        'username',
        'email',
        [
            'label' =>  Yii::t('account', 'Registration time'),
            'type'  =>  'raw',
            'value' =>  Yii::t('account', '{0, date, MMMM dd, YYYY HH:mm}', [$account->created_at]),
        ],
        [
            'label' =>  Yii::t('account', 'Confirmation status'),
            'type'  =>  'raw',
            'value' =>  $account->isConfirmed ?
                Yii::t('account', 'Confirmed at {0, date, MMMM dd, YYYY HH:mm}', [$account->confirmed_at]) :
                Yii::t('account', 'Unconfirmed'),
        ],
        [
            'label' =>  Yii::t('account', 'Block status'),
            'type'  =>  'raw',
            'value' =>  $account->isBlocked ?
                Yii::t('account', 'Blocked at {0, date, MMMM dd, YYYY HH:mm}', [$account->blocked_at]) :
                Yii::t('account', 'Not blocked'),
        ],
        'registration_ip',
    ],
]);

echo "<h4>Profile</h4>";

echo DetailView::widget([
    'model'         =>  $account->profile,
    'attributes'    =>  [
        'name',
        'public_email',
        'gravatar_email',
        'location',
        'website',
        'bio:ntext',
    ],
]);



if( $module->hasRbac() ) {
    $authManager = Yii::$app->getAuthManager();

    $roles = $authManager->getRolesByUser($account->id);
    $rolesDP = new ArrayDataProvider([
        'allModels' => $roles,
        'sort' => [
            'sortParam'     =>  'sortRole',
            'attributes'    =>  ['name', 'description'],
        ],
    ]);
    echo "<h4>Roles</h4>";
    echo GridView::widget([
        'dataProvider' => $rolesDP,
         'columns' => [
            'name',
            'description',
        ]
    ]);

    $permissions = $authManager->getPermissionsByUser($account->id);
    $permissionsDP = new ArrayDataProvider([
        'allModels' => $permissions,
        'sort' => [
            'sortParam'     =>  'sortPermission',
            'attributes'    =>  ['name', 'description'],
        ],
    ]);
    echo "<h4>Permissions</h4>";
    echo GridView::widget([
        'dataProvider' => $permissionsDP,
         'columns' => [
            'name',
            'description',
        ]
    ]);

}


$this->endContent();
