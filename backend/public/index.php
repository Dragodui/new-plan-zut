<?php
require_once __DIR__ . '/../src/Router.php';

use App\Router;

$router = new Router();
$router->get('/subject', [\App\Controllers\SubjectController::class, 'getSubject']);
$router->run();
