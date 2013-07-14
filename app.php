<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app['title'] = "Composer Proxy JP";
$app['base_url'] = "http://composer-proxy.jp/";

$app['repositories'] = array(
    'packagist' => 'https://packagist.org'
);

$app['cache_dir'] = __DIR__.'/web/proxy';

$app['browser'] = $app->share(function() {
    $client = new Buzz\Client\Curl();

    return new Buzz\Browser($client);
});

$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => __DIR__.'/cache/'
));


$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views'
));

$app->get('/', function() use ($app) {
    $body =  $app['twig']->render('index.html.twig', array(
        'app' => $app
    ));

    return new Response($body, 200, array('Cache-Control' => 's-maxage=3600,public'));
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
    $responseJson['providers-url'] = "/proxy/".$rep."/p/%package%$%hash%.json";

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
