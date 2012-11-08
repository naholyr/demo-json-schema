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


// Books validator

$schema = json_decode(file_get_contents('../books.schema.json'));
$json_schema_validator = new JsonSchema\Validator();
$app['book_validator'] = function ($book, &$errors) use ($schema, $json_schema_validator) {
  if (is_string($book)) {
    $book = json_decode($book);
  }
  $json_schema_validator->check($book, $schema);
  if ($json_schema_validator->isValid()) {
    return true;
  } else {
    $errors = $json_schema_validator->getErrors();
    return false;
  }
};


// Error management

$app->error(function (Exception $e, $code) use ($app) {
  $message = $e->getMessage();
  if (!$message) {
    $message = Response::$statusTexts[$code];
  }
  return $app->json($message, $code);
});


// "books" API

$app->get('/books', function () use ($app) {
  return $app->json($app['books_db']['data']);
});

$app->post('/books', $checkJSON, function () use ($app) {
  return $app->abort(501);
});

$app->get('/books/{id}', function () use ($app) {
  return $app->abort(501);
});

$app->put('/books/{id}', function () use ($app) {
  return $app->abort(501);
});

$app->delete('/books/{id}', function () use ($app) {
  return $app->abort(501);
});

$app->delete('/books', function () use ($app) {
  return $app->abort(501);
});


// Start server (kinda)

$app->run();
