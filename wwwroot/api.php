<?php

require __DIR__ . '/../vendor/autoload.php';


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

$app['books_db'] = array(
  'data' => require $data_file,
  'save' => function () use ($app, $data_file) {
    file_put_contents($data_file, $app['books_db']['data']);
  }
);


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


// "books" API

$app->get('/books', function () use ($app) {
  return $app->json($app['books_db']['data']);
})->after($addLinkToSchema);

$app->post('/books', function () use ($app) {
  return $app->abort(501);
})->before($checkJSON)->before($checkBook)->after($addLinkToSchema);

$app->get('/books/{id}', function () use ($app) {
  return $app->abort(501);
})->after($addLinkToSchema);

$app->put('/books/{id}', $checkJSON, $checkBook, function () use ($app) {
  return $app->abort(501);
})->before($checkJSON)->before($checkBook)->after($addLinkToSchema);

$app->delete('/books/{id}', function () use ($app) {
  return $app->abort(501);
});

$app->delete('/books', function () use ($app) {
  return $app->abort(501);
});


// Start server (kinda)

$app->run();
