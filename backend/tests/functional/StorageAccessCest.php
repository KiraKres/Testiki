<?php

class StorageAccessCest
{

    // Тест этого:
    //ТОЛЬКО авторизированные пользователи
    //не у автор. не должно быть доступа к api

    // ТЕСТ: Гостя должно выкидывать на синг ап
    public function guestIsRedirectedFromStorage(\FunctionalTester $I)
    {
        $I->amOnPage(['site/storage']);
        $I->seeInCurrentUrl('signup'); 
    }

    // ТЕСТ: его ты зарегался - то видишь страницу
    public function authorizedUserSeesStorage(\FunctionalTester $I)
    {
        // ОБЯЗАТЕЛЬНО должен быть админ
        $I->amLoggedInAs(\app\models\User::findByUsername('admin'));
        $I->amOnPage(['site/storage']);
        
        // чек, что мы вооюзще видим
        $I->see('Upload File', 'h2');
        $I->see('FILES', 'h3'); 
        $I->seeElement('.files-table');
    }
}