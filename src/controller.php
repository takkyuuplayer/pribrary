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

$app->get('/edit', function() use ($app) {
    $subRequest = Request::create('/edit/0', 'GET');
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});
$app->get('/edit/{book_id}', function($book_id) use ($app) {
    $book = ORM::for_table('books')
        ->where_equal('id', $book_id)
        ->find_one();

    $categories = ORM::for_table('categories')->find_many();
    $form = FormFactory::getBookEditForm($app, array());

    return $app['twig']->render('edit.twig',
        array('book' => $book,
        'categories' => $categories,
        'stash_data' => $book ? json_decode($book->stash_data) : '',
        'form' => $form->createView(),
    ));
});
