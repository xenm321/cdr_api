<?php

return array(
    'root_dir' => __DIR__.'/../',
    'env' => getenv('APP_ENV') ? getenv('APP_ENV') : 'prod',
    'debug' => getenv('APP_ENV') === 'dev' ? true: false,
);
