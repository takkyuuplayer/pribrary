<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Operations\Lookup;
use ApaiIO\ApaiIO;

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

    $category = ORM::for_table('categories')
        ->where_equal('id', $book->category_id)
        ->find_one();

    return $app['twig']->render('show.html',
        array('book' => $book,
            'category' => $category,
            'stash_data' => $book ? json_decode($book->stash_data) : '',
    ));
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

$app->get('/borrow/{book_id}', function($book_id) use ($app) {
    $book = ORM::for_table('books')
        ->where_equal('id', $book_id)
        ->find_one();

    if(! $book) {
        return $app->abort(404, "Book_id: $book_id does not exist.");
    }

    $category = ORM::for_table('categories')
        ->where_equal('id', $book->category_id)
        ->find_one();

    $form = FormFactory::getBookBorrowForm($app, array());

    return $app['twig']->render('borrow.html',
        array('book' => $book,
              'category' => $category,
            'stash_data' => $book ? json_decode($book->stash_data) : '',
                'form' => $form->createView(),
    ));
});
$app->post('/borrow', function(Request $request) use ($app) {
    $book_ids = array_map(function($row) {
        return $row['id'];
    }, ORM::for_table('books')
        ->select('id')
        ->find_array()
    );
    $form = FormFactory::getBookBorrowForm($app, $book_ids);
    $form->handleRequest($request);
    if(!$form->isValid()) {
        $posted = $form->getData();
        $book = ORM::for_table('books')
            ->where_equal('id', $posted['book_id'])
            ->find_one();
        $category = ORM::for_table('categories')
            ->where_equal('id', $book->category_id)
            ->find_one();
        return $app['twig']->render('borrow.html',
        array('book' => $book,
            'category'   => $category,
            'stash_data' => $book ? json_decode($book->stash_data) : '',
            'form'       => $form->createView(),
            'posted'     => $posted,
        ));
    }

    $values = $form->getData();
    $rental = ORM::for_table('rentals')->create();
    $rental->book_id = $values['book_id'];
    $rental->user = $values['user'];
    $rental->start_date = strtotime($values['start_date']);
    $rental->end_date = strtotime($values['end_date']);
    $rental->place = $values['place'];
    $rental->stash_data  = json_encode(array(
        'comment' => $values['comment'],
    ));
    $rental->save();

    return $app->redirect('/rental');
});

$app->get('/rental', function() use ($app) {
    $rentals = ORM::for_table('rentals')
        ->select('*')
        ->select('rentals.stash_data', 'stash_data')
        ->select('rentals.id', 'rental_id')
        ->join('books', array(
            'rentals.book_id', '=', 'books.id'
        ))
        ->join('categories', array(
            'books.category_id', '=', 'categories.id'
        ))
        ->where_equal('rentals.return_flag', '0')
        ->order_by_desc('id')
        ->find_array();

    $csrf_form = $app['form.factory']->createBuilder('form')->getForm();
    return $app['twig']->render('rental.html',
        array('rentals' => $rentals,
              'form' => $csrf_form->createView(),
    ));
});
$app->post('/rental/delete/{rental_id}', function($rental_id, Request $request) use ($app) {
    $csrf_form = $app['form.factory']->createBuilder('form')->getForm();
    $csrf_form->handleRequest($request);
    if(!$csrf_form->isValid()) {
        return $app->redirect('/rental');
    }

    $rental = ORM::for_table('rentals')
        ->where_equal('id', $rental_id)
        ->find_one();
    $rental->return_flag = 1;
    $rental->save();

    return $app->redirect('/rental');
});

$app->get('/isbn/{isbn}', function($isbn) use ($app) {
    $conf = new GenericConfiguration();
    $conf->setCountry('co.jp')
        ->setAccessKey(ACCESS_KEY)
        ->setSecretKey(SECRET_KEY)
        ->setAssociateTag(ASSOCIATE_TAG);

    $apaiIO = new ApaiIO($conf);

    $lookup = new Lookup();
    $lookup->setSearchIndex('Books')
        ->setIdType('ISBN')
        ->setItemId($isbn)
        ->setResponseGroup(array('Small'));

    $formattedResponse = $apaiIO->runOperation($lookup);
    $sx = simplexml_load_string($formattedResponse)->Items->Item;
    $detail = json_decode(json_encode($sx), true);
    return $app->json($detail);
});

