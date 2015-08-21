<?php

if (Yii::$app->user->getIsGuest()) {
    echo \yii\helpers\Html::a('Login', ['/account/security/login']);
    echo \yii\helpers\Html::a('Registration', ['/account/registration/register']);
} else {
    echo \yii\helpers\Html::a('Logout', ['/account/security/logout']);
}

echo $content;
