<?php

namespace adamcameron\php8\PostcodeLookup;

interface ServiceInterface
{
    public function lookup(string $postcode): AdapterResponse;
}
