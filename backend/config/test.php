<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/test_db.php';

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@webroot' => dirname(__DIR__) . '/web',
        '@web' => '/',
    ],
    'language' => 'en-US',
    'components' => [
        'db' => $db,
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
            'messageClass' => 'yii\symfonymailer\Message'
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        //чтобы оно понимало красивые ссылочки - это из веб.пхп
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'storage' => 'site/storage',
                'signup' => 'site/login',
                'login' => 'site/login', 
                
                'GET file' => 'file/index', 
                'POST file/upload' => 'file/upload',
                'POST file/delete' => 'file/delete',
                'POST site/rename-file' => 'site/rename-file',
                
                'file/download/<id:\d+>' => 'file/download',
                
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],

        'user' => [
            'identityClass' => 'app\models\User',
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
            // but if you absolutely need it set cookie domain to localhost
            /*
            'csrfCookie' => [
                'domain' => 'localhost',
            ],
            */
        ],
    ],
    'params' => $params,
];
