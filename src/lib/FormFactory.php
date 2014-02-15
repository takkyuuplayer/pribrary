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
}
