<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\widgets\Menu;

/** @var wartron\yii2account\models\User $user */
$user = Yii::$app->user->identity;
$networksVisible = count(Yii::$app->authClientCollection->clients) > 0;

$billingVisible = false;
$organizationsVisible = false;
$organizationsNav = false;

$navItems = [
    [
        'label' => Yii::t('account', 'Account'),
        'url' => ['/account/settings/account']
    ],
    [
        'label' => Yii::t('account', 'Profile'),
        'url' => ['/account/settings/profile']
    ],
    [
        'label' => Yii::t('account', 'Networks'),
        'url' => ['/account/settings/networks'],
        'visible' => $networksVisible
    ],
    [
        'label' => Yii::t('account', 'Billing'),
        'url' => ['/account/settings/billing'],
        'visible' => $billingVisible
    ],
    [
        'label' => Yii::t('account', 'Organizations'),
        'url' => ['/account/settings/organizations'],
        'visible' => $organizationsVisible
    ],
];

$orgNavItems = [
    [
        'label' => 'test org',
        'url' => ['/account/organization/settings']
    ],
];

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <img src="http://gravatar.com/avatar/<?= $user->profile->gravatar_id ?>?s=24" class="img-rounded" alt="<?= $user->username ?>" />
            <?= $user->username ?>
        </h3>
    </div>
    <div class="panel-body">
        <?= Menu::widget([
            'options' => [
                'class' => 'nav nav-pills nav-stacked',
            ],
            'items' => $navItems,
        ]) ?>
    </div>
</div>


<?php
if($organizationsNav){
?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Organizations</h3>
        </div>
        <div class="panel-body">
            <?= Menu::widget([
                'options' => [
                    'class' => 'nav nav-pills nav-stacked',
                ],
                'items' => $orgNavItems,
            ]) ?>
        </div>
    </div>

<?php
}