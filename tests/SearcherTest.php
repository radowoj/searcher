<?php

namespace Radowoj\Searcher;


use stdClass;

use PHPUnit\Framework\TestCase;
use Radowoj\Searcher\Searcher;
use Radowoj\Searcher\ISearcher;
use Radowoj\Searcher\SearchProvider\SearchProvider;
use Radowoj\Searcher\SearchResult\Collection;
use Radowoj\Searcher\SearchResult\ICollection;
use GuzzleHttp\Client as GuzzleClient;

class SearcherTest extends TestCase
{

    protected function getBasicSearcher()
    {
        $searchProvider = $this->getMockForAbstractClass(SearchProvider::class);
        $searcher = new Searcher($searchProvider);
        return $searcher;
    }


    public function testInstantiation()
    {
        $searcher = $this->getBasicSearcher();
        $this->assertInstanceOf(ISearcher::class, $searcher);
    }


    public function testQueryFluentInterface()
    {
        $searcher = $this->getBasicSearcher();
        $result = $searcher->query('some query');
        $this->assertInstanceOf(ISearcher::class, $result);
    }


    public function testLimitFluentInterface()
    {
        $searcher = $this->getBasicSearcher();
        $result = $searcher->limit(100);
        $this->assertInstanceOf(ISearcher::class, $result);
    }


    public function testOffsetFluentInterface()
    {
        $searcher = $this->getBasicSearcher();
        $result = $searcher->offset(100);
        $this->assertInstanceOf(ISearcher::class, $result);
    }


    public function providerTestFind()
    {
        return [
            ['nyan cat', 100, 0],
            ['some search query', 42, 51]
        ];
    }


    /**
     * @dataProvider providerTestFind
     */
    public function testFind($query, $limit, $offset)
    {
        $searchProvider = $this->getMockForAbstractClass(SearchProvider::class);

        $searchProvider->expects($this->once())
            ->method('searchRequest')
            ->with(
                $this->equalTo($query),
                $this->equalTo($limit),
                $this->equalTo($offset)
            )
            ->will($this->returnValue(new stdClass));

        $searchProvider->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue(new Collection));

        $searcher = new Searcher($searchProvider);

        $results = $searcher
            ->query($query)
            ->limit($limit)
            ->offset($offset)
            ->find();

        $this->assertInstanceOf(ICollection::class, $results);
    }


}
