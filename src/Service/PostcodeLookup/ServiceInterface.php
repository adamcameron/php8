<?php

namespace adamcameron\php8\Service\PostcodeLookup;

use adamcameron\php8\Adapter\PostcodeLookupService\AdapterResponse;

interface ServiceInterface
{
    public function lookup(string $postcode): AdapterResponse;
}
