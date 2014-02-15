<?php
use Silex\WebTestCase;

class BrowserTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../public_html/index.php';
        $app['debug'] = true;
        $app['exception_handler']->disable();

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
}
