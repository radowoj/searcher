<?php

namespace Radowoj\Searcher\SearchProvider;

use PHPUnit\Framework\TestCase;
use Radowoj\Searcher\SearchProvider\Bing;
use Radowoj\Searcher\SearchProvider\ISearchProvider;
use Radowoj\Searcher\SearchResult\ICollection;
use GuzzleHttp\Client as GuzzleClient;

class BingTest extends TestCase
{

    const TEST_API_KEY = 'foo-api-key';

    protected $guzzleMockBuilder = null;

    public function setUp()
    {
        $this->guzzleMockBuilder = $this->getMockBuilder(GuzzleClient::class);
    }


    public function testInstantiation()
    {
        $bing = new Bing($this->guzzleMockBuilder->getMock(), self::TEST_API_KEY);
        $this->assertInstanceOf(ISearchProvider::class, $bing);
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
                '_type' => 'SearchResponse',
                'webPages' => [
                    'value' => [],
                    'totalEstimatedMatches' => 0,
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

        $guzzleMock->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.cognitive.microsoft.com/bing/v5.0/search?q={$encodedSearchString}&count={$limit}&offset={$offset}"
            )->willReturn($responseMock);

        $bing = new Bing($guzzleMock, self::TEST_API_KEY);

        $bing->search($searchString, $limit, $offset);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid Bing API response
     */
    public function testExceptionOnInvalidApiResponse()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();

        $responseMock = $this->getResponseMock((object)[]);

        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $bing = new Bing($guzzleMock, self::TEST_API_KEY);

        $bing->search('foo bar');
    }


    public function testLimitIsEnforced()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();

        $responseMock = $this->getResponseMock((object)[
            '_type' => 'SearchResponse',
            'webPages' => [
                'value' => array_fill(0, 7, (object)[
                    'url' => 'http://example.com',
                    'name' => 'Some title',
                    'snippet' => 'Some description',
                ]),
                'totalEstimatedMatches' => 7000,
            ]
        ]);

        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $bing = new Bing($guzzleMock, self::TEST_API_KEY);

        //limit results to two
        $result = $bing->search('foo bar', 2);
        $this->assertSame(2, $result->count());
    }


    public function testAcceptsEmptySearchResult()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();

        $responseMock = $this->getResponseMock((object)[
            '_type' => 'SearchResponse',
        ]);

        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $bing = new Bing($guzzleMock, self::TEST_API_KEY);
        $result = $bing->search('something');
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

        $bing = new Bing($guzzleMock, self::TEST_API_KEY);
        $result = $bing->search('something');
    }


    /**
     * @expectedException Radowoj\Searcher\Exceptions\RateLimitExceededException
     */
    public function testRateLimitExceeded()
    {
        $guzzleMock = $this->guzzleMockBuilder->setMethods(['request'])->getMock();
        $responseMock = $this->getResponseMock((object)[], 429);
        $guzzleMock->expects($this->once())
            ->method('request')
            ->willReturn($responseMock);

        $bing = new Bing($guzzleMock, self::TEST_API_KEY);
        $result = $bing->search('something');
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

        $bing = new Bing($guzzleMock, self::TEST_API_KEY);
        $result = $bing->search('something');
    }


}
