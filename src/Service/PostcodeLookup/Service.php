<?php

namespace adamcameron\php8\Service\PostcodeLookup;

use adamcameron\php8\Adapter\GetAddress;
use adamcameron\php8\Adapter\PostcodeLookupService\AdapterException;
use adamcameron\php8\Adapter\PostcodeLookupService\AdapterInterface;
use adamcameron\php8\Adapter\PostcodeLookupService\AdapterResponse;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response as HttpStatusCode;

class Service implements ServiceInterface
{
    private const RESPONSES_TO_LOG = [
        HttpStatusCode::HTTP_UNAUTHORIZED => Level::Critical,
        HttpStatusCode::HTTP_FORBIDDEN => Level::Critical,
        HttpStatusCode::HTTP_TOO_MANY_REQUESTS => Level::Warning,
        HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR => Level::Warning
    ];

    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly LoggerInterface  $addressServiceLogger
    ) {
    }

    public function lookup(string $postcode): AdapterResponse
    {
        try {
            return $this->getAddresses($postcode);
        } catch (AdapterException $e) {
            return $this->handleAdapterException($e, $postcode);
        }
    }

    private function getAddresses(string $postcode): AdapterResponse
    {
        $response = $this->adapter->get($postcode);

        $this->logUnexpectedFailures($response, $postcode);

        return $response;
    }

    private function logUnexpectedFailures(
        AdapterResponse $response,
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
        AdapterException $e,
        string $postcode
    ): AdapterResponse {
        $this->addressServiceLogger->log(
            Level::Error,
            $e->getMessage(),
            ['postcode' => $postcode, 'message' => $e->getMessage()]
        );

        return new AdapterResponse(
            [],
            HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            $e->getMessage()
        );
    }
}
