<?php

namespace adamcameron\php8\Task;

class SimpleTask
{
    public function __invoke(): string
    {
        return "G'day world from an async call";
    }
}
