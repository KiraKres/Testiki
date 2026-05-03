<?php

$db = require __DIR__ . '/db.php';

$db['dsn'] = 'mysql:host=db;dbname=testiki_db';  //ссылка на докер контейнер с базов данных

return $db;