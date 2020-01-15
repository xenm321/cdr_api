<?php

$loader = require __DIR__.'/../vendor/autoload.php';

use App\Application;
use Dotenv\Dotenv;

if (!isset($_SERVER['APP_ENV'])) {
    $dotEnv = new Dotenv(__DIR__.'/..');
    $dotEnv->load();
}

if (getenv('APP_ENV') === 'dev') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$app = new Application(require_once 'config.php');

$app->mountControllers();

return $app;
