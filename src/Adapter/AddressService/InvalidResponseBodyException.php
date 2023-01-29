<?php

namespace adamcameron\php8\Adapter\AddressService;

class InvalidResponseBodyException extends AddressServiceException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? "Response JSON schema is not valid");
    }
}
