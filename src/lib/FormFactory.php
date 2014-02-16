<?php
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;

class FormFactory
{
    public static function getCategoryEditForm(Silex\Application $app)
    {
        return $app['form.factory']->createBuilder('form')
            ->add('label', 'text', array(
                'constraints' => array(new Assert\NotBlank(),)))
            ->add('description', 'text')
            ->getForm()
        ;
    }

    public static function getBookEditForm(Silex\Application $app, array $category_ids)
    {
        return $app['form.factory']->createBuilder('form')
            ->add('category_id', 'text', array(
                'constraints' => array(new Assert\Choice(array_values($category_ids)),),
            ))
            ->add('author', 'text', array(
                'constraints' => array(new Assert\NotBlank(),)))
            ->add('title', 'text', array(
                'constraints' => array(new Assert\NotBlank(),)))
            ->add('publisher', 'text', array(
                'constraints' => array(new Assert\NotBlank(),)))
            ->add('comment', 'text')
            ->getForm()
        ;
    }

    public static function getBookBorrowForm(Silex\Application $app, array $book_ids)
    {
        return $app['form.factory']->createBuilder('form')
            ->add('book_id', 'text', array(
                'constraints' => array(new Assert\Choice(array_values($book_ids)),),
            ))
            ->add('user', 'text', array(
                'constraints' => array(new Assert\NotBlank(),)))
            ->add('start_date', 'text', array(
                'constraints' => array(new Assert\Date(), new Assert\NotBlank(),)))
            ->add('end_date', 'text', array(
                'constraints' => array(new Assert\Date(), new Assert\NotBlank(),)))
            ->add('place', 'text', array(
                'constraints' => array(new Assert\NotBlank(),)))
            ->add('comment', 'text')
            ->getForm()
        ;
    }
}
