<?php

namespace adamcameron\php8\designpatterns\observer;

interface Observer
{
    public function notify(): void;
}
