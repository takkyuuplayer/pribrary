<?php
class BookTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->app = require __DIR__ . '/../src/bootstrap.php';
    }
    public function testSave()
    {
        $max = ORM::for_table('books')
            ->where_equal('category_id', 1)
            ->max('number');

        $book = Model::factory('Books')->create();

        $book->category_id = 1;
        $book->number = $max+1;
        $book->author      = basename(__FILE__, '.php');
        $book->title       = basename(__FILE__, '.php');
        $book->publisher   = basename(__FILE__, '.php');
        $book->save();

        $this->assertSame($book->number, $max + 1, 'number was incremented');
    }
}
