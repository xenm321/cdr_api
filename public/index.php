<?php

date_default_timezone_set('Europe/Moscow');

/** @var \App\Application $app */
$app = require_once __DIR__.'/../app/bootstrap.php';

$app->run();
