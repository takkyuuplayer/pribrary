<?php
require_once __DIR__. '/config/constants.php';
require_once PROJECT_DIR . '/vendor/autoload.php';

date_default_timezone_set(TIMEZONE);

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
        'translator.messages' => array(),
    ));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => PROJECT_DIR .'/src/views',
));
$app['twig']->registerUndefinedFunctionCallback(function($name) {
    if (function_exists($name)) {
        return new Twig_SimpleFunction($name, function() use($name) {
            return call_user_func_array($name, func_get_args());
        });
        return false;
    }
});

ORM::configure('sqlite:' .PROJECT_DIR. '/db/books.sqlite3');

return $app;
