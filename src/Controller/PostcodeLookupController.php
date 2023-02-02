<?php

namespace adamcameron\php8\Controller;

use adamcameron\php8\Service\PostcodeLookup\ServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostcodeLookupController extends AbstractController
{

    public function __construct(private readonly ServiceInterface $postcodeLookupService)
    {
    }

    public function doGet(string $postcode) : JsonResponse
    {
        $response = $this->postcodeLookupService->lookup($postcode);
        return new JsonResponse(
            [
                'postcode' => $postcode,
                'addresses' => $response->getAddresses(),
                'message' => $response->getMessage()
            ],
            $response->getHttpStatus()
        );
    }
}
