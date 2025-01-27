<?php

require __DIR__ . '/vendor/autoload.php';

$config = [
    'host' => 'clickhouse',
    'port' => '8123',
    'username' => 'default',
    'password' => '',
    'https' => false,
];
$db = new ClickHouseDB\Client($config);
$db->database('default');
$db->setTimeout(1.5);      // 1 second , support only Int value
$db->setTimeout(10);       // 10 seconds
$db->setConnectTimeOut(5); // 5 seconds
$db->ping(true); // if can`t connect throw exception