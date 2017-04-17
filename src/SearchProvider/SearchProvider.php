<?php

namespace Radowoj\Searcher\SearchProvider;

use stdClass;
use Radowoj\Searcher\SearchResult\ICollection;

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
     * Populates Search Result Collection from data found in API response
     * @param  stdClass     $result API response
     * @return Radowoj\Searcher\SearchResult\ICollection  Collection of results
     */
    abstract protected function populateCollection(stdClass $result) : ICollection;


    /**
     * Usually API will ignore our limit, so we must take care of it
     * @param  stdClass $result to modify
     * @param  int       $limit  limit to enforce
     * @return stdClass - result after enforcing limit
     */
    abstract protected function enforceLimit(stdClass $result, int $limit) : stdClass;


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

}
