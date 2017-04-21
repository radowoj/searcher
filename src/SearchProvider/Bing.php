<?php

namespace Radowoj\Searcher\SearchProvider;

use stdClass;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface as Psr7Response;

use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\ICollection;
use Radowoj\Searcher\SearchResult\Item;
use Radowoj\Searcher\SearchResult\IItem;

use Radowoj\Searcher\Exceptions\Exception;
use Radowoj\Searcher\Exceptions\QuotaExceededException;
use Radowoj\Searcher\Exceptions\RateLimitExceededException;

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
                    self::API_KEY_HEADER => $this->apiKey,
                ],
                'http_errors' => false,
            ]
        );

        return $this->decodeResponse($result);
    }


    /**
     * Handle response based on HTTP status code (catches 400s - usually quota or rate limit,
     * so authorisation errors and other stuff will be thrown as generic Searcher exception)
     *
     * On status == 200 it simply returns json-decoded response.
     *
     * @param  Psr7Response $result result from Guzzle
     * @return array
     */
    protected function decodeResponse(Psr7Response $result) : stdClass
    {
        switch($result->getStatusCode()) {
            case 200:
                return json_decode($result->getBody());
            case 403:   //Out of call volume quota
                throw new QuotaExceededException($result->getReasonPhrase());
            case 429:   //Rate limit is exceeded
                throw new RateLimitExceededException($result->getReasonPhrase());
            default:
                throw new Exception("Bing API responded with HTTP status {$result->getStatusCode()} - {$result->getReasonPhrase()}");
        }
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
