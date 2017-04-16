<?php

namespace Radowoj\Searcher\SearchProvider;

abstract class SearchProvider implements ISearchProvider
{

    abstract protected function searchRequest(string $query, int $limit, int $offset);

    public function search(string $query, int $limit, int $offset)
    {
        return $this->makeCollection(
            $this->searchRequest($query, $limit, $offset)
        );
    }

}
