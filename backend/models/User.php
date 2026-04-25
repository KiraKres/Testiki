<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Модель для таблицы "user"
 *
 * @property int $user_id
 * @property string $user_name
 * @property string $password_hash
 * @property string $auth_key
 * @property string $access_token
 */
class User extends ActiveRecord implements IdentityInterface
{
    // Указываем имя таблицы в базе данных
    public static function tableName()
    {
        return 'user';
    }

    // Правила валидации для базы данных
    public function rules()
    {
        return [
            [['user_name', 'password_hash', 'auth_key'], 'required'],
            [['user_name'], 'unique'],
            [['user_name', 'password_hash', 'access_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
        ];
    }

    // --- Методы IdentityInterface (нужны для авторизации) ---

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['user_name' => $username]);
    }

    public function getId()
    {
        return $this->user_id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    // Метод для проверки пароля (хэша)
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
}