<?php

namespace adamcameron\php8\Adapter\AddressService;

use adamcameron\php8\Adapter\AddressService\Response as AddressServiceResponse;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Adapter
{

    private const SUPPORTED_SERVICE_RESPONSES = [
        HttpFoundationResponse::HTTP_OK,
        HttpFoundationResponse::HTTP_BAD_REQUEST,
        HttpFoundationResponse::HTTP_UNAUTHORIZED,
        HttpFoundationResponse::HTTP_FORBIDDEN,
        HttpFoundationResponse::HTTP_TOO_MANY_REQUESTS,
        HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
    ];

    private const SERVICE_URL_TEMPLATE = "https://api.getAddress.io/find/{postcode}?api-key={api-key}";

    private string $apiKey;
    private HttpClientInterface $client;

    public function __construct(string $apiKey, HttpClientInterface $client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    public function get(string $postCode) : AddressServiceResponse
    {
        $response = $this->makeRequest($postCode);
        $lookupResult = $this->extractValidLookupResult($response);

        return $this->handleValidatedResponse($response, $lookupResult);
    }

    private function makeRequest(string $postCode): ResponseInterface
    {
        $url = strtr(
            self::SERVICE_URL_TEMPLATE,
            ["{postcode}" => $postCode, "{api-key}" => $this->apiKey]
        );

        return $this->client->request("GET", $url);
    }

    private function extractValidLookupResult(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        if (!in_array($statusCode, self::SUPPORTED_SERVICE_RESPONSES)) {
            throw new UnsupportedResponseStatusException("Unexpected status code returned: $statusCode");
        }

        $body = $response->getContent(false);
        $lookupResult = json_decode($body, JSON_OBJECT_AS_ARRAY);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new InvalidResponseBodyException(
                sprintf("json_decode returned [%s]", json_last_error_msg())
            );
        }

        if (!is_array($lookupResult)) {
            throw new InvalidResponseBodyException();
        }
        return $lookupResult;
    }

    private function handleValidatedResponse(
        ResponseInterface $response,
        array $lookupResult
    ): AddressServiceResponse {
        $statusCode = $response->getStatusCode();

        if ($statusCode == HttpFoundationResponse::HTTP_OK) {
            return $this->handleSuccessResponse($lookupResult);
        }
        return $this->handleFailureResponse($lookupResult, $statusCode);
    }

    private function handleSuccessResponse(array $lookupResult): AddressServiceResponse
    {
        if (
            !array_key_exists("addresses", $lookupResult)
            || !is_array($lookupResult["addresses"])
            || count(array_filter($lookupResult["addresses"], fn($address) => !is_string($address)))
        ) {
            throw new InvalidResponseBodyException();
        }

        return new AddressServiceResponse(
            $lookupResult["addresses"],
            HttpFoundationResponse::HTTP_OK
        );
    }

    private function handleFailureResponse(
        array $lookupResult,
        int $statusCode
    ): AddressServiceResponse {
        if (array_key_exists("Message", $lookupResult) && is_string($lookupResult["Message"])) {
            return new AddressServiceResponse([], $statusCode, $lookupResult["Message"]);
        }
        return new AddressServiceResponse([], $statusCode, "No failure message returned from service");
    }
}
