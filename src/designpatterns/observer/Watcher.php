<?php

namespace adamcameron\php8\designpatterns\observer;

class Watcher implements Observer
{
    private ObservedModel $observer;

    public function __construct(private readonly string $name)
    {
    }

    public function setObserver(ObservedModel $observer): void
    {
        $this->observer = $observer;
    }

    public function notify(): void
    {
        printf("%s: %s<br>", $this->name, $this->observer->getUpdate()['someValue']);
    }
}
