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

        $result = $this->guzzle->request(
            'GET',
            $uri, [
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
        $decodedResult = json_decode($result->getBody());
        switch($result->getStatusCode()) {
            case 200:
                return $decodedResult;
            case 403:
                throw new QuotaExceededException(
                    $decodedResult->error->message ?? $result->getReasonPhrase()
                );
            default:
                throw new Exception("Google API responded with HTTP status {$result->getStatusCode()} - {$result->getReasonPhrase()}");
        }
    }


    protected function enforceLimit(stdClass $result, int $limit) : stdClass
    {
        if (!isset($result->items)) {
            return $result;
        }
        $result->items = array_slice($result->items, 0, $limit);
        return $result;
    }


    protected function validateRequestResult(stdClass $result)
    {
        if (!isset($result->kind) || $result->kind !== "customsearch#search") {
            throw new Exception("Invalid Google API response: " . print_r($result, 1));
        }
    }


    protected function extractResults(stdClass $result) : array
    {
        return isset($result->items)
            ? $result->items
            : [];
    }


    protected function extractTotalMatches(stdClass $result) : int
    {
        return isset($result->searchInformation->totalResults)
            ? $result->searchInformation->totalResults
            : 0;
    }


    protected function populateItem(stdClass $item) : IItem
    {
        return new Item([
            'url' => $item->link,
            'title' => $item->title,
            'description' => $item->snippet,
        ]);
    }

}
