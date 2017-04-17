# searcher

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/radowoj/searcher/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/radowoj/searcher/?branch=master) 
[![Code Coverage](https://scrutinizer-ci.com/g/radowoj/searcher/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/radowoj/searcher/?branch=master) 
[![Build Status](https://scrutinizer-ci.com/g/radowoj/searcher/badges/build.png?b=master)](https://scrutinizer-ci.com/g/radowoj/searcher/build-status/master)

Unified search API for Google, Bing and maybe others in the future. Utilizes Guzzle for http requests.

## Goal

* To use Google / Bing / possibly other Web Search Api with the same interface, regardless of which one is being used under the hood.

## Requirements
* PHP >= 7.0
* guzzlehttp/guzzle

## Usage

```php
use GuzzleHttp\Client as GuzzleClient;
use Radowoj\Searcher\SearchProvider\Bing;
use Radowoj\Searcher\Searcher;

$client = new GuzzleClient();

// 
// If you want to use Google
//
$searchProvider = new Google(
    $client,
    'your-google-api-key',
    'your-custom-search-cx-value-set-in-google-account-panel'
);

//
// Or if you want to use Bing
//
$searchProvider = new Bing(
    $client,
    'your-bing-api-key'
);

//
// Rest of the necessary code is the same regardless of search provider used
//

$searcher = new Searcher($searchProvider);

$results = $searcher->query('"nyan cat"')
    ->limit(10)
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
```
