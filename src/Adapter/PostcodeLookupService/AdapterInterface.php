<?php

namespace adamcameron\php8\Adapter\PostcodeLookupService;

interface AdapterInterface
{
    public function get(string $postCode): AdapterResponse;
}
