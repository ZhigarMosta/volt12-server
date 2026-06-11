<?php

namespace App\Controller\Volt12;

use App\Entity\User;
use App\Service\Volt12\UserOrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class UserOrderController extends AbstractController
{
    public function __construct(
        private UserOrderService $userOrderService
    ) {}

    #[Route('/order', name: 'volt12_order_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $user = $request->attributes->get('_app_user');

        $errors = $this->userOrderService->validate($data);
        if ($errors !== []) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        $order = $this->userOrderService->create($data, $user);

        return $this->json([
            'success' => true,
            'order_id' => $order->getId(),
        ], 201);
    }
}
