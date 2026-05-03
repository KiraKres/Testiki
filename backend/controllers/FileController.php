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
                'only' => ['index', 'upload', 'delete', 'update', 'download'],
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
                    'update' => ['POST', 'PUT'],
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

        // ФИКС ДЛЯ ТЕСТОВ: Если Yii не нашел файл, берем его из $_FILES напрямую
        if (!$uploadedFile) {
            return ['status' => 'error', 'message' => 'Файл не найден в запросе'];
        }
        
        if ($uploadedFile) {
            // timestamp_md5hash_fileName - гсспд мне мозг почесали
            $timestamp = time();
            $hash = md5($uploadedFile->baseName);
            $trueName = $timestamp . "_" . $hash . "_" . $uploadedFile->name;
            
            // ФИКС: Используем прямой путь контейнера, чтобы не путаться в алиасах
            $uploadDir = '/var/www/html/web/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $path = $uploadDir . $trueName;
            
            //ДЛЯ ТЕСТОВ ИЩУ ОИШБКУ
            if (!is_writable($uploadDir)) {
                return ['status' => 'error', 'message' => 'Директория недоступна для записи: ' . $uploadDir];
            }

            // сохраняем физически на диск, ФИКС ДЛЯ ТЕСТОВ ИСПОЛЬЗУЮ КОПИ ТК saveAs капризничает
            if (copy($uploadedFile->tempName, $path)) {
                // запись в бд
                $model->file_true_name = $trueName;

                // смотрим какие файлы есть у пользователя, тут логика как в винде с файлами, чтобы имена не повторялись
                $userInputName = trim(Yii::$app->request->post('file_name', ''));
                
                if (empty($userInputName)) {
                    $userInputName = $uploadedFile->name;
                }
                
                $extension = pathinfo($uploadedFile->name, PATHINFO_EXTENSION);
                
                // юзер стер расширение в инпуте - принудительно возвращаем его - юзер лох какойто
                if (!str_ends_with(strtolower($userInputName), '.' . strtolower($extension))) {
                    $originalName = $userInputName . '.' . $extension;
                } else {
                    $originalName = $userInputName;
                }

                $finalName = $originalName;
                //запускается только если файл с таким именем уже существует
                $counter = 1;
                
                //типа будет - img.png - img(1).png , чтобы не было путаницы
                // НЮАНС отделяем имя от расширения для цикла
                // ФИКС: вместо pathinfo используем mb_substr, чтобы пробелы и кириллица не отвалилась
                $dotPos = mb_strrpos($originalName, '.');
                $namePart = ($dotPos !== false) ? mb_substr($originalName, 0, $dotPos) : $originalName;
                
                while (File::find()->where(['user_id' => Yii::$app->user->id, 'file_name' => $finalName])->exists()) {
                    $finalName = $namePart . "($counter)." . $extension;
                    $counter++;
                }

                $model->file_name = $finalName;
                $model->file_path = $path;

                //ДЛЯ ТЕСТОВ
                if (Yii::$app->user->identity) {
                    $model->user_name = Yii::$app->user->identity->user_name;
                    $model->user_id = Yii::$app->user->id;
                }
                
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

    public function actionUpdate($id) //изменить имя файла
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Находим файл строго текущего пользователя
        $model = File::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);

        if ($model) {
            $data = Yii::$app->request->post();
            
            // ФИКС: Если в запросе пришло 'newName' (из теста), перекладываем в 'file_name'
            if (isset($data['newName'])) {
                $model->file_name = $data['newName'];
            } elseif (isset($data['file_name'])) {
                $model->file_name = $data['file_name'];
            }
            
            // Если через load не зашло (из-за правил safe в модели), сохраняем принудительно
            if ($model->save()) {
                return ['status' => 'success', 'message' => 'Название обновлено'];
            }
            
            // Если save() не прошел, выплевываем ошибки (для отладки)
            return [
                'status' => 'error', 
                'message' => 'Ошибка валидации', 
                'errors' => $model->getErrors()
            ];
        }

        return ['status' => 'error', 'message' => 'Не удалось обновить'];
    }

    //сначала нужно отдать файл, только после сохранить - тут отдать
    public function actionDownload($id)
    {
        //чек айди
        $file = File::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);

        if ($file) {
            // ФИКС: Так как в БД лежит полный путь /var/www/html/web/uploads/..., 
            // нам не нужно добавлять alias @webroot, иначе путь дублируется.
            $filePath = $file->file_path;

            if (file_exists($filePath)) {
                return Yii::$app->response->sendFile($filePath, $file->file_name);
            }
        }

        throw new \yii\web\NotFoundHttpException("Файл не найден.");
    }
}