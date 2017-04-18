<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client as GuzzleClient;
use Radowoj\Searcher\SearchProvider\Bing;
use Radowoj\Searcher\Searcher;
use Radowoj\Searcher\Exceptions\Exception;

$config = require_once('config.php');

$client = new GuzzleClient();

$searchProvider = new Bing(
    $client,
    $config['bing-api-key']
);


$searcher = new Searcher($searchProvider);

try {
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

} catch (Exception $e) {
    $exceptionClass = get_class($e);
    die(
        "Exception {$exceptionClass}: {$e->getMessage()}" . PHP_EOL .
        "Trace: " . PHP_EOL . 
        "{$e->getTraceAsString()}" . PHP_EOL
    );
}
