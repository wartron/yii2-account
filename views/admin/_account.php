<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/*
 * @var yii\web\View $this
 * @var wartron\yii2account\models\User $user
 */

$this->beginContent('@wartron/yii2account/views/admin/update.php', [
    'title'     =>  'Account',
    'account'   =>  $account,
]);

$form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation'   => true,
    'enableClientValidation' => false,
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
]);

echo $this->render('_user', ['form' => $form, 'account' => $account]);

?>
<div class="form-group">
    <div class="col-lg-offset-3 col-lg-9">
        <?= Html::submitButton(Yii::t('account', 'Update'), ['class' => 'btn btn-block btn-success']); ?>
    </div>
</div>

<?php

ActiveForm::end();

$this->endContent();
