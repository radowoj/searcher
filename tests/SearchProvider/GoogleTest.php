<?php

namespace Radowoj\Searcher\SearchProvider;

use PHPUnit\Framework\TestCase;
use Radowoj\Searcher\SearchProvider\Google;
use Radowoj\Searcher\SearchProvider\ISearchProvider;
use GuzzleHttp\Client as GuzzleClient;

class GoogleTest extends TestCase
{

    const TEST_API_KEY = 'foo-api-key';
    const TEST_CX = 'foo-cx';

    protected $guzzleMockBuilder = null;

    public function setUp()
    {
        $this->guzzleMockBuilder = $this->getMockBuilder(GuzzleClient::class)
            ->setMethods([]);
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


    protected function getResponseMock($returnValue = null)
    {
        if (is_null($returnValue)) {
            $returnValue = (object)[
                'items' => [],
                'searchInformation' => [
                    'totalResults' => 0,
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

}
