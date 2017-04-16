<?php

namespace Radowoj\Searcher\SearchProvider;

use Radowoj\Searcher\SearchResult\ICollection;

interface ISearchProvider
{

    public function search(string $query, int $limit, int $offset) : ICollection;
}
