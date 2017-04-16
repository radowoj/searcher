<?php

namespace Radowoj\Searcher\SearchProvider;

use stdClass;
use Exception;

use GuzzleHttp\Client as GuzzleClient;

use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\ICollection;
use Radowoj\Searcher\SearchResult\Item;


class Bing extends SearchProvider implements ISearchProvider
{
    const URI = 'https://api.cognitive.microsoft.com/bing/v5.0/search?';
    const API_KEY_HEADER = 'Ocp-Apim-Subscription-Key';

    protected $apiKey = null;

    protected $guzzle = null;

    public function __construct(GuzzleClient $guzzle, string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->guzzle = $guzzle;
    }


    protected function searchRequest(string $query, int $limit, int $offset) : stdClass
    {
        $params = [
            'q' => $query,
            'count' => $limit,
            'offset' => $offset
        ];

        $queryString = http_build_query($params);

        $uri = self::URI . $queryString;

        $result = $this->guzzle->request(
            'GET',
            $uri, [
                'headers' => [
                    self::API_KEY_HEADER => $this->apiKey
                ]
            ]
        );

        $resultObject = json_decode($result->getBody());
        $this->validateResult($resultObject);
        $resultObject = $this->limitResult($resultObject, $limit);
        return $resultObject;
    }


    protected function validateResult(stdClass $result)
    {
        if (!isset($result->webPages->value) || !isset($result->webPages->totalEstimatedMatches)) {
            throw new Exception("Invalid Bing API response: " . print_r($result, 1));
        }
    }


    protected function limitResult(stdClass $result, $limit)
    {
        $result->webPages->value = array_slice($result->webPages->value, 0, $limit);
        return $result;
    }


    protected function getCollection(stdClass $result) : ICollection
    {
        $results = array_map(function($item) {
            return new Item([
                'url' => preg_match('/^https?:\/\//', $item->url)
                    ? $item->url
                    : "http://{$item->url}",
                'title' => $item->name,
                'description' => $item->snippet,
            ]);
        }, $result->webPages->value);

        return new Collection(
            $results,
            $result->webPages->totalEstimatedMatches
        );
    }


}
