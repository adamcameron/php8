<?php

namespace adamcameron\php8\Adapter\AddressService;

class Response
{
    private array $addresses;
    private int $httpStatus;
    private string $message;

    public function __construct(array $addresses, int $httpStatus, string $message = "")
    {
        $this->addresses = $addresses;
        $this->httpStatus = $httpStatus;
        $this->message = $message;
    }

    public function getAddresses()
    {
        return $this->addresses;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
