<?php

namespace adamcameron\php8\Service;

use adamcameron\php8\Adapter\GetAddress;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response as HttpStatusCode;

class PostcodeLookupService
{
    private const RESPONSES_TO_LOG = [
        HttpStatusCode::HTTP_UNAUTHORIZED => Level::Critical,
        HttpStatusCode::HTTP_FORBIDDEN => Level::Critical,
        HttpStatusCode::HTTP_TOO_MANY_REQUESTS => Level::Warning,
        HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR => Level::Warning
    ];

    public function __construct(
        private readonly GetAddress\Adapter $adapter,
        private readonly LoggerInterface $addressServiceLogger
    ) {
    }

    public function lookup(string $postcode): GetAddress\Response
    {
        try {
            return $this->getAddresses($postcode);
        } catch (GetAddress\Exception $e) {
            return $this->handleAdapterException($e, $postcode);
        }
    }

    private function getAddresses(string $postcode): GetAddress\Response
    {
        $response = $this->adapter->get($postcode);

        $this->logUnexpectedFailures($response, $postcode);

        return $response;
    }

    private function logUnexpectedFailures(
        GetAddress\Response $response,
        string $postcode
    ): void {
        $statusCode = $response->getHttpStatus();

        if (array_key_exists($statusCode, self::RESPONSES_TO_LOG)) {
            $this->addressServiceLogger->log(
                self::RESPONSES_TO_LOG[$statusCode],
                GetAddress\Adapter::ERROR_MESSAGES[$statusCode],
                ['postcode' => $postcode, 'message' => $response->getMessage()]
            );
        }
    }

    private function handleAdapterException(
        GetAddress\Exception|\Exception $e,
        string $postcode
    ): GetAddress\Response {
        $this->addressServiceLogger->log(
            Level::Error,
            $e->getMessage(),
            ['postcode' => $postcode, 'message' => $e->getMessage()]
        );

        return new GetAddress\Response(
            [],
            HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            $e->getMessage()
        );
    }
}
