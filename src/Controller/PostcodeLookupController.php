<?php

namespace adamcameron\php8\Controller;

use adamcameron\php8\Adapter\AddressService\Adapter as AddressServiceAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PostcodeLookupController extends AbstractController
{
    private AddressServiceAdapter $addressServiceAdapter;

    public function __construct(AddressServiceAdapter $addressServiceAdapter)
    {
        $this->addressServiceAdapter = $addressServiceAdapter;
    }

    public function doGet(string $postcode)
    {
        try {
            $response = $this->addressServiceAdapter->get($postcode);

            return new JsonResponse(
                [
                    'postcode' => $postcode,
                    'addresses' => $response->getAddresses(),
                    'message' => $response->getMessage()
                ],
                $response->getHttpStatus()
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'postcode' => $postcode,
                    'addresses' => [],
                    'message' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
