<?php

namespace Radowoj\Searcher\SearchResult;

class Collection
{
    protected $items = [];

    protected $totalMatches = null;


    public function __construct(array $items, int $totalMatches)
    {
        $this->items = $items;
        $this->totalMatches = $totalMatches;
    }
}
