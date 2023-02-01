<?php

namespace adamcameron\php8\tests\Unit\Adapter\GetAddress;

use adamcameron\php8\Adapter\GetAddress\Adapter as GetAddressAdapter;
use adamcameron\php8\Adapter\GetAddress\Exception as GetAddressException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/** @testdox Tests of the GetAddress\Adapter */
class AdapterTest extends TestCase
{
    /** @testdox It throws an GetAddress\Exception if the getaddress.io call returns an unexpected status */
    public function testThrowsExceptionOnUnexpectedStatus()
    {
        $statusToReturn = Response::HTTP_NOT_IMPLEMENTED;

        $this->assertCorrectExceptionThrown(
            GetAddressException::class,
            "Unexpected status code returned: $statusToReturn"
        );

        $adapter = $this->getTestAdapter($statusToReturn, "CONTENT_NOT_TESTED");

        $adapter->get("POSTCODE_NOT_TESTED");
    }

    /** @testdox It throws an GetAddress\Exception if the body is not JSON */
    public function testThrowsExceptionOnBodyNotJson()
    {
        $this->assertCorrectExceptionThrown(
            GetAddressException::class,
            "json_decode returned [Syntax error]"
        );

        $adapter = $this->getTestAdapter(Response::HTTP_OK, "NOT_JSON");

        $adapter->get("NOT_TESTED");
    }

    /** @testdox Throws an GetAddress\Exception if the body is not an array */
    public function testThrowsExceptionOnResultNotArray()
    {
        $this->assertCorrectExceptionThrown(
            GetAddressException::class,
            "Response JSON schema is not valid"
        );

        $adapter = $this->getTestAdapter(Response::HTTP_OK, '"NOT_AN_ARRAY"');

        $adapter->get("NOT_TESTED");
    }

    /** @testdox it throws an GetAddress\Exception if there is no address data in the response json */
    public function testThrowsExceptionOnNoAddressData()
    {
        $this->assertCorrectExceptionThrown(
            GetAddressException::class,
            "Response JSON schema is not valid"
        );

        $adapter = $this->getTestAdapter(
            Response::HTTP_OK,
            '{"notAddressData": "NOT_ADDRESS_DATA"}'
        );

        $adapter->get("NOT_TESTED");
    }

    /** @testdox it throws an GetAddress\Exception if the addresses data is not an array */
    public function testThrowsExceptionOnAddressDataNotArray()
    {
        $this->assertCorrectExceptionThrown(
            GetAddressException::class,
            "Response JSON schema is not valid"
        );

        $adapter = $this->getTestAdapter(
            Response::HTTP_OK,
            '{"addresses": "NOT_AN_ARRAY"}'
        );

        $adapter->get("NOT_TESTED");
    }

    /** @testdox it throws an GetAddress\Exception if the addresses data is not an array of strings */
    public function testThrowsExceptionOnAddressDataNotArrayOfStrings()
    {
        $this->assertCorrectExceptionThrown(
            GetAddressException::class,
            "Response JSON schema is not valid"
        );

        $adapter = $this->getTestAdapter(
            Response::HTTP_OK,
            '{"addresses": ["address1", ["not a string"], "address"]}'
        );

        $adapter->get("NOT_TESTED");
    }

    /** @testdox returns empty addresses with status code on a non-200-OK response */
    public function testReturnsEmptyAddressesOnNon200Response()
    {
        $statusToReturn = Response::HTTP_BAD_REQUEST;

        $adapter = $this->getTestAdapter(
            $statusToReturn,
            '{"Message": "Bad Request: Invalid postcode."}'
        );

        $result = $adapter->get("NOT_TESTED");

        $this->assertEquals($statusToReturn, $result->getHttpStatus());
        $this->assertEquals([], $result->getAddresses());
    }

    /** @testdox it returns the message on a non-200 response */
    public function testReturnsMessageOnNon200Response()
    {
        $statusToReturn = Response::HTTP_BAD_REQUEST;
        $expectedMessage = "Bad Request: Invalid postcode.";

        $adapter = $this->getTestAdapter(
            $statusToReturn,
            sprintf('{"Message": "%s"}', $expectedMessage)
        );

        $result = $adapter->get("NOT_TESTED");

        $this->assertEquals($statusToReturn, $result->getHttpStatus());
        $this->assertEquals($expectedMessage, $result->getMessage());
    }

    /** @testdox it returns a standard message if the non-200 response doesn't include a valid one */
    public function testReturnsStandardMessageOnNon200Response()
    {
        $statusToReturn = Response::HTTP_BAD_REQUEST;

        $adapter = $this->getTestAdapter(
            $statusToReturn,
            '{"notMessage": "NOT_MESSAGE"}'
        );

        $result = $adapter->get("NOT_TESTED");

        $this->assertEquals($statusToReturn, $result->getHttpStatus());
        $this->assertEquals("No failure message returned from service", $result->getMessage());
    }

    /** @testdox it returns a Response object if the response is valid */
    public function testReturnsResponseObject()
    {
        $statusToReturn = Response::HTTP_OK;
        $expectedAddresses = [
            "TEST_ADDRESS_1",
            "TEST_ADDRESS_2"
        ];

        $adapter = $this->getTestAdapter(
            $statusToReturn,
            sprintf('{"addresses": %s}', json_encode($expectedAddresses))
        );

        $result = $adapter->get("NOT_TESTED");

        $this->assertEquals($statusToReturn, $result->getHttpStatus());
        $this->assertEquals($expectedAddresses, $result->getAddresses());
    }

    private function getTestAdapter(int $statusToReturn, string $content): GetAddressAdapter
    {
        $client = $this->getMockedClient($statusToReturn, $content);

        return new GetAddressAdapter("NOT_TESTED", $client);
    }

    private function getMockedClient(int $statusToReturn, string $content): MockObject
    {
        $response = $this->getMockedResponse($statusToReturn, $content);

        $client = $this
            ->getMockBuilder(HttpClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client
            ->expects($this->once())
            ->method("request")
            ->willReturn($response);

        return $client;
    }

    private function getMockedResponse(int $status, string $content): MockObject
    {
        $response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $response
            ->expects($this->atLeastOnce())
            ->method("getStatusCode")
            ->willReturn($status);
        $response
            ->expects($this->any())
            ->method("getContent")
            ->willReturn($content);

        return $response;
    }

    private function assertCorrectExceptionThrown(string $type, string $message): void
    {
        $this->expectException($type);
        $this->expectExceptionMessage($message);
    }
}
