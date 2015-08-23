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


$this->beginContent('@wartron/yii2account/views/admin/update.php', ['user' => $user]);

?>

<table class="table">
    <tr>
        <td><strong><?= Yii::t('account', 'Registration time') ?>:</strong></td>
        <td><?= Yii::t('account', '{0, date, MMMM dd, YYYY HH:mm}', [$user->created_at]) ?></td>
    </tr>
    <?php if ($user->registration_ip !== null): ?>
        <tr>
            <td><strong><?= Yii::t('account', 'Registration IP') ?>:</strong></td>
            <td><?= $user->registration_ip ?></td>
        </tr>
    <?php endif ?>
    <tr>
        <td><strong><?= Yii::t('account', 'Confirmation status') ?>:</strong></td>
        <?php if ($user->isConfirmed): ?>
            <td class="text-success"><?= Yii::t('account', 'Confirmed at {0, date, MMMM dd, YYYY HH:mm}', [$user->confirmed_at]) ?></td>
        <?php else: ?>
            <td class="text-danger"><?= Yii::t('account', 'Unconfirmed') ?></td>
        <?php endif ?>
    </tr>
    <tr>
        <td><strong><?= Yii::t('account', 'Block status') ?>:</strong></td>
        <?php if ($user->isBlocked): ?>
            <td class="text-danger"><?= Yii::t('account', 'Blocked at {0, date, MMMM dd, YYYY HH:mm}', [$user->blocked_at]) ?></td>
        <?php else: ?>
            <td class="text-success"><?= Yii::t('account', 'Not blocked') ?></td>
        <?php endif ?>
    </tr>
</table>

<?php

$this->endContent();

?>
