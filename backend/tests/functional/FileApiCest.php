<?php

class FileApiCest
{
    public function testFileUpload(\FunctionalTester $I)
    {
        //находим админа и логинимся за него
        $user = \app\models\User::findByUsername('admin');
        $I->amLoggedInAs($user);
        
        $uniqueName = 'test_' . uniqid();
        //POST-запрос на маршрут 'file/upload'
        $I->sendPost('file/upload', [
            'file_name' => $uniqueName
        ], [
            'file' => \codecept_data_dir('test.txt') 
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['status' => 'success']);
    }

    public function guestCannotRenameFile(\FunctionalTester $I)
    {
        // отключаем автоматический редирект, чтобы увидеть сам факт перенаправления (302)
        $I->stopFollowingRedirects();
        
        $I->sendPost('site/rename-file', [
            'id' => 1, 
            'newName' => 'kir0chka'
        ]);
        
        // нас должны автоматически перекинуть на стриницу логина
        $I->seeResponseCodeIs(302);
    }

    public function testDeleteOwnFile(\FunctionalTester $I)
    {
        $user = \app\models\User::findByUsername('admin');
        $I->amLoggedInAs($user);

        // пробуем удалить файл с айди 2
        $I->sendPost('file/delete?id=2'); 
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['status' => 'success']);
        
        // в бд его больше нет
        $I->sendGet('file/index');
        $I->dontSeeResponseContainsJson(['id' => 2]);
    }

    public function testCannotDeleteOthersFile(\FunctionalTester $I)
    {
        // ОБЯЗАТЕЛЬНО ДОЛЖЕН БЫТЬ ЮЗЕР - ДЕМО В САМОЙ БД
        $user = \app\models\User::findByUsername('demo'); 
        $I->amLoggedInAs($user);

        // пытаемся удалить файл админа 
        $I->sendPost('file/delete?id=2');

        // кинет ошибку тк у демо юзера нет этого файла
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['status' => 'error', 'message' => 'Файл не найден']);
    }
}