<?php

namespace App\Controller\Volt12;

use App\Service\Volt12\FeedbackService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class FeedbackController extends AbstractController
{
    public function __construct(
        private FeedbackService $feedbackService
    )
    {
    }

    #[Route('/feedback', name: 'volt12_feedback', methods: ['POST'])]
    public function feedback(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $type = trim($data['type'] ?? '');
        $userName = trim($data['user_name'] ?? '');
        $userPhone = trim($data['user_phone'] ?? '');
        $description = trim($data['description'] ?? '');
        $userEmail = trim($data['user_email'] ?? '');

        $errors = [];
        if ($type === '') {
            $errors[] = 'type is required';
        }
        if ($userName === '') {
            $errors[] = 'user_name is required';
        }
        if ($userPhone === '') {
            $errors[] = 'user_phone is required';
        }
        if ($description === '') {
            $errors[] = 'description is required';
        }

        if ($errors !== []) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        $this->feedbackService->send($type, $userName, $userPhone, $userEmail, $description);

        return $this->json(['success' => true]);
    }
}
