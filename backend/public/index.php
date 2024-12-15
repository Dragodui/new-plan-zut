<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use GuzzleHttp\Client;

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
