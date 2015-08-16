<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use dektrium\rbac\widgets\Assignments;

/**
 * @var yii\web\View 				$this
 * @var wartron\yii2account\models\User 	$user
 */

?>

<?php $this->beginContent('@wartron/yii2account/views/admin/update.php', ['user' => $user]) ?>

<?= yii\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-info',
    ],
    'body' => Yii::t('account', 'You can assign multiple roles or permissions to user by using the form below'),
]) ?>

<?= Assignments::widget(['userId' => $user->id]) ?>

<?php $this->endContent() ?>
