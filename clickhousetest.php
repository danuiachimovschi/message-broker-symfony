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
$db->setTimeout(1.5);
$db->setTimeout(10);
$db->setConnectTimeOut(5);
$db->ping(true);