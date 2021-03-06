<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

/**
 * @var yii\widgets\ActiveForm 		$form
 * @var wartron\yii2account\models\User 	$user
 */




echo $form->field($account, 'email')->textInput(['maxlength' => 255]);
echo $form->field($account, 'username')->textInput(['maxlength' => 255]);
echo $form->field($account, 'password')->passwordInput();
