<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require_once PROJECT_DIR . '/src/lib/FormFactory.php';

$app->get('/', function() use ($app) {
    return $app['twig']->render('top.html');
});

$app->get('/category', function() use ($app) {
    $categories = ORM::for_table('categories')->find_many();
    $form = FormFactory::getCategoryEditForm($app);
    return $app['twig']->render('category.html',
        array('categories' => $categories,
        'form' => $form->createView(),
    ));
});
$app->post('/category', function(Request $request) use ($app) {
    $form = FormFactory::getCategoryEditForm($app);
    $form->handleRequest($request);

    if(!$form->isValid()) {
        $categories = ORM::for_table('categories')->find_many();
        return $app['twig']->render('category.html',
            array('categories' => $categories,
                'posted' => $form->getData(),
                'form' => $form->createView(),
        ));
    }

    $category = ORM::for_table('categories')->create();
    $values = $form->getData();
    $category->label = $values['label'];
    $category->description = is_null($values['description']) ? '' : $values['description'];
    $category->stash_data = json_encode(array());
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

    return $app['twig']->render('edit.html',
        array('book' => $book,
        'categories' => $categories,
        'stash_data' => $book ? json_decode($book->stash_data) : '',
        'form' => $form->createView(),
    ));
});
$app->post('/edit/{book_id}', function(Request $request, $book_id) use ($app) {
    $categories = ORM::for_table('categories')->find_many();
    $category_ids = array_map(function($category) {
        return $category->id;
    }, $categories);

    $form = FormFactory::getBookEditForm($app, $category_ids);
    $form->handleRequest($request);
    if(!$form->isValid()) {
        return $app['twig']->render('edit.html',
            array(
                'book' => null,
                'categories' => $categories,
                'posted' => $form->getData(),
                'form' => $form->createView(),
        ));
    }

    $book = ORM::for_table('books')
        ->where_equal('id', $book_id)
        ->find_one();
    if(!$book) {
        $book = ORM::for_table('books')->create();
    }

    $values = $form->getData();
    $book->category_id = $values['category_id'];
    $book->author      = $values['author'];
    $book->title       = $values['title'];
    $book->publisher   = $values['publisher'];
    $book->stash_data  = json_encode(array(
        'comment' => $values['comment'],
    ));
    $book->save();

    return $app['twig']->render('show.html',
        array('book' => $book,
              'stash_data' => json_decode($book->stash_data))
    );
});

$app->get('/search', function(Request $request) use ($app) {
    $book = ORM::for_table('books');
    if($request->get('category_id', null)) {
        $book->where_equal('category_id', $request->get('category_id'));
    }
    if($request->get('author', null)) {
        $book->where_like('author', sprintf('%%%s%%', $request->get('author')));
    }
    if($request->get('title', null)) {
        $book->where_like('title', sprintf('%%%s%%', $request->get('title')));
    }
    if($request->get('publisher', null)) {
        $book->where_like('publisher', sprintf('%%%s%%',$request->get('publisher')));
    }

    $books = $book->find_many();
    $categories = ORM::for_table('categories')->find_many();
    return $app['twig']->render('search.html',
        array('books' => $books,
        'categories' => $categories,
        'posted' => $request->query->all(),
    ));
});
