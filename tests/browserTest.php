<?php
use Silex\WebTestCase;

class BrowserTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../src/bootstrap.php';
        require PROJECT_DIR . '/src/controller.php';

        $app['debug'] = true;
        $app['exception_handler']->disable();
        $app['session.test'] = true;


        return $app;
    }

    public function testTopPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('a:contains("Category")[href="/category"]'));
        $this->assertCount(1, $crawler->filter('a:contains("Book")[href="/edit"]'));
        $this->assertCount(1, $crawler->filter('a:contains("Search")[href="/search"]'));
    }

    public function testCategoryPage()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/category');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('a:contains("Category")[href="/category"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[label]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[description]"]'));

        $crawler = $client->request('GET', '/category');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[label]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[description]"]'));

        $form = $crawler->selectButton('Register')->form();
        $crawler = $client->submit($form);

        $before = ORM::for_table('categories')->count();
        $form = $crawler->selectButton('Register')->form();
        $form['form[label]'] = basename(__FILE__, '.php') . date(' Y-m-d H:i:s');
        $crawler = $client->submit($form);
        $this->assertSame($before + 1, ORM::for_table('categories')->count(), '1 category inserted');
    }

    public function testBookEditPage()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/edit');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('select[name="form[category_id]"]'));
        $this->assertCount(1, $crawler->filter('li:contains("Book").active'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[author]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[title]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[publisher]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[comment]"]'));
        $this->assertCount(1, $crawler->filter('input[type="hidden"][name="form[_token]"]'));

        $form = $crawler->selectButton('Register')->form();
        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter('input[name="form[_token]"]'));

        $before = ORM::for_table('books')->count();
        $form = $crawler->selectButton('Register')->form();
        $form['form[author]']    = basename(__FILE__, '.php');
        $form['form[title]']     = basename(__FILE__, '.php');
        $form['form[publisher]'] = basename(__FILE__, '.php');
        $crawler = $client->submit($form);
        $this->assertCount(0, $crawler->filter('input[name="form[_token]"]'));
        $this->assertSame($before + 1, ORM::for_table('books')->count(), '1 book inserted');
    }

    /**
     * @depends testBookEditPage
     */
    public function testSearchPage()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/search');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('li:contains("Search").active'));
        $this->assertCount(1, $crawler->filter('select[name="category_id"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="author"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="title"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="publisher"]'));
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Borrow")')->count());

        $form = $crawler->selectButton('Search')->form();
        $form['author']    = basename(__FILE__, '.php');
        $form['title']     = basename(__FILE__, '.php');
        $form['publisher'] = basename(__FILE__, '.php');
        $crawler = $client->submit($form);
        $this->assertGreaterThanOrEqual(3, $crawler->filter(sprintf('td:contains("%s")', basename(__FILE__, '.php')))->count());
        $this->assertTrue($client->getResponse()->isOk());
    }

    /**
     * @depends testBookEditPage
     */
    public function testBorrowPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/borrow/1');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('li:contains("Rental").active'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[user]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[start_date]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[end_date]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[place]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[comment]"]'));
        $this->assertCount(1, $crawler->filter('input[type="hidden"][name="form[book_id]"]'));
        $this->assertCount(1, $crawler->filter('input[name="form[_token]"]'));

        $form = $crawler->selectButton('Register')->form();
        $crawler = $client->submit($form);
        $this->assertCount(1, $crawler->filter('input[name="form[user]"]'), 'invalid');

        $before = ORM::for_table('rentals')->count();
        $form = $crawler->selectButton('Register')->form();
        $form['form[user]']    = basename(__FILE__, '.php');
        $form['form[start_date]']     = date('Y-m-d');
        $form['form[end_date]'] = date('Y-m-d', strtotime('+1 week'));
        $form['form[place]'] = basename(__FILE__, '.php');
        $crawler = $client->submit($form);
        $this->assertCount(0, $crawler->filter('input[name="form[user]"]'));
        $this->assertSame($before + 1, ORM::for_table('rentals')->count(), '1 book inserted');
    }

    /**
     * @depends testBorrowPage
     */
    public function testRentalPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/rental');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('li:contains("Rental").active'));

        $this->assertGreaterThanOrEqual(1, $crawler->filter('input[type="submit"][value="Return"]')->count());
        $this->assertGreaterThanOrEqual(1, $crawler->filter('input[name="form[_token]"]')->count());

        $before = ORM::for_table('rentals')->where_equal('return_flag', 0)->count();
        $form = $crawler->selectButton('Return')->form();
        $client->submit($form);

        $this->assertEquals($before - 1, ORM::for_table('rentals')->where_equal('return_flag', 0)->count(), 'returned');
    }

}
