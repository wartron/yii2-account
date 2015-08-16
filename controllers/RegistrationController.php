<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wartron\yii2account\controllers;

use wartron\yii2account\Finder;
use wartron\yii2account\models\RegistrationForm;
use wartron\yii2account\models\ResendForm;
use wartron\yii2account\models\Account;
use wartron\yii2account\traits\AjaxValidationTrait;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * RegistrationController is responsible for all registration process, which includes registration of a new account,
 * resending confirmation tokens, email confirmation and registration via social networks.
 *
 * @property \wartron\yii2account\Module $module
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RegistrationController extends Controller
{
    use AjaxValidationTrait;

    /** @var Finder */
    protected $finder;

    /**
     * @param string           $id
     * @param \yii\base\Module $module
     * @param Finder           $finder
     * @param array            $config
     */
    public function __construct($id, $module, Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($id, $module, $config);
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['register', 'connect'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['confirm', 'resend'], 'roles' => ['?', '@']],
                ],
            ],
        ];
    }

    /**
     * Displays the registration page.
     * After successful registration if enableConfirmation is enabled shows info message otherwise redirects to home page.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionRegister()
    {
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }

        /** @var RegistrationForm $model */
        $model = Yii::createObject(RegistrationForm::className());

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            return $this->render('/message', [
                'title'  => Yii::t('account', 'Your account has been created'),
                'module' => $this->module,
            ]);
        }

        return $this->render('register', [
            'model'  => $model,
            'module' => $this->module,
        ]);
    }

    /**
     * Displays page where user can create new account that will be connected to social account.
     *
     * @param string $code
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionConnect($code)
    {
        $accountNetwork = $this->finder->findAccountNetwork()->byCode($code)->one();

        if ($accountNetwork === null || $accountNetwork->getIsConnected()) {
            throw new NotFoundHttpException();
        }

        /** @var User $user */
        $account = Yii::createObject([
            'class'    => Account::className(),
            'scenario' => 'connect',
            'username' => $accountNetwork->username,
            'email'    => $accountNetwork->email,
        ]);

        if ($account->load(Yii::$app->request->post()) && $account->create()) {
            $accountNetwork->connect($account);
            Yii::$app->user->login($account, $this->module->rememberFor);
            return $this->goBack();
        }

        return $this->render('connect', [
            'model'   => $account,
            'accountNetwork' => $accountNetwork,
        ]);
    }

    /**
     * Confirms user's account. If confirmation was successful logs the user and shows success message. Otherwise
     * shows error message.
     *
     * @param int    $id
     * @param string $code
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionConfirm($id, $code)
    {
        $account = $this->finder->findAccountById($id);

        if ($user === null || $this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        $user->attemptConfirmation($code);

        return $this->render('/message', [
            'title'  => Yii::t('account', 'Account confirmation'),
            'module' => $this->module,
        ]);
    }

    /**
     * Displays page where user can request new confirmation token. If resending was successful, displays message.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionResend()
    {
        if ($this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        /** @var ResendForm $model */
        $model = Yii::createObject(ResendForm::className());

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->resend()) {
            return $this->render('/message', [
                'title'  => Yii::t('account', 'A new confirmation link has been sent'),
                'module' => $this->module,
            ]);
        }

        return $this->render('resend', [
            'model' => $model,
        ]);
    }
}
