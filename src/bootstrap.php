<?php
require_once __DIR__. '/config/constants.php';
require_once PROJECT_DIR . '/vendor/autoload.php';

date_default_timezone_set(TIMEZONE);

$app = new Silex\Application();
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
        'translator.messages' => array(),
    ));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => PROJECT_DIR .'/src/views',
));

ORM::configure('sqlite:' .PROJECT_DIR. '/db/books.sqlite3');

return $app;
