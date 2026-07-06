<?php

namespace App\Controller\Volt12;

use App\Exception\EmailLimitExceededException;
use App\Service\Volt12\BookingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[Route('/volt12')]
class BookingController extends AbstractController
{
    public function __construct(
        private BookingService $bookingService,
        private RateLimiterFactory $bookingSubmitLimiter
    )
    {
    }

    #[Route('/booking', name: 'volt12_booking', methods: ['POST'])]
    public function booking(Request $request): JsonResponse
    {
        $limiter = $this->bookingSubmitLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'errors' => ['Too many requests']], 429);
        }

        $data = $request->toArray();

        $type = trim($data['type'] ?? '');
        $userName = trim($data['user_name'] ?? '');
        $userPhone = trim($data['user_phone'] ?? '');
        $userEmail = trim($data['user_email'] ?? '');
        $message = trim($data['message'] ?? '');

        $errors = [];
        if ($type === '') {
            $errors[] = 'type is required';
        } elseif (!in_array($type, BookingService::ALL_TYPES, true)) {
            $errors[] = 'type is invalid';
        }
        if ($userName === '') {
            $errors[] = 'user_name is required';
        }
        if ($userPhone === '') {
            $errors[] = 'user_phone is required';
        }
        if ($userEmail === '') {
            $errors[] = 'user_email is required';
        } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'user_email is invalid';
        }

        if ($errors !== []) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        try {
            $this->bookingService->send($type, $userName, $userPhone, $userEmail, $message);
        } catch (EmailLimitExceededException $e) {
            return $this->json(['success' => false, 'errors' => [$e->getMessage()]], 429);
        }

        return $this->json(['success' => true]);
    }
}
