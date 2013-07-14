<?php

// For build in server
if (php_sapi_name() === 'cli-server') {
    $filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (is_file($filename)) {
        return false;
    }
}

$app = require(__DIR__."/../app.php");
$app['debug'] = true;

if ($app['debug']) {
    $app->run();
} else {
    $app['http_cache']->run();
}
