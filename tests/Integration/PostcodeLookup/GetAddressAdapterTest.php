<?php

namespace adamcameron\php8\tests\Integration\PostcodeLookup;

use adamcameron\php8\PostcodeLookup\GetAddressAdapter;
use adamcameron\php8\tests\Fixtures\PostcodeLookup\TestConstants;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * @testdox Tests of the GetAddressAdapter
 * @group slow
 */
class GetAddressAdapterTest extends TestCase
{
    private $adapter;

    protected function setUp(): void
    {
        $client = HttpClient::create();
        $this->adapter = new GetAddressAdapter(getenv("ADDRESS_SERVICE_API_KEY"), $client);
    }

    /** @testdox It can get addresses from a valid postcode */
    public function testCanGetAddress()
    {
        $response = $this->adapter->get(TestConstants::POSTCODE_OK);

        $this->assertEquals(Response::HTTP_OK, $response->getHttpStatus());
        $this->assertGreaterThanOrEqual(1, count($response->getAddresses()));
        $this->assertEmpty($response->getMessage());
    }

    public static function provideErrorTestCases(): array
    {
        return [
            // getaddress is misbehaving on 400s at the moment
            //"Invalid postcode (400)" => [TestConstants::POSTCODE_INVALID, Response::HTTP_BAD_REQUEST],
            "Invalid API key (401)" => [TestConstants::POSTCODE_UNAUTHORIZED, Response::HTTP_UNAUTHORIZED],
            "Invalid account (403)" => [TestConstants::POSTCODE_FORBIDDEN, Response::HTTP_FORBIDDEN],
            "Throttled (429)" => [TestConstants::POSTCODE_OVER_LIMIT, Response::HTTP_TOO_MANY_REQUESTS],
            "Server error (5xx)" => [TestConstants::POSTCODE_SERVER_ERROR, Response::HTTP_INTERNAL_SERVER_ERROR]
        ];
    }

    /**
     * @testdox It returns the expected HTTP status code and a message but no addresses on an error
     * @dataProvider provideErrorTestCases
     */
    public function testReturnsExpectedHttpStatusAndMessageButNoAddressesOnError($postcode, $expectedHttpStatus)
    {
        $response = $this->adapter->get($postcode);

        $this->assertEquals($expectedHttpStatus, $response->getHttpStatus());
        $this->assertNotEmpty($response->getMessage());
        $this->assertEquals(0, count($response->getAddresses()));
    }
}
