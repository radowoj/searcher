<?php

namespace Radowoj\Searcher;

use Radowoj\Searcher\SearchProvider\ISearchProvider;

class Searcher
{

    protected $provider = null;

    protected $query = '';

    protected $limit = 100;

    protected $offset = 0;


    public function __construct(ISearchProvider $provider)
    {
        $this->provider = $provider;
    }


    public function query(string $query)
    {
        $this->query = $query;
        return $this;
    }


    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }


    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }


    public function find()
    {
        return $this->provider->search($this->query, $this->limit, $this->offset);
    }

}
