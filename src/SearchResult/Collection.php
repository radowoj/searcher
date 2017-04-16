<?php

namespace Radowoj\Searcher\SearchResult;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Exception;
use OutOfRangeException;

class Collection implements ICollection, ArrayAccess, IteratorAggregate, Countable
{
    protected $items = [];

    protected $totalMatches = null;


    public function __construct(array $items, int $totalMatches)
    {
        $this->items = $items;
        $this->totalMatches = $totalMatches;
    }


    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }


    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfRangeException("Invalid collection index: {$offset}");
        }

        return $this->items[$offset];
    }


    protected function immutable()
    {
        throw new Exception("Search result collection is immutable.");
    }


    public function offsetSet($offset, $value)
    {
        $this->immutable();
    }


    public function offsetUnset($offset)
    {
        $this->immutable();
    }


    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }


    public function count()
    {
        return count($this->items);
    }


    public function totalCount()
    {
        return $this->totalMatches;
    }

}
