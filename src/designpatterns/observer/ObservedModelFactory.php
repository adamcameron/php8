<?php

namespace adamcameron\php8\designpatterns\observer;

class ObservedModelFactory
{
    public function __construct(private array $observers)
    {
    }

    public function createInstance(string $someValue): ObservedModel
    {
        $instance = new ObservedModel($someValue);
        foreach ($this->observers as $observer) {
            $instance->registerObserver($observer);
            $observer->setObserver($instance);
        }
        return $instance;
    }
}
