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
 * @var wartron\yii2account\models\User
 */
?>
<?= Yii::t('account', 'Hello') ?>,

<?= Yii::t('account', 'Your account on {0} has been created', Yii::$app->name) ?>.
<?php if ($module->enableGeneratingPassword): ?>
<?= Yii::t('account', 'We have generated a password for you') ?>:
<?= $user->password ?>
<?php endif ?>

<?php if ($token !== null): ?>
<?= Yii::t('account', 'In order to complete your registration, please click the link below') ?>.

<?= $token->url ?>

<?= Yii::t('account', 'If you cannot click the link, please try pasting the text into your browser') ?>.
<?php endif ?>

<?= Yii::t('account', 'If you did not make this request you can ignore this email') ?>.
