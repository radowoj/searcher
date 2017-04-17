<?php

namespace Radowoj\Searcher\SearchProvider;

use stdClass;
use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\ICollection;
use Radowoj\Searcher\SearchResult\IItem;

abstract class SearchProvider implements ISearchProvider
{

    /**
     * Performs a search request and returns result as stdClass (json-decoded search API response)
     * @param  string    $query  search query
     * @param  int       $limit  number of results (API may not support it)
     * @param  int       $offset offset of results (again, API may not support it, but most APIs do)
     * @return stdClass - json-decoded response from API
     */
    abstract protected function searchRequest(string $query, int $limit, int $offset) : stdClass;


    /**
     * Checks response object for fields necessary to populate a collection
     * @param  stdClass API response
     * @throws Exception on invalid response
     */
    abstract protected function validateRequestResult(stdClass $result);


    /**
     * Usually API will ignore our limit, so we must take care of it
     * @param  stdClass $result to modify
     * @param  int       $limit  limit to enforce
     * @return stdClass - result after enforcing limit
     */
    abstract protected function enforceLimit(stdClass $result, int $limit) : stdClass;

    /**
     * Creates a search result item from API-specific stdClass representing single search result
     * @param  stdClass $item Single stdClass representing search result, fetched from search API
     * @return IItem          Radowoj\Searcher\SearchResult\IItem  Single search result item
     */
    abstract protected function populateItem(stdClass $item) : IItem;


    /**
     * Extracts results array from API-specific response object
     * @param  stdClass $result Response object fetched from API
     * @return array array of stdClasses representing results
     */
    abstract protected function extractResults(stdClass $result) : array;


    /**
     * Extracts total matches from API-specific response object
     * @param  stdClass $result Response object fetched from API
     * @return int total number of results
     */
    abstract protected function extractTotalMatches(stdClass $result) : int;


    /**
     * Performs and processes a search request
     * @param  string       $query  Search query
     * @param  integer      $limit  Result limit (API may return other amount, so result will be truncated)
     * @param  integer      $offset Result offset
     * @return Radowoj\Searcher\SearchResult\ICollection  Collection of results
     */
    public function search(string $query, int $limit = 100, int $offset = 0) : ICollection
    {
        $requestResult = $this->searchRequest($query, $limit, $offset);

        $this->validateRequestResult($requestResult);

        $requestResult = $this->enforceLimit($requestResult, $limit);

        return $this->populateCollection($requestResult);
    }


    /**
     * Populates result collection. Concrete class must provide methods to populate a single item,
     * extract results list from result object and extract total matches from result object
     * @param  stdClass    $result result object from search api
     * @return Radowoj\Searcher\SearchResult\ICollection  Collection of results
     */
    protected function populateCollection(stdClass $result) : ICollection
    {
        $results = array_map(function($item) {
            return $this->populateItem($item);

        }, $this->extractResults($result));

        return new Collection(
            $results,
            $this->extractTotalMatches($result)
        );
    }

}
