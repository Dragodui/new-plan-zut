<?php
require __DIR__ . '/../vendor/autoload.php';

// database password: mEXPo9So



use Slim\Factory\AppFactory;
use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'options' => [
    PDO::ATTR_TIMEOUT => 20, 
],
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$app = AppFactory::create();

// cors middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*') // change * to your domain in production
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS') 
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization'); 
});

$app->get('/', function ($request, $response) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get('/teacher', function($request, $response) {
    $queryParams = $request->getQueryParams();
    $teacher = $queryParams['teacher'] ?? null;

    if (!$teacher) {
        $error = ['error' => 'teacher are required'];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $apiUrl = "https://plan.zut.edu.pl/schedule.php?kind=teacher&query=" . urlencode($teacher);

    try {
        $client = new Client(['verify' => false]);
        $apiResponse = $client->request('GET', $apiUrl);
        $data = $apiResponse->getBody()->getContents();

        $response->getBody()->write($data);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $error = ['error' => 'Unable to fetch teacher schedule', 'details' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->get('/subject', function($request, $response) {
    $queryParams = $request->getQueryParams();
    $subject = $queryParams['subject'] ?? null;

    if (!$subject) {
        $error = ['error' => 'Subject are required'];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $apiUrl = "https://plan.zut.edu.pl/schedule.php?kind=subject&query=" . urlencode($subject);

    try {
        $client = new Client(['verify' => false]);
        $apiResponse = $client->request('GET', $apiUrl);
        $data = $apiResponse->getBody()->getContents();

        $response->getBody()->write($data);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $error = ['error' => 'Unable to fetch subject schedule', 'details' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->get('/classroom', function($request, $response) {
    $queryParams = $request->getQueryParams();
    $room = $queryParams['room'] ?? null;

    if (!$room) {
        $error = ['error' => 'Room are required'];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $apiUrl = "https://plan.zut.edu.pl/schedule.php?kind=room&query=" . urlencode($room);

    try {
        $client = new Client(['verify' => false]);
        $apiResponse = $client->request('GET', $apiUrl);
        $data = $apiResponse->getBody()->getContents();

        $response->getBody()->write($data);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $error = ['error' => 'Unable to fetch classroom schedule', 'details' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

use Illuminate\Database\Eloquent\Model;

// class Schedule extends Model {
//     protected $table = 'schedules';
//     protected $fillable = [
//         'title', 'start', 'end', 'description', 'worker_title', 'worker',
//         'room', 'group_name', 'tok_name', 'lesson_form', 'lesson_form_short',
//         'lesson_status', 'color'
//     ];
//     public $timestamps = false;
// }

// $app->get('/schedule', function ($request, $response) {
//     $queryParams = $request->getQueryParams();

//     // Проверяем, что переданы query параметры
//     if (empty($queryParams)) {
//         $error = ['error' => 'At least one query parameter is required'];
//         $response->getBody()->write(json_encode($error));
//         return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
//     }

//     // Определяем ключ для поиска в базе
//     $queryKey = http_build_query($queryParams);

//     // Проверяем наличие данных в базе
//     $existingSchedules = Schedule::where('description', $queryKey)->get();

//     if ($existingSchedules->isNotEmpty()) {
//         // Если данные есть, возвращаем их
//         $response->getBody()->write($existingSchedules->toJson());
//         return $response->withHeader('Content-Type', 'application/json');
//     }

//     // Если данных нет, делаем запрос к API
//     $apiUrl = "https://plan.zut.edu.pl/schedule_student.php?" . $queryKey;

//     try {
//         // Запрос к API
//         $client = new Client(['verify' => false]);
//         $apiResponse = $client->request('GET', $apiUrl);
//         $data = json_decode($apiResponse->getBody()->getContents(), true);

//         // Если API вернул данные, сохраняем их в базу
//         if (is_array($data)) {
//             foreach ($data as $item) {
//                 if (isset($item['title'])) { // Пропускаем невалидные записи
//                     Schedule::create([
//                         'title' => $item['title'],
//                         'start' => $item['start'],
//                         'end' => $item['end'],
//                         'description' => $queryKey, // Сохраняем ключ поиска для идентификации
//                         'worker_title' => $item['worker_title'] ?? null,
//                         'worker' => $item['worker'] ?? null,
//                         'room' => $item['room'] ?? null,
//                         'group_name' => $item['group_name'] ?? null,
//                         'tok_name' => $item['tok_name'] ?? null,
//                         'lesson_form' => $item['lesson_form'] ?? null,
//                         'lesson_form_short' => $item['lesson_form_short'] ?? null,
//                         'lesson_status' => $item['lesson_status'] ?? null,
//                         'color' => $item['color'] ?? null,
//                     ]);
//                 }
//             }
//         }

//         // Возвращаем данные клиенту из базы
//         $savedSchedules = Schedule::where('description', $queryKey)->get();
//         $response->getBody()->write($savedSchedules->toJson());
//         return $response->withHeader('Content-Type', 'application/json');
//     } catch (\Exception $e) {
//         // Обрабатываем ошибки
//         $error = ['error' => 'Unable to fetch schedule', 'details' => $e->getMessage()];
//         $response->getBody()->write(json_encode($error));
//         return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
//     }
// });


$app->get('/schedule', function ($request, $response) {
    $queryParams = $request->getQueryParams();

    if (empty($queryParams)) {
        $error = ['error' => 'At least one query parameter is required'];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $apiUrl = "https://plan.zut.edu.pl/schedule_student.php?" . http_build_query($queryParams);

    try {
        $client = new Client(['verify' => false]);
        $apiResponse = $client->request('GET', $apiUrl);
        $data = $apiResponse->getBody()->getContents();

        $response->getBody()->write($data);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $error = ['error' => 'Unable to fetch schedule', 'details' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();
