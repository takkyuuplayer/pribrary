<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Silex\Provider\FormServiceProvider;

require_once __DIR__ . '/../config/constants.php';
require_once PROJECT_DIR . '/vendor/autoload.php';

$app = new Silex\Application();
$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
        'translator.messages' => array(),
    ));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => PROJECT_DIR .'/src/views',
));

ORM::configure('sqlite:' .PROJECT_DIR. '/db/books.sqlite3');

$app->get('/', function() use ($app) {
    return $app['twig']->render('top.twig');
});

$app->get('/category', function() use ($app) {
    $categories = ORM::for_table('categories')->find_many();
    return $app['twig']->render('category.twig', array('categories' => $categories));
});
$app->post('/category', function(Request $request) use ($app) {
    $category = ORM::for_table('categories')->create();
    $category->label = $request->get('label');
    $category->description = $request->get('description');
    $category->stash_data = json_encode([]);
    $category->save();

    return $app->redirect('/category');
});

$app->run();
return $app;
