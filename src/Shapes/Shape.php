<?php

namespace adamcameron\php8\Shapes;

abstract class Shape
{

    public function __construct(protected string $colour)
    {
    }

    public function getColour(): string
    {
        return $this->colour;
    }

    abstract public function getArea();
}
