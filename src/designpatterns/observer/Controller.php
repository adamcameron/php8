<?php

namespace adamcameron\php8\designpatterns\observer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController
{
    public function __construct(private readonly ObservedModelFactory $observedModelFactory)
    {
    }

    public function doGet(string $message): Response
    {
        $observedModel = $this->observedModelFactory->createInstance($message);
        $observedModel->doSomethingToSomeValue("adjusted value");

        return new Response("");
    }
}
