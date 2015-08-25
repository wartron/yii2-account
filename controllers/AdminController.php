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
use wartron\yii2account\models\Profile;
use wartron\yii2account\models\Account;
use wartron\yii2account\models\AccountSearch;
use wartron\yii2account\Module;
use Yii;
use yii\base\ExitException;
use yii\base\Model;
use yii\base\Module as Module2;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use wartron\yii2uuid\helpers\Uuid;
/**
 * AdminController allows you to administrate users.
 *
 * @property Module $module
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com
 */
class AdminController extends Controller
{
    /** @var Finder */
    protected $finder;

    /**
     * @param string  $id
     * @param Module2 $module
     * @param Finder  $finder
     * @param array   $config
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
                    'delete'  => ['post'],
                    'confirm' => ['post'],
                    'block'   => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return Yii::$app->user->identity->getIsAdmin();
                        },
                    ],
                ],
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        if ( $this->module->hasBilling() ) {
            $actions['billing'] = [
                'class' =>  'wartron\yii2account\billing\actions\admin\BillingAction',
            ];
        }

        return $actions;
    }

    /**
     * Lists all Account models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'actions-redirect');
        $searchModel  = Yii::createObject(AccountSearch::className());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * Creates a new Account model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        /** @var Account $account */
        $account = Yii::createObject([
            'class'    => Account::className(),
            'scenario' => 'create',
        ]);

        $this->performAjaxValidation($account);

        if ($account->load(Yii::$app->request->post()) && $account->create()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('account', 'Account has been created'));

            return $this->redirect(['update', 'id' => Uuid::uuid2str($account->id)]);
        }

        return $this->render('create', [
            'user' => $account,
        ]);
    }

    /**
     * Updates an existing Account model.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        Url::remember('', 'actions-redirect');
        $account = $this->findModel($id);
        $account->scenario = 'update';

        $this->performAjaxValidation($account);

        if ($account->load(Yii::$app->request->post()) && $account->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('account', 'Account details have been updated'));

            return $this->refresh();
        }

        return $this->render('_account', [
            'user' => $account,
        ]);
    }

    /**
     * Updates an existing profile.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdateProfile($id)
    {
        Url::remember('', 'actions-redirect');
        $account    = $this->findModel($id);
        $profile = $account->profile;

        if ($profile == null) {
            $profile = Yii::createObject(Profile::className());
            $profile->link('account', $account);
        }

        $this->performAjaxValidation($profile);

        if ($profile->load(Yii::$app->request->post()) && $profile->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('account', 'Profile details have been updated'));

            return $this->refresh();
        }

        return $this->render('_profile', [
            'user'    => $account,
            'profile' => $profile,
        ]);
    }

    /**
     * Shows information about user.
     *
     * @param int $id
     *
     * @return string
     */
    public function actionInfo($id)
    {
        Url::remember('', 'actions-redirect');
        $account = $this->findModel($id);

        return $this->render('_info', [
            'user' => $account,
        ]);
    }

    /**
     * If "wartron/yii2-account-rbac-uuid" extension is installed, this page displays form
     * where user can assign multiple auth items to user.
     *
     * @param int $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAssignments($id)
    {
        if (!isset(Yii::$app->extensions['wartron/yii2-account-rbac-uuid'])) {
            throw new NotFoundHttpException();
        }
        Url::remember('', 'actions-redirect');
        $account = $this->findModel($id);

        return $this->render('_assignments', [
            'user' => $account,
        ]);
    }

    /**
     * Confirms the User.
     *
     * @param int $id
     *
     * @return Response
     */
    public function actionConfirm($id)
    {
        $this->findModel($id)->confirm();
        Yii::$app->getSession()->setFlash('success', Yii::t('account', 'User has been confirmed'));

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        if ($id == Yii::$app->user->getId()) {
            Yii::$app->getSession()->setFlash('danger', Yii::t('account', 'You can not remove your own account'));
        } else {
            $this->findModel($id)->delete();
            Yii::$app->getSession()->setFlash('success', Yii::t('account', 'User has been deleted'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Blocks the user.
     *
     * @param int $id
     *
     * @return Response
     */
    public function actionBlock($id)
    {
        if ($id == Yii::$app->user->getId()) {
            Yii::$app->getSession()->setFlash('danger', Yii::t('account', 'You can not block your own account'));
        } else {
            $account = $this->findModel($id);
            if ($account->getIsBlocked()) {
                $account->unblock();
                Yii::$app->getSession()->setFlash('success', Yii::t('account', 'User has been unblocked'));
            } else {
                $account->block();
                Yii::$app->getSession()->setFlash('success', Yii::t('account', 'User has been blocked'));
            }
        }

        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $id = Uuid::str2uuid($id);
        $account = $this->finder->findAccountById($id);
        if ($account === null) {
            throw new NotFoundHttpException('The requested page does not exist');
        }

        return $account;
    }

    /**
     * Performs AJAX validation.
     *
     * @param array|Model $model
     *
     * @throws ExitException
     */
    protected function performAjaxValidation($model)
    {
        if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            if ($model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                echo json_encode(ActiveForm::validate($model));
                Yii::$app->end();
            }
        }
    }
}
