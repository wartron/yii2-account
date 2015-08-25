<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use wartron\yii2widgets\urlactive\Nav;

echo Nav::widget([
    'options'   =>  [
        'class'     => 'nav-tabs',
        'style'     => 'margin-bottom: 15px',
    ],
    'items' =>  [
        [
            'label'     => Yii::t('account', 'Accounts'),
            'url'       => ['/account/admin/index'],
            'urlActive' => [
                ['/account/admin/info'],
                ['/account/admin/update'],
                ['/account/admin/update-profile'],
                ['/account/admin/assignments'],
                ['/account/admin/create'],
                // from wartron/yii2-account-billing
                ['/account/admin/billing'],
                ['/account/admin/billing-payments'],
            ]
        ],
        [
            'label'     =>  Yii::t('account', 'Roles'),
            'url'       =>  ['/rbac/role/index'],
            'visible'   =>  $module->hasRbac() && $module->can('admin-rbac'),
        ],
        [
            'label'     =>  Yii::t('account', 'Permissions'),
            'url'       =>  ['/rbac/permission/index'],
            'visible'   =>  $module->hasRbac() && $module->can('admin-rbac'),
        ],
        [
            'label' =>  Yii::t('account', 'Create'),
            'items' =>  [
                [
                    'label'     =>  Yii::t('account', 'New Account'),
                    'url'       =>  ['/account/admin/create'],
                ],
                [
                    'label'     =>  Yii::t('account', 'New role'),
                    'url'       =>  ['/rbac/role/create'],
                    'visible'   =>  $module->hasRbac() && $module->can('admin-rbac'),
                ],
                [
                    'label'     =>  Yii::t('account', 'New permission'),
                    'url'       =>  ['/rbac/permission/create'],
                    'visible'   =>  $module->hasRbac() && $module->can('admin-rbac'),
                ],
            ],
        ],
    ],
]);

