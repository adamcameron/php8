<?php

namespace adamcameron\php8\tests\acceptance\Controller;

use adamcameron\php8\Adapter\AddressService\Adapter as AddressServiceAdapter;
use adamcameron\php8\Adapter\AddressService\UnsupportedResponseStatusException;
use adamcameron\php8\tests\fixtures\AddressService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/** @testdox Tests of the PostcodeLookupController */
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
            sprintf("/postcode-lookup/%s", AddressService::POSTCODE_OK)
        );
        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode()
        );

        $result = json_decode($response->getContent(), false);
        $this->assertObjectHasAttribute('addresses', $result);
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
        $this->assertObjectHasAttribute('addresses', $result);
        $this->assertEmpty($result->addresses);
    }

    public function provideCasesForClientErrorTests() : array
    {
        return [
            [AddressService::POSTCODE_INVALID, Response::HTTP_BAD_REQUEST],
            [AddressService::POSTCODE_UNAUTHORIZED, Response::HTTP_UNAUTHORIZED],
            [AddressService::POSTCODE_FORBIDDEN, Response::HTTP_FORBIDDEN],
            [AddressService::POSTCODE_OVER_LIMIT, Response::HTTP_TOO_MANY_REQUESTS],
            [AddressService::POSTCODE_SERVER_ERROR, Response::HTTP_INTERNAL_SERVER_ERROR]
        ];
    }

    /** @testdox it returns an error status and no addresses when there's been a server error */
    public function testReturnsErrorStatusCodeAndNoAddressesWhenServerError()
    {
        $container = self::getContainer();
        $mockedAddressServiceAdapter = $this
            ->getMockBuilder(AddressServiceAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $mockedAddressServiceAdapter
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new UnsupportedResponseStatusException("TEST_ERROR_MESSAGE"));
        $container->set(AddressServiceAdapter::class, $mockedAddressServiceAdapter);

        $this->client->request(
            "GET",
            sprintf("/postcode-lookup/%s", AddressService::POSTCODE_OK)
        );

        $response = $this->client->getResponse();

        $this->assertEquals(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $response->getStatusCode()
        );

        $result = json_decode($response->getContent(), false);
        $this->assertObjectHasAttribute('addresses', $result);
        $this->assertEmpty($result->addresses);
        $this->assertEquals("TEST_ERROR_MESSAGE", $result->message);
    }
}
