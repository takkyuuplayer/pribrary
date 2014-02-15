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
        $this->assertCount(1, $crawler->filter('a:contains("Category")'));
        $this->assertCount(1, $crawler->filter('a:contains("New")'));
        $this->assertCount(1, $crawler->filter('a:contains("Search")'));
    }

    public function testCategoryPage()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/category');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[label]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[description]"]'));

        $crawler = $client->request('GET', '/category');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[label]"]'));
        $this->assertCount(1, $crawler->filter('input[type="text"][name="form[description]"]'));

        $form = $crawler->selectButton('Register')->form();
        $crawler = $client->submit($form);

        $before = ORM::for_table('categories')->count();
        date_default_timezone_set('Asia/Tokyo');
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
}
