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

$this->beginContent('@wartron/yii2account/views/admin/update.php', ['user' => $user]);



echo DetailView::widget([
    'model'         =>  $user,
    'attributes'    =>  [
        'id:hex',
        'username',
        'email',
        [
            'label' =>  Yii::t('account', 'Registration time'),
            'type'  =>  'raw',
            'value' =>  Yii::t('account', '{0, date, MMMM dd, YYYY HH:mm}', [$user->created_at]),
        ],
        [
            'label' =>  Yii::t('account', 'Confirmation status'),
            'type'  =>  'raw',
            'value' =>  $user->isConfirmed ?
                Yii::t('account', 'Confirmed at {0, date, MMMM dd, YYYY HH:mm}', [$user->confirmed_at]) :
                Yii::t('account', 'Unconfirmed'),
        ],
        [
            'label' =>  Yii::t('account', 'Block status'),
            'type'  =>  'raw',
            'value' =>  $user->isBlocked ?
                Yii::t('account', 'Blocked at {0, date, MMMM dd, YYYY HH:mm}', [$user->blocked_at]) :
                Yii::t('account', 'Not blocked'),
        ],
        'registration_ip',
    ],
]);


$this->endContent();
