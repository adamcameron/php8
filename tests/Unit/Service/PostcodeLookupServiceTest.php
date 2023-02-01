<?php

namespace Service;

use adamcameron\php8\Adapter\GetAddress;
use adamcameron\php8\Kernel;
use adamcameron\php8\Service\PostcodeLookupService;
use adamcameron\php8\tests\Fixtures\GetAddress\TestConstants;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/** @testdox Tests of the PostcodeLookupService */
class PostcodeLookupServiceTest extends TestCase
{
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

        $container = $this->getContainer();

        $this->configureContainerWithTestLoggingHandler($container, $testHandler);
        $this->configureContainerWithMockedAdapter(
            $container,
            $statusCode,
            $expectedMessage
        );

        $service = $container->get(PostcodeLookupService::class);

        $service->lookup(TestConstants::POSTCODE_OK);

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

    /** @testdox It logs unhandled exceptions and returns an empty Response */
    public function testExceptionHandling()
    {
        $expectedMessage = "TEST_EXCEPTION_MESSAGE";

        $testHandler = new TestHandler();
        $container = $this->getContainer();
        $this->configureContainerWithTestLoggingHandler($container, $testHandler);
        $this->configureContainerWithErroringAdapter($container, $expectedMessage);

        $service = $container->get(PostcodeLookupService::class);

        $response = $service->lookup(TestConstants::POSTCODE_OK);

        $this->assertLogEntryIsCorrect(
            $testHandler,
            Level::Error,
            Response::HTTP_SERVICE_UNAVAILABLE,
            $expectedMessage
        );

        $this->assertEquals(
            new GetAddress\Response([], Response::HTTP_INTERNAL_SERVER_ERROR, $expectedMessage),
            $response
        );
    }

    private function getContainer() : ContainerInterface
    {
        $kernel = new Kernel("test", false);
        $kernel->boot();
        return $kernel->getContainer();
    }

    private function configureContainerWithTestLoggingHandler(
        ContainerInterface $container,
        TestHandler $testHandler
    ): void {
        $logger = $container->get("monolog.logger.address_service");
        $logger->setHandlers([$testHandler]);
    }

    private function configureContainerWithMockedAdapter(
        ContainerInterface $container,
        int $statusCode,
        string $expectedMessage,
    ) {
        $mockedAddressServiceAdapter = $this
            ->getMockBuilder(GetAddress\Adapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $mockedAddressServiceAdapter
            ->expects($this->once())
            ->method('get')
            ->willReturn(new GetAddress\Response(
                [],
                $statusCode,
                $expectedMessage
            ));
        $container->set(GetAddress\Adapter::class, $mockedAddressServiceAdapter);
    }

    private function configureContainerWithErroringAdapter(ContainerInterface $container, string $expectedMessage)
    {
        $mockedAddressServiceAdapter = $this
            ->getMockBuilder(GetAddress\Adapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $mockedAddressServiceAdapter
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new GetAddress\Exception($expectedMessage));
        $container->set(GetAddress\Adapter::class, $mockedAddressServiceAdapter);
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

        $expectedLogMessage = array_key_exists($statusCode, GetAddress\Adapter::ERROR_MESSAGES)
            ? GetAddress\Adapter::ERROR_MESSAGES[$statusCode]
            : $expectedMessage;

        $this->assertEquals(
            $expectedLogMessage,
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
