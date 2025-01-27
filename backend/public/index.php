<?php

spl_autoload_register(function ($class) {
    $class = str_replace('App\\', '', $class); 
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class); 
    
    $file = __DIR__ . '/../src/' . $class . '.php'; 

    if (file_exists($file)) {
        require_once $file;
    } else {
        throw new Exception("Class {$class} not found with path: {$file}");
    }
});

use App\Router;


\App\Database\InitializeDB::run();
$router = new Router();
$router->get('/subject', [\App\Controllers\SubjectController::class, 'getSubject']);
$router->get('/teacher', [\App\Controllers\TeacherController::class, 'getTeacher']);
$router->get('/schedule', [\App\Controllers\ScheduleController::class, 'getSchedule']);
$router->get('/classroom', [\App\Controllers\ClassroomController::class, 'getClassroom']);
$router->get('/building', [\App\Controllers\BuildingController::class, 'getBuilding']);
$router->run();
