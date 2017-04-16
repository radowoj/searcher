<?php

namespace Radowoj\Searcher\SearchProvider;

interface ISearchProvider
{

    public function search(string $query, int $limit, int $offset);
}
