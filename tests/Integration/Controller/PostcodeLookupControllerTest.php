<?php

namespace adamcameron\php8\tests\Integration\Controller;

use adamcameron\php8\tests\Fixtures\PostcodeLookup\TestConstants;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @testdox Tests of the PostcodeLookupController
 * @group slow
 */
class PostcodeLookupControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /** @testdox It retrieves addresses when the post code is valid */
    public function testRetrievesAddressesWhenPostCodeIsValid()
    {
        $this->client->request(
            "GET",
            sprintf("/postcode-lookup/%s", TestConstants::POSTCODE_OK)
        );
        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );

        $result = json_decode($response->getContent(), false);
        $this->assertTrue(property_exists($result, 'addresses'));
        $this->assertGreaterThanOrEqual(1, count($result->addresses));
    }

    /**
     * @testdox It returns an error status code and no addresses when the postcode is invalid
     * @dataProvider provideCasesForClientErrorTests
     */
    public function testReturnsErrorStatusCodeAndNoAddressesWhenPostCodeIsInvalid(
        string $postcode,
        int $statusCode
    ) {
        $this->client->request(
            "GET",
            sprintf("/postcode-lookup/%s", $postcode)
        );
        $response = $this->client->getResponse();

        $this->assertEquals($statusCode, $response->getStatusCode());

        $result = json_decode($response->getContent(), false);
        $this->assertTrue(property_exists($result, 'addresses'));
        $this->assertEmpty($result->addresses);
    }

    public static function provideCasesForClientErrorTests(): array
    {
        return [
            // getaddress is misbehaving on 400s at the moment
            /*"Invalid postcode returns BAD REQUEST" => [
                TestConstants::POSTCODE_INVALID,
                Response::HTTP_BAD_REQUEST
            ],*/
            "Bad API key returns UNAUTHORIZED" => [
                TestConstants::POSTCODE_UNAUTHORIZED,
                Response::HTTP_UNAUTHORIZED
            ],
            "Unpaid account returns FORBIDDEN" => [
                TestConstants::POSTCODE_FORBIDDEN,
                Response::HTTP_FORBIDDEN
            ],
            "Too many requests" => [
                TestConstants::POSTCODE_OVER_LIMIT,
                Response::HTTP_TOO_MANY_REQUESTS
            ],
            "An unhandled server error returns 500" => [
                TestConstants::POSTCODE_SERVER_ERROR,
                Response::HTTP_INTERNAL_SERVER_ERROR
            ]
        ];
    }
}
