<?php

namespace Radowoj\Searcher\SearchProvider;

use PHPUnit\Framework\TestCase;
use Radowoj\Searcher\SearchProvider\Bing;
use Radowoj\Searcher\SearchProvider\ISearchProvider;
use GuzzleHttp\Client as GuzzleClient;

class BingTest extends TestCase
{

    const TEST_API_KEY = 'foo-api-key';

    protected $guzzleMockBuilder = null;

    public function setUp()
    {
        $this->guzzleMockBuilder = $this->getMockBuilder(GuzzleClient::class)
            ->setMethods([]);
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


    protected function getResponseMock($returnValue = null)
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

        $responseMock = $this->getMockBuilder(GuzzleHttp\Psr7\Response::class)
            ->setMethods(['getBody'])
            ->getMock();

        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode(
                $returnValue
            ));

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

}
