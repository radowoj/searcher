<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client as GuzzleClient;
use Radowoj\Searcher\SearchProvider\Bing;
use Radowoj\Searcher\Searcher;

$config = require_once('config.php');

$client = new GuzzleClient();

$searchProvider = new Bing(
    $config['bing-api-key'],
    $client
);


$searcher = new Searcher($searchProvider);

$results = $searcher->query('"nyan cat"')
    ->limit(20)
    ->offset(0)
    ->find();

var_dump($results);
