<?php

namespace adamcameron\php8\Controller;

use adamcameron\php8\Service\PostcodeLookupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostcodeLookupController extends AbstractController
{

    public function __construct(private readonly PostcodeLookupService $postcodeLookupService)
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
