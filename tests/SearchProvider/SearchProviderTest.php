<?php

namespace Radowoj\Searcher;

use PHPUnit\Framework\TestCase;
use Radowoj\Searcher\SearchProvider\SearchProvider;
use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\ICollection;
use stdClass;

class SearchProviderTest extends TestCase
{
    public function testReturnsCollection()
    {
        $mock = $this->getMockForAbstractClass(SearchProvider::class);

        $mock->expects($this->once())
            ->method('searchRequest')
            ->will($this->returnValue(new stdClass));

        $mock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue(new Collection));

        $result = $mock->search('foo');
        $this->assertInstanceOf(ICollection::class, $result);
    }
}
