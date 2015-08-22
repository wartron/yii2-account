<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\bootstrap\Nav;

?>

<?= Nav::widget([
    'options' => [
        'class' => 'nav-tabs',
        'style' => 'margin-bottom: 15px',
    ],
    'items' => [
        [
            'label'   => Yii::t('account', 'Accounts'),
            'url'     => ['/account/admin/index'],
        ],
        [
            'label'   => Yii::t('account', 'Roles'),
            'url'     => ['/rbac/role/index'],
            'visible' => isset(Yii::$app->extensions['wartron/yii2-account-rbac-uuid']),
        ],
        [
            'label' => Yii::t('account', 'Permissions'),
            'url'   => ['/rbac/permission/index'],
            'visible' => isset(Yii::$app->extensions['wartron/yii2-account-rbac-uuid']),
        ],
        [
            'label' => Yii::t('account', 'Create'),
            'items' => [
                [
                    'label'   => Yii::t('account', 'New Account'),
                    'url'     => ['/account/admin/create'],
                ],
                [
                    'label' => Yii::t('account', 'New role'),
                    'url'   => ['/rbac/role/create'],
                    'visible' => isset(Yii::$app->extensions['wartron/yii2-account-rbac-uuid']),
                ],
                [
                    'label' => Yii::t('account', 'New permission'),
                    'url'   => ['/rbac/permission/create'],
                    'visible' => isset(Yii::$app->extensions['wartron/yii2-account-rbac-uuid']),
                ],
            ],
        ],
    ],
]) ?>
