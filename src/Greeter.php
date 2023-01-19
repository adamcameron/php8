<?php

namespace adamcameron\php8;

class Greeter
{
    public const FORMAL = 1;
    public const INFORMAL = 2;

    public static function greet(string $name, int $style = self::FORMAL): string
    {
        if ($style === self::FORMAL) {
            return "Hello, $name";
        }
        return "Hi, $name";
    }

}
