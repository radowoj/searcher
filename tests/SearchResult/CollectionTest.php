<?php

namespace Radowoj\Searcher\SearchResult;

use PHPUnit\Framework\TestCase;
use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\ICollection;
use Radowoj\Searcher\SearchResult\Item;
use Radowoj\Searcher\SearchResult\IItem;

use Traversable;
use Countable;
/**
 * @covers Radowoj\Searcher\SearchResult\Collection
 */
class CollectionTest extends TestCase
{

    protected $collection;


    public function setUp()
    {
        $this->collection = new Collection([
            0 => new Item([]),
            1 => new Item([]),
        ], 42);
    }


    public function testInstantiation()
    {
        $collection = new Collection();
        $this->assertInstanceOf(ICollection::class, $collection);
    }


    public function testOffsetExists()
    {
        $this->assertTrue($this->collection->offsetExists(0));
        $this->assertTrue($this->collection->offsetExists(1));
        $this->assertFalse($this->collection->offsetExists(2));
    }


    /**
     * @expectedException OutOfRangeException
     * @expectedExceptionMessage Invalid collection index: 2
     */
    public function testOffsetGet()
    {
        $this->assertInstanceOf(IItem::class, $this->collection->offsetGet(0));
        $this->assertInstanceOf(IItem::class, $this->collection->offsetGet(1));
        $this->assertInstanceOf(IItem::class, $this->collection->offsetGet(2));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Search result collection is immutable.
     */
    public function testOffsetSetImmutability()
    {
        $this->collection->offsetSet(1, 'asas');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Search result collection is immutable.
     */
    public function testOffsetUnsetImmutability()
    {
        $this->collection->offsetUnset(1);
    }


    public function testIterator()
    {
        $this->assertInstanceOf(Traversable::class, $this->collection);
        $this->assertInstanceOf(Traversable::class, $this->collection->getIterator());
    }


    public function testCountable()
    {
        $this->assertInstanceOf(Countable::class, $this->collection);
        $this->assertSame(2, $this->collection->count());
    }


    public function testTotalCount()
    {
        $this->assertSame(42, $this->collection->totalCount());
    }

}
