<?php
session_start();
require_once __DIR__ . '/../src/lib/FormFactory.php';
class FormFactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->app = require __DIR__ . '/../src/bootstrap.php';
        $this->app['session.test'] = true;
    }
    public function testCategoryEditForm()
    {
        $form = FormFactory::getCategoryEditForm($this->app);
        $form->bind(array());
        $this->assertFalse($form->isValid());

        $form = FormFactory::getCategoryEditForm($this->app);
        $form->bind(array('label' => 'la',
                        'description' => 'de'));
        $this->assertFalse($form->isValid());

        $form = FormFactory::getCategoryEditForm($this->app);
        $view = $form->createView();
        $form->bind(array('label' => 'la',
            'description' => 'de',
            '_token' => $view['_token']->vars['value'],
        ));
        $this->assertTrue($form->isValid());
    }

    public function testBookEditForm()
    {
        $category_ids = array_map(function($row) {
            return $row['id'];
        }, ORM::for_table('categories')
            ->select('id')
            ->find_array()
        );

        $form = FormFactory::getBookEditForm($this->app, $category_ids);
        $view = $form->createView();
        $form->bind(array('category_id' => $category_ids[0],
            'author' => 'author',
            'title' => 'title',
            'publisher' => 'pub',
            'comment' => 'co',
            '_token' => $view['_token']->vars['value'],
        ));
        $this->assertTrue($form->isValid());

        $form = FormFactory::getBookEditForm($this->app, $category_ids);
        $view = $form->createView();
        $form->bind(array('category_id' => $category_ids[count($category_ids)-1],
            'author' => 'author',
            'title' => 'title',
            'publisher' => 'pub',
            'comment' => 'co',
            '_token' => $view['_token']->vars['value'],
        ));
        $this->assertTrue($form->isValid());
    }
}
