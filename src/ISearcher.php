<?php

namespace Radowoj\Searcher;

use Radowoj\Searcher\SearchResult\ICollection;

interface ISearcher
{
    public function query(string $query) : ISearcher;
    public function limit(int $limit) : ISearcher;
    public function offset(int $offset) : ISearcher;
    public function find() : ICollection;
}
