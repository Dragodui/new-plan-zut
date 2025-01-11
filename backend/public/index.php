<?php
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../vendor/autoload.php';


use App\Router;

$router = new Router();
$router->get('/subject', [\App\Controllers\SubjectController::class, 'getSubject']);
$router->get('/teacher', [\App\Controllers\TeacherController::class, 'getTeacher']);
$router->get('/schedule', [\App\Controllers\ScheduleController::class, 'getSchedule']);
$router->run();
