<?php

namespace Radowoj\Searcher\SearchProvider;

use stdClass;
use Radowoj\Searcher\SearchResult\ICollection;

abstract class SearchProvider implements ISearchProvider
{

    abstract protected function searchRequest(string $query, int $limit, int $offset) : stdClass;

    abstract protected function getCollection(stdClass $result);

    public function search(string $query, int $limit = 100, int $offset = 0) : ICollection
    {
        return $this->getCollection(
            $this->searchRequest($query, $limit, $offset)
        );
    }

}
