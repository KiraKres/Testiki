<?php
//ПАРОЛЬ admin - admin УЖЕ НЕ АКТУАЛЬНО
//http://localhost:8080/index.php?r=file/index
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\File;

class FileController extends Controller
{
    // отключаем CSRF-защиту для API-запросов 
    public $enableCsrfValidation = false;

    //только залогиненные пользователи
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'upload', 'delete', 'update'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // @ - значит только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST', 'DELETE'],
                    'upload' => ['POST'],
                ],
            ],
        ];
    }

    //чек список
    public function actionIndex()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;//- по умолчанию yii шлет html, ставит в json
        // находим файлы только текущего пользователя
        $files = File::find()->where(['user_id' => Yii::$app->user->id])->all();
        return $files;
    }

    //загурзка
    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new File();
        
        // POST-запрос
        $uploadedFile = UploadedFile::getInstanceByName('file');
        
        if ($uploadedFile) {
            // timestamp_md5hash_fileName - гсспд мне мозг почесали
            $timestamp = time();
            $hash = md5($uploadedFile->baseName);
            $trueName = $timestamp . "_" . $hash . "_" . $uploadedFile->name;
            $path = 'uploads/' . $trueName;

            // сохраняем физически на диск
            if ($uploadedFile->saveAs($path)) {
                // запись в бд
                $model->file_true_name = $trueName;

                // смотрим какие файлы есть у пользователя, тут логика как в винде с файлами, чтобы имена не повторялись
                $originalName = Yii::$app->request->post('file_name', $uploadedFile->name);
                $finalName = $originalName;
                //запускается только если файл с таким именем уже существует
                $counter = 1;
                //типа будет - img.png - img(1).png , чтобы не было путаницы
                while (File::find()->where(['user_id' => Yii::$app->user->id, 'file_name' => $finalName])->exists()) {
                    $finalName = $originalName . "($counter)";
                    $counter++;
                }

                $model->file_name = $finalName;
                $model->file_path = $path;
                $model->user_name = Yii::$app->user->identity->user_name;
                $model->user_id = Yii::$app->user->id;
                
                if ($model->save()) {
                    return ['status' => 'success', 'message' => 'Файл загружен'];
                }
            }
        }

        return ['status' => 'error', 'message' => 'Ошибка загрузки'];
    }

    //удаление
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = File::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);

        if ($file) {
            // удаляем файл с диска
            if (file_exists($file->file_path)) {
                unlink($file->file_path);
            }
            // удаляем запись из базы
            $file->delete();
            return ['status' => 'success'];
        }

        return ['status' => 'error', 'message' => 'Файл не найден'];
    }

    public function actionUpdate($id) //изменить имяя файла
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = File::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);

        if ($model && $model->load(Yii::$app->request->post(), '')) {
            // file_name берется из пришедших данных
            if ($model->save()) {
                return ['status' => 'success', 'message' => 'Название обновлено'];
            }
        }

        return ['status' => 'error', 'message' => 'Не удалось обновить'];
    }

    //сначала нужно отдать файл, только после сохранить - тут отдать
    public function actionDownload($id)
    {
        //чек айди
        $file = File::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);

        if ($file) {
            $filePath = Yii::getAlias('@webroot') . '/' . $file->file_path;

            if (file_exists($filePath)) {
                return Yii::$app->response->sendFile($filePath, $file->file_name);
            }
        }

        throw new \yii\web\NotFoundHttpException("Файл не найден.");
    }
}