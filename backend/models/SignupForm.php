<?php
namespace app\models;

use Yii;
use yii\base\Model;

class SignupForm extends Model
{
    public $username;
    public $password;
    public $password_repeat;
    
    public function rules()
    {
        return [
            [['username', 'password', 'password_repeat'], 'required'],       
            ['username', 'unique', 'targetClass' => '\app\models\User', 'targetAttribute' => 'user_name', 'message' => 'Этот ник уже занят.'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => "Пароли не совпадают!"],
            ['password', 'string', 'min' => 4],
        ];
    }

    public function signup()
    {
        if (!$this->validate()) return null;
        
        $user = new User();
        $user->user_name = $this->username;
        // хэширование паролей байт скрипт
        $user->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        $user->auth_key = Yii::$app->security->generateRandomString();
        
        return $user->save() ? $user : null;
    }
}