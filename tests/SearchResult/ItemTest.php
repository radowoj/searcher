<?php

namespace Radowoj\Searcher\SearchResult;

use PHPUnit\Framework\TestCase;
use Radowoj\Searcher\SearchResult\Item;
use Radowoj\Searcher\SearchResult\IItem;

class ItemTest extends TestCase
{
    public function testInstantiation()
    {
        $item = new Item([]);
        $this->assertInstanceOf(IItem::class, $item);
    }


    public function testArrayRepresentation()
    {
        $array = [
            'url' => 'http://example.com',
            'title' => 'some title',
            'description' => 'some description',
        ];

        $item = new Item($array);

        $this->assertSame($array, $item->toArray());
    }


    public function testToString()
    {
        $url = 'http://example.com';
        $item = new Item(['url' => $url]);
        $this->assertSame($url, (string)$item);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid URL given:
     */
    public function testInvalidUrl()
    {
        $url = 'some invalid url';
        $item = new Item(['url' => $url]);
    }
}
