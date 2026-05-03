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
use app\models\File;

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

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            
            if (isset($post['mode']) && $post['mode'] === 'signup') {
                // регистрируемся
                if ($signupModel->load($post, '') && $signupModel->signup()) {
                    $user = User::findByUsername($signupModel->username);
                    Yii::$app->user->login($user);
                    return $this->redirect(['site/storage']);
                }
                // Если ошибка — рендерим логин, но передаем signupModel как основную
                return $this->render('login', [
                    'model' => $signupModel, 
                    'mode' => 'signup' 
                ]);
            } else {
                // обычный вход
                if ($loginModel->load($post, '') && $loginModel->login()) {
                    return $this->redirect(['site/storage']);
                }
                return $this->render('login', [
                    'model' => $loginModel, 
                    'mode' => 'login'
                ]);
            }
        }

        return $this->render('login', ['model' => $loginModel, 'mode' => 'login']);
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
        $data = json_decode(\Yii::$app->request->getRawBody(), true);

        if (isset($data['id']) && isset($data['newName'])) {
            $model = File::findOne($data['id']);
            if (!$model) return ['success' => false, 'error' => 'File not found'];

            $newName = trim($data['newName']);
            if (empty($newName)) return ['success' => false, 'error' => 'Name cannot be empty'];

            //расширение из физического имени файла на диске
            $extension = pathinfo($model->file_true_name, PATHINFO_EXTENSION);

            // принудительно, чтобы не менять расширения
            // ФИКС: очищаем имя от расширения, если юзер его случайно ввел
            // Используем mb_stripos для надежности, чтобы точка в конце не ломала имя
            $cleanName = preg_replace('/\.'.preg_quote($extension, '/').'$/i', '', $newName);
            $finalName = $cleanName . '.' . $extension;

            // дублики
            $counter = 1;
            $tempName = $finalName;
            
            // Проверяем, существует ли файл с таким именем у этого юзера (исключая текущий файл)
            // ВАЖНО: Добавляем user_id, чтобы не конфликтовать с чужими файлами
            while (File::find()->where(['user_id' => Yii::$app->user->id, 'file_name' => $tempName])
                    ->andWhere(['not', ['id' => $model->id]])->exists()) {
                // ФИКС: формируем новое имя, вставляя счетчик ПЕРЕД расширением
                $tempName = $cleanName . "($counter)." . $extension;
                $counter++;
            }

            $model->file_name = $tempName;

            if ($model->save()) {
                $model->refresh();
                // Возвращаем finalName, чтобы Vue мог обновить строку в таблице
                return ['success' => true, 
                'finalName' => $tempName,
                'newTime' => $model->time_modify
                ];
            }
        }
        return ['success' => false];
    }

    public function actionSignup()
    {
        // фикс ошибки 404
        return $this->actionLogin();
    }
}
