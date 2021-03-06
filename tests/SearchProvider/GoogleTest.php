<?php

namespace Radowoj\Searcher\SearchProvider;

use PHPUnit\Framework\TestCase;
use Radowoj\Searcher\SearchProvider\Google;
use Radowoj\Searcher\SearchProvider\ISearchProvider;
use Radowoj\Searcher\SearchResult\ICollection;
use GuzzleHttp\Client as GuzzleClient;

class GoogleTest extends TestCase
{

    const TEST_API_KEY = 'foo-api-key';
    const TEST_CX = 'foo-cx';

    protected $guzzleMockBuilder = null;


    public function setUp()
    {
        $this->guzzleMockBuilder = $this->getMockBuilder(GuzzleClient::class);
    }


    public function testInstantiation()
    {
        $google = new Google($this->guzzleMockBuilder->getMock(), self::TEST_API_KEY, self::TEST_CX);
        $this->assertInstanceOf(ISearchProvider::class, $google);
    }



    public function providerSearchRequestParams()
    {
        return [
            'just search string' => ['search string'],
            'string with limit' => ['some search string', 42],
            'search string with limit and offset' => ['foo search string', 36, 11]
        ];
    }


    protected function getResponseMock($returnValue = null, $statusCode = 200)
    {
        if (is_null($returnValue)) {
            $returnValue = (object)[
                'kind' => 'customsearch#search',
                'items' => [],
                'searchInformation' => [
                    'totalResults' => 0,
                ]
            ];
        }

        $responseMock = $this->getMockBuilder('\GuzzleHttp\Psr7\Response')
            ->setMethods(['getBody', 'getStatusCode'])
            ->getMock();

        $responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn(json_encode(
                $returnValue
            ));

        $responseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn($statusCode);

        return $responseMock;
    }


    /**
     * @dataProvider providerSearchRequestParams
     */
    public function testSearchRequest($searchString, $limit = 100, $offset = 0)
    {
        $encodedSearchString = urlencode($searchString);

        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();

        $responseMock = $this->getResponseMock();

        $apiKey = self::TEST_API_KEY;
        $cx = self::TEST_CX;

        $queryUrl = "https://www.googleapis.com/customsearch/v1?key={$apiKey}&q={$encodedSearchString}&cx={$cx}";
        if ($offset) {
            $queryUrl .= "&start={$offset}";
        }

        $guzzleMock->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                $queryUrl
            )->willReturn($responseMock);

        $google = new Google($guzzleMock, self::TEST_API_KEY, self::TEST_CX);

        $google->search($searchString, $limit, $offset);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid Google API response
     */
    public function testExceptionOnInvalidApiResponse()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();

        $responseMock = $this->getResponseMock((object)[]);

        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $google = new Google($guzzleMock, self::TEST_API_KEY, self::TEST_CX);

        $google->search('foo bar');
    }


    public function testLimitIsEnforced()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();

        $responseMock = $this->getResponseMock((object)[
            'kind' => 'customsearch#search',
            'items' => array_fill(0, 7, (object)[
                'link' => 'http://example.com',
                'title' => 'Some title',
                'snippet' => 'Some description',
            ]),
            'searchInformation' => [
                'totalResults' => 7000,
            ]
        ]);

        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $google = new Google($guzzleMock, self::TEST_API_KEY, self::TEST_CX);

        //limit results to two
        $result = $google->search('foo bar', 2);
        $this->assertSame(2, $result->count());
    }


    public function testAcceptsEmptySearchResult()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();

        $responseMock = $this->getResponseMock((object)[
            'kind' => 'customsearch#search',
        ]);

        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $google = new Google($guzzleMock, self::TEST_API_KEY, self::TEST_CX);
        $result = $google->search('something');
        $this->assertInstanceOf(ICollection::class, $result);
    }


    /**
     * @expectedException Radowoj\Searcher\Exceptions\QuotaExceededException
     */
    public function testQuotaExceeded()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();
        $responseMock = $this->getResponseMock((object)[], 403);
        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $google = new Google($guzzleMock, self::TEST_API_KEY, self::TEST_CX);
        $result = $google->search('something');
    }


    /**
     * @expectedException Radowoj\Searcher\Exceptions\Exception
     */
    public function testHttpError()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();

        //possible only if Bing search server is a teapot, but... let's test it ;p
        $responseMock = $this->getResponseMock((object)[], 418);
        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $google = new Google($guzzleMock, self::TEST_API_KEY, self::TEST_CX);
        $result = $google->search('something');
    }

}
