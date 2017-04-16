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

//Array access
var_dump(($results[5])->toArray());

//Traversable
foreach($results as $result){
    var_dump($result->toArray());
}

//Countable
var_dump(count($results));

//...and total-result-countable ;)
var_dump($results->totalCount());
