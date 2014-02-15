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
        $this->assertFalse($client->getResponse()->isRedirect(), 'validation error');

        $before = ORM::for_table('categories')->count();
        date_default_timezone_set('Asia/Tokyo');
        $form = $crawler->selectButton('Register')->form();
        $form['form[label]'] = basename(__FILE__, '.php') . date('YmdHis');
        $crawler = $client->submit($form);
        $this->assertSame($before + 1, ORM::for_table('categories')->count(), '1 category inserted');
    }
}
