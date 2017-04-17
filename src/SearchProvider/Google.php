<?php

namespace Radowoj\Searcher\SearchProvider;


use stdClass;
use Exception;

use GuzzleHttp\Client as GuzzleClient;

use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\ICollection;
use Radowoj\Searcher\SearchResult\Item;



class Google extends SearchProvider implements ISearchProvider
{
    const URI = 'https://www.googleapis.com/customsearch/v1?';

    protected $apiKey = null;

    protected $cx = null;

    protected $guzzle = null;

    public function __construct(GuzzleClient $guzzle, string $apiKey, string $cx)
    {
        $this->apiKey = $apiKey;
        $this->cx = $cx;
        $this->guzzle = $guzzle;
    }


    protected function getSearchQueryString(string $query, int $limit, int $offset)
    {
        $params = [
            'key' => $this->apiKey,
            'q' => urlencode($query),
            'cx' => $this->cx,
        ];

        if ($offset) {
            $params['start'] = $offset;
        }

        $paramsMerged = [];

        foreach($params as $key => $value) {
            $paramsMerged[] = "{$key}={$value}";
        }

        return implode('&', $paramsMerged);
    }


    protected function searchRequest(string $query, int $limit, int $offset) : stdClass
    {
        $uri = self::URI . $this->getSearchQueryString($query, $limit, $offset);

        $result = $this->guzzle->request('GET', $uri);
        return json_decode($result->getBody());
    }


    protected function enforceLimit(stdClass $result, int $limit) : stdClass
    {
        $result->items = array_slice($result->items, 0, $limit);
        return $result;
    }


    protected function validateRequestResult(stdClass $result)
    {
        if (!isset($result->searchInformation->totalResults) || !isset($result->items)) {
            throw new Exception("Invalid Google API response: " . print_r($result, 1));
        }
    }


    protected function populateCollection(stdClass $result) : ICollection
    {
        $results = array_map(function($item) {
            return new Item([
                'url' => $item->link,
                'title' => $item->title,
                'description' => $item->snippet,
            ]);
        }, $result->items);


        return new Collection(
            $results,
            $result->searchInformation->totalResults
        );

        return new Collection();
    }

}
