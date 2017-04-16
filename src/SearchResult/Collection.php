<?php

namespace Radowoj\Searcher\SearchResult;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Exception;
use OutOfRangeException;
use InvalidArgumentException;

class Collection implements ICollection, ArrayAccess, IteratorAggregate, Countable
{
    protected $items = [];

    protected $totalMatches = 0;


    public function __construct(array $items = [], int $totalMatches = 0)
    {
        foreach($items as $item) {
            if (!$item instanceof IItem) {
                throw new InvalidArgumentException('Given items must implement Radowoj\Searcher\SearchResult\IItem interface');
            }
        }
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
        return $this->immutable();
    }


    public function offsetUnset($offset)
    {
        return $this->immutable();
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
