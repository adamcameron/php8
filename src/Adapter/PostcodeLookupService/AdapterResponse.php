<?php

namespace adamcameron\php8\Adapter\PostcodeLookupService;

readonly class AdapterResponse
{
    public function __construct(
        private array $addresses,
        private int $httpStatus,
        private string $message = ""
    ) {
    }

    public function getAddresses() : array
    {
        return $this->addresses;
    }

    public function getHttpStatus() : int
    {
        return $this->httpStatus;
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}
