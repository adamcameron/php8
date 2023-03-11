<?php

namespace adamcameron\php8\Shapes;

class Circle extends Shape
{

    public function __construct(string $colour, protected float $radius)
    {
        parent::__construct($colour);
    }

    public function getArea(): float
    {
        return pi() * $this->radius ** 2;
    }
}
