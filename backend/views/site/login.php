<?php
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Welcome!';
$this->registerCssFile('@web/css/login-style.css?v=' . time());
// Vue
$this->registerJsFile('https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<div class="login-page" id="auth-app"> <div class="bg-circle"></div>
    <div class="bg-circle-2"></div>
    <div class="bg-circle-3"></div>
    <div class="bg-circle-4"></div>

    <div class="login-container">
        <div class="login-card">
            <h1>Welcome!</h1>
            
            <div class="tabs">
                <span class="tab" :class="{ active: mode === 'login' }" @click="mode = 'login'">sign in</span>
                <span class="tab" :class="{ active: mode === 'signup' }" @click="mode = 'signup'">sign up</span>
            </div>      

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'fieldConfig' => [
                    'template' => "{label}\n<div class='input-group-custom'>{input}<div class='icon-field'></div></div>\n{error}",
                    'labelOptions' => ['class' => 'custom-label'],
                    'inputOptions' => ['class' => 'custom-input'],
                ],
            ]); ?>

            <?= $form->field($model, 'username', [
                'options' => ['class' => 'mb-3 icon-user'],
            ])->textInput(['placeholder' => 'username'])->label('username') ?>

            <?= $form->field($model, 'password', [
                'options' => ['class' => 'mb-3 icon-lock'],
            ])->passwordInput(['placeholder' => 'password'])->label('password') ?>

            <div v-if="mode === 'signup'" v-cloak>
                <?= $form->field($model, 'password', [ 
                    'options' => ['class' => 'mb-3 icon-lock'],
                    'template' => "{label}\n<div class='input-group-custom'>{input}<div class='icon-field'></div></div>\n{error}"
                ])->passwordInput(['placeholder' => 'confirm password'])->label('confirm password') ?>
            </div>

            <div class="form-group">
            </div>

            <input type="hidden" name="mode" :value="mode">

            <div class="form-group">
                <button type="submit" class="btn-signin">
                    {{ mode === 'login' ? 'sign in' : 'sign up' }}
                </button>
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
        <div class="decor-line-2"></div>
    </div>
</div>

<script>
//  Vue
new Vue({
    el: '#auth-app',
    data: {
        mode: 'login' // по умолчанию будет логин, можно переключить
    }
});
</script>

<style>
/* чтобы Vue не мешал при загрузке */
[v-cloak] { display: none; }
</style>