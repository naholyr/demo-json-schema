<?php

require './vendor/autoload.php';

$schema = file_get_contents('./books.schema.json');

$json = file_get_contents('php://stdin');

$validator = new JsonSchema\Validator();
$validator->check(json_decode($json), json_decode($schema));

if ($validator->isValid()) {
    echo "The supplied JSON validates against the schema.\n";
} else {
    echo "JSON does not validate. Violations:\n";
    foreach ($validator->getErrors() as $error) {
        echo sprintf("[%s] %s\n",$error['property'], $error['message']);
    }
}
