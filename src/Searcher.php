<?php

namespace Radowoj\Searcher;

use Radowoj\Searcher\SearchProvider\ISearchProvider;
use Radowoj\Searcher\SearchResult\ICollection;

class Searcher implements ISearcher
{

    protected $provider = null;

    protected $query = '';

    protected $limit = 100;

    protected $offset = 0;


    public function __construct(ISearchProvider $provider)
    {
        $this->provider = $provider;
    }


    public function query(string $query) : ISearcher
    {
        $this->query = $query;
        return $this;
    }


    public function limit(int $limit) : ISearcher
    {
        $this->limit = $limit;
        return $this;
    }


    public function offset(int $offset) : ISearcher
    {
        $this->offset = $offset;
        return $this;
    }


    public function find() : ICollection
    {
        return $this->provider->search($this->query, $this->limit, $this->offset);
    }

}
