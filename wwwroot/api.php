<?php

require __DIR__ . '/../vendor/autoload.php';


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Exception;


$app = new Silex\Application();


// Books lolilol database

$data_file = __DIR__ . '/data/books.data.php';
if (!is_file($data_file)) {
  copy($data_file . '-dist', $data_file);
}

$app['books_db'] = array(
  'data' => require $data_file,
  'save' => function () use ($app, $data_file) {
    file_put_contents($data_file, $app['books_db']['data']);
  }
);


// "books" API

$app->get('/books', function () use ($app) {
  return $app->json($app['books_db']['data']);
});

$app->post('/books', $checkJSON, function () use ($app) {
  // TODO
});

$app->get('/books/{id}', function () use ($app) {
  // TODO
});

$app->put('/books/{id}', function () use ($app) {
  // TODO
});

$app->delete('/books/{id}', function () use ($app) {
  // TODO
});

$app->delete('/books', function () use ($app) {
  // TODO
});


// Start server (kinda)

$app->run();
