<?php

namespace adamcameron\php8\PostcodeLookup;

interface AdapterInterface
{
    public function get(string $postCode): AdapterResponse;
}
