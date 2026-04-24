<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Welcome!';
$this->registerCssFile('@web/css/login-style.css?v=' . time());
?>

<div class="login-page">
    <div class="bg-circle"></div>

    <div class="login-container">
        <div class="login-card">
            <h1>Welcome!</h1>
            <div class="tabs">
                <span class="tab active">sign in</span>
                <span class="tab">sign up</span>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'custom-label'],
                    'inputOptions' => ['class' => 'custom-input'],
                ],
            ]); ?>

            <?= $form->field($model, 'username')->textInput(['placeholder' => '👤 username'])->label('username') ?>

            <?= $form->field($model, 'password')->passwordInput(['placeholder' => '🔒 password'])->label('password') ?>

            <div class="form-group">
                <?= Html::submitButton('sign in', ['class' => 'btn-signin', 'name' => 'login-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <div class="dino-wrapper">
            <img src="/img/dino_login.svg" alt="Dino" class="dino-image">
        </div>

        <div class="leaf_on_bottom">  
            <img src="/img/leaf_on_bottom_login.svg" class="leaf leaf-new-bottom" alt="">
        </div>

        <div class="leaf_on_top">  
            <img src="/img/leaf_on_top_login.svg" class="leaf leaf-new-top" alt="">
        </div>

        <div class="leaf_on_top">  
            <img src="/img/leaf_on_top_login_1.svg" class="leaf leaf-new-top_1" alt="">
        </div>

        <div class="leaf_on_top">  
            <img src="/img/leaf_on_top_login_2.svg" class="leaf leaf-new-top_2" alt="">
        </div>

        <div class="decor-line"></div>

        <div class="leaf_on_top">  
            <img src="/img/leaf_on_top_login_3.svg" class="leaf leaf-new-top_3" alt="">
        </div>

    </div>
</div>