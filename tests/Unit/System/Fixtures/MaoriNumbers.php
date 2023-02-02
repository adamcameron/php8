<?php

namespace adamcameron\php8\tests\Unit\System\Fixtures;

enum MaoriNumbers : int {
    case TAHI = 1;
    case RUA = 2;
    case TORU = 3;
    case WHĀ = 4;
    case RIMA = 5;
    case ONO = 6;
    case WHITU = 7;
    case WARU = 8;
    case IWA = 9;
    case TEKAU = 10;

    use MaoriNumbersConstsTrait;

    private CONST EN = ["one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten"];

    public function toEnglish() : string
    {
        return self::EN[$this->value - 1];
    }

    public static function asEnglish(int $i) : string
    {
        return self::EN[$i - 1];
    }

    public function getParity() : string
    {
        return match($this) {
            self::TAHI, self::TORU, self::RIMA, self::WHITU, self::IWA => "odd",
            self::RUA, self::WHĀ, self::ONO, self::WARU, self::TEKAU => "even"
        };
    }

    public function __invoke() : string
    {
        return $this->toEnglish();
    }
}
