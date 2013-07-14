<?php

require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();

$app['queue'] = true;
$app['quque_host'] = 'localhost';
$app['queue_port'] = 4000;

$app['repositories'] = array(
    'packagist' => 'https://packagist.org'
);

$app['cache_dir'] = __DIR__.'/web/proxy';

$app['browser'] = $app->share(function() {
    return new Buzz\Browser();
});

$app->get('/', function() use ($app) {
    return "Hello";
});

$app->get('/proxy/{rep}/packages.json', function($rep) use ($app) {
    if (!isset($app['repositories'][$rep])) {
        $app->abort(404, "Not Found");
    }

    $url = $app['repositories'][$rep]."/packages.json";
    $response = $app['browser']->get($url);
    if (!$response->isOk()) {
        $app->abort($response->getStatusCode(), "");
    }

    $responseJson = json_decode($response->getContent(), true);

    unset($responseJson['notify']);
    unset($responseJson['notify-batch']);
    unset($responseJson['search']);

    $dir = $app['cache_dir']."/".$rep;
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    file_put_contents($app['cache_dir']."/".$rep."/packages.json", json_encode($responseJson));

    return $app->json($responseJson);
});

$app->get('/proxy/{rep}/p/{p}${hash}.json', function($rep, $p, $hash) use ($app) {
    if (!isset($app['repositories'][$rep])) {
        $app->abort(404, "Not Found");
    }

    $path = "/p/";
    $file = $p."$".$hash.".json";

    $url = $app['repositories'][$rep].$path.$file;
    $response = $app['browser']->get($url);
    if (!$response->isOk()) {
        $app->abort($response->getStatusCode(), "");
    }

    $responseJson = json_decode($response->getContent(), true);

    $dir = $app['cache_dir']."/".$rep.$path;
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    file_put_contents($dir.$file, json_encode($responseJson));

    return $app->json($responseJson);
});

$app->get('/proxy/{rep}/p/{namespace}/{package}${hash}.json', function($rep, $namespace, $package, $hash) use ($app) {
    if (!isset($app['repositories'][$rep])) {
        $app->abort(404, "Not Found");
    }

    $path = "/p/".$namespace."/";
    $file = $package."$".$hash.".json";

    $url = $app['repositories'][$rep].$path.$file;
    $response = $app['browser']->get($url);
    if (!$response->isOk()) {
        $app->abort($response->getStatusCode(), "");
    }

    $responseJson = json_decode($response->getContent(), true);

    $dir = $app['cache_dir']."/".$rep.$path;
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    file_put_contents($dir.$file, json_encode($responseJson));

    return $app->json($responseJson);
});

$app->get('/proxy/{rep}/p/{namespace}/{package}.json', function($rep, $namespace, $package) use ($app) {
    if (!isset($app['repositories'][$rep])) {
        $app->abort(404, "Not Found");
    }

    $path = "/p/".$namespace."/";
    $file = $package.".json";

    $url = $app['repositories'][$rep].$path.$file;
    $response = $app['browser']->get($url);
    if (!$response->isOk()) {
        $app->abort($response->getStatusCode(), "");
    }

    $responseJson = json_decode($response->getContent(), true);

    $dir = $app['cache_dir']."/".$rep.$path;
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    file_put_contents($dir.$file, json_encode($responseJson));

    return $app->json($responseJson);
});

return $app;
