<?php

namespace adamcameron\php8\tests\Functional\Controller;

use adamcameron\php8\Adapter\AddressService;
use adamcameron\php8\tests\Fixtures\AddressService\TestConstants;
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

    /** @testdox it returns an error status and no addresses when there's been a server error */
    public function testReturnsErrorStatusCodeAndNoAddressesWhenServerError()
    {
        $container = self::getContainer();
        $mockedAddressServiceAdapter = $this
            ->getMockBuilder(AddressService\Adapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $mockedAddressServiceAdapter
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new AddressService\Exception("TEST_ERROR_MESSAGE"));
        $container->set(AddressService\Adapter::class, $mockedAddressServiceAdapter);

        $this->client->request(
            "GET",
            sprintf("/postcode-lookup/%s", TestConstants::POSTCODE_OK)
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
