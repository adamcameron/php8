<?php

namespace adamcameron\php8\Shapes;

class Square extends Shape
{

    public function __construct(string $colour, protected float $side)
    {
        parent::__construct($colour);
    }

    public function getArea(): float
    {
        return $this->side ** 2;
    }
}
