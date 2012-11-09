<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/FileDB.php';


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Exception;


$app = new Silex\Application();


// Books lolilol database

$data_file = __DIR__ . '/../data/books.data.php';
if (!is_file($data_file)) {
  copy($data_file . '-dist', $data_file);
}

$app['books_db'] = new FileDB($data_file);


// Books validator

$app['book_schema'] = json_decode(file_get_contents('../books.schema.json'));
$app['json_schema_validator'] = new JsonSchema\Validator();


// Error management

$app->error(function (Exception $e, $code) use ($app) {
  $message = $e->getMessage();
  if (!$message) {
    $message = Response::$statusTexts[$code];
  }
  return $app->json($message, $code);
});


// Only accept JSON

$checkJSON = function (Request $request) use ($app) {
  if (0 !== strpos($request->headers->get('Content-Type'), 'application/json')) {
    return $app->abort(400, 'application/json required');
  }
  $data = json_decode($request->getContent(), true);
  if (is_null($data)) {
    return $app->abort(400, 'valid non-null JSON expected');
  }
};


// Only accept valid book

$checkBook = function (Request $request) use ($app) {
  $book = json_decode($request->getContent());
  $validator = $app['json_schema_validator'];
  $validator->check($book, $app['book_schema']);
  if (!$validator->isValid()) {
    return $app->json($validator->getErrors(), 400);
  }
};


// Check {id} is a valid book

$checkBookId = function (Request $request) use ($app) {
  $book = $app['books_db']->get($request->get('id'));
  if (!$book) return $app->abort(404);
  $request->request->set('book', $book);
};


// Add link to schema

$addLinkToSchema = function (Request $request, Response $response) use ($app) {
  if ($response instanceof JsonResponse) {
    $links = $response->headers->get('Link');
    if (!is_array($links)) {
      $links = array();
    }
    $schema_url = 'http' . (@$_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/books/$schema';
    $links[] = '<' . $schema_url . '>; rel="describedby"';
    $response->headers->set('Link', $links);
  }
  return $response;
};


// Provide schema

$app->get('/books/$schema', function () use ($app) {
  $response = $app->json($app['book_schema']);
  $response->headers->set('Content-Type', 'Content-Type: application/json; profile=http://json-schema.org/draft-03/hyper-schema');
  return $response;
});


// Home

$app->get('/', function () use ($app) {
  return file_get_contents('index.html');
});


// "books" API

$app->get('/books', function () use ($app) {
  return $app->json($app['books_db']->all());
})->after($addLinkToSchema);

$app->post('/books', function (Request $request) use ($app) {
  $id = $app['books_db']->add(json_decode($request->getContent(), true));
  $app['books_db']->persist();
  $response = new Response(201);
  $response->headers->set('location', '/books/' . $id);
  return $response;
})->before($checkJSON)->before($checkBook)->after($addLinkToSchema);

$app->get('/books/{id}', function (Request $request) use ($app) {
  return $app->json($request->get('book'));
})->before($checkBookId)->after($addLinkToSchema);

$app->put('/books/{id}', function (Request $request) use ($app) {
  $app['books_db']->set($request->get('id'), json_decode($request->getContent(), true));
  $app['books_db']->persist();
  return new Response(null, 204);
})->before($checkBookId)->before($checkJSON)->before($checkBook)->after($addLinkToSchema);

$app->delete('/books/{id}', function (Request $request) use ($app) {
  $app['books_db']->remove($request->get('id'));
  $app['books_db']->persist();
  return new Response(null, 204);
})->before($checkBookId);

$app->delete('/books', function () use ($app) {
  $app['books_db']->clear();
  $app['books_db']->persist();
  return new Response(null, 204);
});


// Start server (kinda)

$app->run();
