<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require_once PROJECT_DIR . '/src/lib/FormFactory.php';

$app->get('/', function() use ($app) {
    return $app['twig']->render('top.twig');
});
