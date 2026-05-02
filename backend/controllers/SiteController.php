<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use app\models\User;

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
            'only' => ['logout', 'signup', 'login'], //к каким экшенам применяем правила
            'rules' => [
                [
                    'actions' => ['login', 'signup'],
                    'allow' => true,
                    'roles' => ['?'], // ? значит только для гостей
                ],
                [
                    'actions' => ['logout'],
                    'allow' => true,
                    'roles' => ['@'], // @ значит только для авторизованных
                ],
            ],
        ],
        'verbs' => [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                // 'logout' => ['post'], // ВРЕМЕННО 
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
        if (!Yii::$app->user->isGuest) return $this->redirect(['site/storage']);

        $loginModel = new LoginForm();
        $signupModel = new SignupForm();

        // проверяем, какая форма пришла
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            
            // регистр
            if (isset($post['mode']) && $post['mode'] === 'signup') {
                if ($signupModel->load($post, "LoginForm") && $signupModel->signup()) {
                    Yii::$app->user->login(User::findByUsername($signupModel->username));
                    $user = \app\models\User::findByUsername($signupModel->username);
                    return $this->redirect(['site/storage']);
                }
            } 
            //  обычный вход
            else {
                if ($loginModel->load($post) && $loginModel->login()) {
                    return $this->redirect(['site/storage']);
                }
            }
        }

        return $this->render('login', [
            'model' => $loginModel, 
            'signupModel' => $signupModel,
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

    //ВОЗМУЩАЕТСЯ НА CSRF - ЗАЩИЬУ

    public function beforeAction($action)
    {
        // отключаем проверку CSRF для ренейма
        if ($action->id === 'rename-file') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionRenameFile()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = json_decode(\Yii::$app->request->getRawBody(), true); //ответ в виде джейсончика

        if (isset($data['id']) && isset($data['newName'])) {  
            $file = \app\models\File::findOne(['id' => $data['id'], 'user_id' => \Yii::$app->user->id]); //ьекущий юзер проверка

            if ($file) {
                $originalNewName = $data['newName'];
                $finalName = $originalNewName;
                $counter = 1;

                //аналогично как в файлконтроллере - приписка цифорки (1) и тд
                while (\app\models\File::find()
                    ->where(['user_id' => \Yii::$app->user->id, 'file_name' => $finalName])
                    ->andWhere(['!=', 'id', $file->id]) // не сравниваем файл с самим собой
                    ->exists()) {
                    $finalName = $originalNewName . "($counter)";
                    $counter++;
                }

                $file->file_name = $finalName;
                if ($file->save()) {
                    return [
                        'success' => true, 
                        'finalName' => $finalName
                    ];
                }
        }
    }

    return ['success' => false];
}

}
