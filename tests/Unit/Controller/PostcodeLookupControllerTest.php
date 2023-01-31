<?php

namespace adamcameron\php8\tests\Unit\Controller;

use adamcameron\php8\Adapter\AddressService;
use adamcameron\php8\tests\Fixtures\AddressService\TestConstants;
use Monolog\Handler\TestHandler;
use Monolog\Level;
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

    /**
     * @testdox It logs any issues we might need to deal with
     * @dataProvider provideCasesForLoggingTests
     */
    public function testLogging(
        int $statusCode,
        string $expectedMessage,
        Level $expectedLogLevel
    ) {
        $testHandler = new TestHandler();

        $this->configureControllerWithTestLoggingHandler(
            $statusCode,
            $expectedMessage,
            $testHandler
        );

        $this->client->request(
            "GET",
            sprintf("/postcode-lookup/%s", TestConstants::POSTCODE_OK)
        );

        $this->assertLogEntryIsCorrect(
            $testHandler,
            $expectedLogLevel,
            $statusCode,
            $expectedMessage
        );
    }

    public function provideCasesForLoggingTests() : array
    {
        return [
            "Unauthorized should log critical" => [
                Response::HTTP_UNAUTHORIZED,
                "Unauthorized",
                Level::Critical
            ],
            "Forbidden should log critical" => [
                Response::HTTP_FORBIDDEN,
                "Forbidden",
                Level::Critical
            ],
            "Too many requests should log critical" => [
                Response::HTTP_TOO_MANY_REQUESTS,
                "Too Many Requests",
                Level::Warning
            ],
            "Server error should log critical" => [
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "Internal Server Error",
                Level::Warning
            ]
        ];
    }

    private function configureControllerWithTestLoggingHandler(
        int $statusCode,
        string $expectedMessage,
        TestHandler $testHandler
    ): void {
        $container = self::getContainer();
        $mockedAddressServiceAdapter = $this
            ->getMockBuilder(AddressService\Adapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $mockedAddressServiceAdapter
            ->expects($this->once())
            ->method('get')
            ->willReturn(new AddressService\Response(
                [],
                $statusCode,
                $expectedMessage
            ));
        $container->set(AddressService\Adapter::class, $mockedAddressServiceAdapter);

        $logger = $container->get("monolog.logger.address_service");
        $logger->setHandlers([$testHandler]);
    }

    public function assertLogEntryIsCorrect(
        TestHandler $testHandler,
        Level $expectedLogLevel,
        int $statusCode,
        string $expectedMessage
    ): void {
        $logRecords = $testHandler->getRecords();
        $this->assertCount(1, $logRecords);
        $this->assertEquals($expectedLogLevel->getName(), $logRecords[0]["level_name"]);
        $this->assertEquals(
            AddressService\Adapter::ERROR_MESSAGES[$statusCode],
            $logRecords[0]["message"]
        );
        $this->assertEquals(
            [
                "postcode" => TestConstants::POSTCODE_OK,
                "message" => $expectedMessage
            ],
            $logRecords[0]["context"]
        );
    }
}
