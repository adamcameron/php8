<?php

namespace adamcameron\php8\Controller;

use adamcameron\php8\Adapter\AddressService;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatusCode;

class PostcodeLookupController extends AbstractController
{
    private AddressService\Adapter $addressServiceAdapter;
    private LoggerInterface $logger;

    private const RESPONSES_TO_LOG = [
        HttpStatusCode::HTTP_UNAUTHORIZED => Level::Critical,
        HttpStatusCode::HTTP_FORBIDDEN => Level::Critical,
        HttpStatusCode::HTTP_TOO_MANY_REQUESTS => Level::Warning,
        HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR => Level::Warning
    ];

    public function __construct(
        AddressService\Adapter $addressServiceAdapter,
        LoggerInterface $addressServiceLogger
    ) {
        $this->addressServiceAdapter = $addressServiceAdapter;
        $this->logger = $addressServiceLogger;
    }

    public function doGet(string $postcode) : JsonResponse
    {
        try {
            $response = $this->addressServiceAdapter->get($postcode);

            $this->logUnexpectedFailures($response, $postcode);

            return new JsonResponse(
                [
                    'postcode' => $postcode,
                    'addresses' => $response->getAddresses(),
                    'message' => $response->getMessage()
                ],
                $response->getHttpStatus()
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'postcode' => $postcode,
                    'addresses' => [],
                    'message' => $e->getMessage()
                ],
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function logUnexpectedFailures(
        AddressService\Response $response,
        string $postcode
    ): void {
        $statusCode = $response->getHttpStatus();

        if (array_key_exists($statusCode, self::RESPONSES_TO_LOG)) {
            $this->logger->log(
                self::RESPONSES_TO_LOG[$statusCode],
                AddressService\Adapter::ERROR_MESSAGES[$statusCode],
                ['postcode' => $postcode, 'message' => $response->getMessage()]
            );
        }
    }
}
