<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
{
    return [
        'access' => [
            'class' => \yii\filters\AccessControl::class,
            'only' => ['logout', 'signup', 'login'], // К каким экшенам применяем правила
            'rules' => [
                [
                    'actions' => ['login', 'signup'],
                    'allow' => true,
                    'roles' => ['?'], // '?' значит только для гостей
                ],
                [
                    'actions' => ['logout'],
                    'allow' => true,
                    'roles' => ['@'], // '@' значит только для авторизованных
                ],
            ],
        ],
        'verbs' => [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                // 'logout' => ['post'], // ВРЕМЕННО закомментируй это, чтобы выйти через ссылку
            ],
        ],
    ];
}

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
{
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }

    return $this->redirect(['site/storage']);
}

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
{
    if (!Yii::$app->user->isGuest) {
        return $this->redirect(['site/storage']);
    }

    $model = new \app\models\LoginForm();
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
        return $this->redirect(['site/storage']);
    }

    $model->password = '';
    return $this->render('login', [
        'model' => $model,
    ]);
}

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionStorage()
    {
    // только залогиненые
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['site/login']);
    }
    return $this->render('storage');
    }
}
