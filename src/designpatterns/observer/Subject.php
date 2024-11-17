<?php

namespace adamcameron\php8\designpatterns\observer;

interface Subject
{
    public function registerObserver(Observer $observer): void;

    public function removeObserver(Observer $observer): void;

    public function notifyObservers(): void;

    public function getUpdate(): array;
}
