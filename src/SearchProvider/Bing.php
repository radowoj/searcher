<?php

namespace Radowoj\Searcher\SearchProvider;

use stdClass;
use InvalidArgumentException;

use GuzzleHttp\Client as GuzzleClient;

use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\Item;


class Bing extends SearchProvider implements ISearchProvider
{
    const URI = 'https://api.cognitive.microsoft.com/bing/v5.0/search?';
    const API_KEY_HEADER = 'Ocp-Apim-Subscription-Key';

    protected $apiKey = null;

    protected $guzzle = null;

    public function __construct(string $apiKey, GuzzleClient $guzzle)
    {
        $this->apiKey = $apiKey;
        $this->guzzle = $guzzle;
    }


    protected function searchRequest(string $query, int $limit, int $offset)
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


    protected function makeCollection(stdClass $result)
    {
        if (!isset($result->webPages->value)) {
            throw new InvalidArgumentException("Invalid Bing API response: " . print_r($result, 1));
        }

        if (!isset($result->webPages->totalEstimatedMatches)) {
            throw new InvalidArgumentException("Invalid Bing API response: " . print_r($result, 1));
        }

        $results = array_map(function($item) {
            return new Item([
                'url' => $item->displayUrl,
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
