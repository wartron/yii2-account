<?php

$db = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=wartron_yii2account_test_uuid',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
];

if (file_exists(__DIR__ . '/db.local.php')) {
    $db = array_merge($db, require(__DIR__ . '/db.local.php'));
}

return $db;

