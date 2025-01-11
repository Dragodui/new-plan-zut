<?php
require_once __DIR__ . '/../src/Router.php';

use App\Router;

$router = new Router();
$router->get('/subject', [\App\Controllers\SubjectController::class, 'getSubject']);
$router->get('/subject', [\App\Controllers\TeacherController::class, 'getTeacher']);
$router->get('/subject', [\App\Controllers\ScheduleController::class, 'getSchedule']);
$router->run();
