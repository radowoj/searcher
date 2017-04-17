<?php

namespace Radowoj\Searcher\SearchProvider;

use stdClass;
use Exception;

use GuzzleHttp\Client as GuzzleClient;

use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\ICollection;
use Radowoj\Searcher\SearchResult\Item;
use Radowoj\Searcher\SearchResult\IItem;


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

        return json_decode($result->getBody());
    }


    protected function validateRequestResult(stdClass $result)
    {
        if (!isset($result->_type) || $result->_type !== 'SearchResponse') {
            throw new Exception("Invalid Bing API response: " . print_r($result, 1));
        }
    }


    protected function enforceLimit(stdClass $result, int $limit) : stdClass
    {
        if (!isset($result->webPages->value)) {
            return $result;
        }
        $result->webPages->value = array_slice($result->webPages->value, 0, $limit);
        return $result;
    }


    protected function extractResults(stdClass $result) : array
    {
        return isset($result->webPages->value)
            ? $result->webPages->value
            : [];
    }


    protected function extractTotalMatches(stdClass $result) : int
    {
        return isset($result->webPages->totalEstimatedMatches)
            ? $result->webPages->totalEstimatedMatches
            : 0;
    }
    

    protected function populateItem(stdClass $item) : IItem
    {
        return new Item([
            'url' => preg_match('/^https?:\/\//', $item->url)
                ? $item->url
                : "http://{$item->url}",
            'title' => $item->name,
            'description' => $item->snippet,
        ]);
    }

}
