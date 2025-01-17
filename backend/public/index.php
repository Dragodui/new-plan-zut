<?php

require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Database/initializeDB.php';

use App\Router;


\App\Database\InitializeDB::run();
$router = new Router();
$router->get('/subject', [\App\Controllers\SubjectController::class, 'getSubject']);
$router->get('/teacher', [\App\Controllers\TeacherController::class, 'getTeacher']);
$router->get('/schedule', [\App\Controllers\ScheduleController::class, 'getSchedule']);
$router->get('/classroom', [\App\Controllers\ClassroomController::class, 'getClassroom']);
$router->run();
