<?php

namespace App\Controller\Volt12;

use App\Service\Volt12\FeedbackFromMapService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class FeedbackFromMapController extends AbstractController
{
    public function __construct(
        private FeedbackFromMapService $feedbackFromMapService
    )
    {
    }

    #[Route('/feedback_from_map', name: 'volt12_feedback_from_map', methods: ['GET'])]
    public function feedback_from_map(): JsonResponse
    {
        return $this->json($this->feedbackFromMapService->getWithLimit());
    }
}
