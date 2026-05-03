<?php
//ЭТОТ ТЕСТ НА ЛОГИРОВАНИЕ - ПУСТИТ ИЛИ НЕТ
class LoginFormCest
{
    // этот метод выполняется ПЕРЕД каждым тестом в этом файле
    public function _before(\FunctionalTester $I)
    {
        // стр логина
        $I->amOnRoute('site/login');
    }

    // фукнция проверки - открылась ли страница вообще? Если да, то покажет велком
    public function openLoginPage(\FunctionalTester $I)
    {
        $I->see('Welcome!', 'h1');
    }

    // ТЕСТ: логин с пустыми полями
    public function loginWithEmptyCredentials(\FunctionalTester $I)
    {
        // Нажимаем на кнопку, ничего не вводя
        $I->click('.btn-signin'); 
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    // ТЕСТ: неверный пароль
    public function loginWithWrongCredentials(\FunctionalTester $I)
    {
        $I->fillField('username', 'admin');
        $I->fillField('password', 'wrong_password');
        $I->click('.btn-signin');
        
        $I->expectTo('увидеть сообщение о неверном пароле');
        $I->see('Incorrect username or password.');
    }

    // ТЕСТ: успешный вход
    public function loginSuccessfully(\FunctionalTester $I)
    {
        $I->fillField('username', 'admin'); // тут надо, чтобы в бд был обязательно пользователь admin - 1234
        $I->fillField('password', '1234'); 
        $I->click('.btn-signin');

        // мы в хранилище
        $I->seeInCurrentUrl('/storage');
        // формы лога нет на экране - значит успешно
        $I->dontSeeElement('form#login-form');
    }

    // ТЕСТ: sign up
    public function testSwitchToSignup(\FunctionalTester $I)
    {

        $I->amOnRoute('site/login');
        $I->see('sign up', '.tab'); // проверяем что вкладка видна
    }

    //ТЕСТ: регистация с 0
    public function signupSuccessfully(\FunctionalTester $I)
    {
        // имитируем заполнение формы регистрации напрямую
        $I->submitForm('#login-form', [
            'username' => 'newuser',
            'password' => '123456',
            'password_repeat' => '123456',
            'mode' => 'signup' 
        ]);
        
        $I->seeInCurrentUrl('/storage');
    }
}