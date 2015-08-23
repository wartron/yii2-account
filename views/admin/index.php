<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use wartron\yii2account\models\AccountSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\web\View;
use yii\widgets\Pjax;
use wartron\yii2uuid\helpers\Uuid;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var AccountSearch $searchModel
 */

$this->title = Yii::t('account', 'Manage accounts');
$this->params['breadcrumbs'][] = $this->title;

$module = Yii::$app->getModule('account');

echo $this->render('/_alert', [
    'module' => $module,
]);

echo $this->render('/admin/_menu');

Pjax::begin();

echo GridView::widget([
    'dataProvider' 	=> $dataProvider,
    'filterModel'  	=> $searchModel,
    'layout'  		=> "{items}\n{pager}",
    'columns' => [
        [
            'attribute' => 'type',
            'filter' => [
                1   =>  'User',
                2   =>  'Organization',
            ],
        ],
        [
            'attribute' => 'username',
            'value' => function ($model) {
                return Html::a($model->username, ['update', 'id' =>  Uuid::uuid2str($model->id)]);
            },
            'format' => 'raw',
        ],
        'email',
        [
            'attribute' => 'registration_ip',
            'value' => function ($model) {
                return $model->registration_ip == null
                    ? '<span class="not-set">' . Yii::t('account', '(not set)') . '</span>'
                    : $model->registration_ip;
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'created_at',
            'value' => function ($model) {
                if (extension_loaded('intl')) {
                    return Yii::t('account', '{0, date, MMMM dd, YYYY HH:mm}', [$model->created_at]);
                } else {
                    return date('Y-m-d G:i:s', $model->created_at);
                }
            },
            'filter' => DatePicker::widget([
                'model'      => $searchModel,
                'attribute'  => 'created_at',
                'dateFormat' => 'php:Y-m-d',
                'options' => [
                    'class' => 'form-control',
                ],
            ]),
        ],
        [
            'header' => Yii::t('account', 'Confirmation'),
            'value' => function ($model) {
                if ($model->isConfirmed) {
                    return '<div class="text-center"><span class="text-success">' . Yii::t('account', 'Confirmed') . '</span></div>';
                } else {
                    return Html::a(Yii::t('account', 'Confirm'), ['confirm', 'id' => Uuid::uuid2str($model->id) ], [
                        'class' => 'btn btn-xs btn-success btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('account', 'Are you sure you want to confirm this user?'),
                    ]);
                }
            },
            'format' => 'raw',
            'visible' => $module->enableConfirmation && $module->can('backend-accounts-confirm'),
        ],
        [
            'header' => Yii::t('account', 'Block status'),
            'value' => function ($model) {
                if ($model->isBlocked) {
                    return Html::a(Yii::t('account', 'Unblock'), ['block', 'id' => Uuid::uuid2str($model->id) ], [
                        'class' => 'btn btn-xs btn-success btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('account', 'Are you sure you want to unblock this user?'),
                    ]);
                } else {
                    return Html::a(Yii::t('account', 'Block'), ['block', 'id' => Uuid::uuid2str($model->id) ], [
                        'class' => 'btn btn-xs btn-danger btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('account', 'Are you sure you want to block this user?'),
                    ]);
                }
            },
            'format' => 'raw',
            'visible' => $module->can('backend-accounts-block'),
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => ($module->can('backend-accounts-delete') ? '{update} {delete}' : '{update}'),
        ],
    ],
]);

Pjax::end();