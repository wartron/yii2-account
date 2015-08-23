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
use wartron\yii2account\models\SettingsForm;
use wartron\yii2account\Module;
use wartron\yii2account\traits\AjaxValidationTrait;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * SettingsController manages updating user settings (e.g. profile, email and password).
 *
 * @property \wartron\yii2account\Module $module
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class SettingsController extends Controller
{
    use AjaxValidationTrait;

    /** @inheritdoc */
    public $defaultAction = 'profile';

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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'disconnect' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['profile', 'account', 'confirm', 'networks', 'organizations', 'disconnect'],
                        'roles'   => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Shows profile settings form.
     *
     * @return string|\yii\web\Response
     */
    public function actionProfile()
    {
        $model = $this->finder->findProfileById(Yii::$app->user->identity->getId());

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('account', 'Your profile has been updated'));

            return $this->refresh();
        }

        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    /**
     * Displays page where user can update account settings (username, email or password).
     *
     * @return string|\yii\web\Response
     */
    public function actionAccount()
    {
        /** @var SettingsForm $model */
        $model = Yii::createObject(SettingsForm::className());

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('account', 'Your account details have been updated'));

            return $this->refresh();
        }

        return $this->render('account', [
            'model' => $model,
        ]);
    }

    /**
     * Attempts changing user's password.
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

        if ($account === null || $this->module->emailChangeStrategy == Module::STRATEGY_INSECURE) {
            throw new NotFoundHttpException();
        }

        $account->attemptEmailChange($code);

        return $this->redirect(['account']);
    }

    /**
     * Displays list of connected network accounts.
     *
     * @return string
     */
    public function actionNetworks()
    {
        return $this->render('networks', [
            'user' => Yii::$app->user->identity,
        ]);
    }

    /**
     * Disconnects a network account from user.
     *
     * @param int $id
     *
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDisconnect($id)
    {
        $accountNetwork = $this->finder->findAccountNetwork()->byId($id)->one();

        if ($accountNetwork === null) {
            throw new NotFoundHttpException();
        }
        if ($accountNetwork->account_id != Yii::$app->user->id) {
            throw new ForbiddenHttpException();
        }
        $accountNetwork->delete();

        return $this->redirect(['networks']);
    }

    /**
     * Displays list of organizations.
     *
     * @return string
     */
    public function actionOrganizations()
    {
        return $this->render('organizations', [
            'user' => Yii::$app->user->identity,
        ]);
    }

}
