<?php

namespace adamcameron\php8\designpatterns\observer;

class ObservedModel implements Subject
{

    private array $observers = [];

    public function __construct(private string $someValue)
    {
    }

    public function doSomethingToSomeValue(string $someOtherValue): void
    {
        $this->someValue = sprintf("%s: %s", $someOtherValue, $this->someValue);
        $this->notifyObservers();
    }

    public function registerObserver(Observer $observer): void
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(Observer $observer): void
    {
        $key = array_search($observer, $this->observers);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }

    public function notifyObservers(): void
    {
        foreach ($this->observers as $observer) {
            $observer->notify();
        }
    }

    public function getUpdate(): array
    {
        return [
            'someValue' => $this->someValue
        ];
    }
}
