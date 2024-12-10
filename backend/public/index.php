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

$app->get('/schedule', function ($request, $response) {
    $queryParams = $request->getQueryParams();
    $studentNumber = $queryParams['number'] ?? null;

    if (!$studentNumber) {
        $error = ['error' => 'Student number is required'];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $apiUrl = "https://plan.zut.edu.pl/schedule_student.php?number=" . urlencode($studentNumber);

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
