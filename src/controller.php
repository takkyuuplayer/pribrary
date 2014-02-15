<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require_once PROJECT_DIR . '/src/lib/FormFactory.php';

$app->get('/', function() use ($app) {
    return $app['twig']->render('top.twig');
});

$app->get('/category', function() use ($app) {
    $categories = ORM::for_table('categories')->find_many();
    $form = FormFactory::getCategoryEditForm($app);
    return $app['twig']->render('category.twig',
        array('categories' => $categories,
        'form' => $form->createView(),
    ));
});
$app->post('/category', function(Request $request) use ($app) {
    $form = FormFactory::getCategoryEditForm($app);
    $form->handleRequest($request);

    if(!$form->isValid()) {
        $categories = ORM::for_table('categories')->find_many();
        return $app['twig']->render('category.twig',
            array('categories' => $categories,
                'posted' => $form->getData(),
                'form' => $form->createView(),
        ));
    }

    $category = ORM::for_table('categories')->create();
    $values = $form->getData();
    $category->label = $values['label'];
    $category->description = is_null($values['description']) ? '' : $values['description'];
    $category->stash_data = json_encode([]);
    $category->save();

    return $app->redirect('/category');
});
