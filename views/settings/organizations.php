<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;

/*
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 */

$this->title = Yii::t('account', 'Organizations');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('account')]) ?>

<div class="row">
    <div class="col-md-3">
        <?= $this->render('_menu') ?>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="/account/organizations/new" class="btn btn-sm boxed-group-action">New organization</a>
                <?= Html::encode($this->title) ?>
            </div>
            <div class="panel-body">


            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <?= Yii::t('account', 'Transform account') ?>
            </div>
            <div class="panel-body">
                <div class="alert alert-warning">
                    <p><?= Yii::t('account', 'You cannot transform this account into an organization until you leave all organizations that you belong to.') ?>.</p>
                </div>

            </div>
        </div>
    </div>
</div>
