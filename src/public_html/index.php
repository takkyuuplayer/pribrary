<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Silex\Provider\FormServiceProvider;

$app = require __DIR__ . '/../bootstrap.php';
require_once PROJECT_DIR . '/src/controller.php';

$app->run();
