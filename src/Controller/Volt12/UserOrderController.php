<?php

namespace App\Controller\Volt12;

use App\Service\Volt12\UserOrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
            'success'  => true,
            'order_id' => $order->getId(),
        ], 201);
    }

    #[Route('/orders', name: 'volt12_order_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->attributes->get('_app_user');

        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = min(50, max(1, (int) $request->query->get('per_page', 10)));

        $result = $this->userOrderService->getOrdersPage($user, $page, $perPage);

        return $this->json(['success' => true, ...$result]);
    }

    #[Route('/orders/{id}', name: 'volt12_order_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->attributes->get('_app_user');

        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $order = $this->userOrderService->getOrderForUser($id, $user);

        if (!$order) {
            return $this->json(['success' => false, 'error' => 'Order not found'], 404);
        }

        return $this->json([
            'success' => true,
            'order'   => $this->userOrderService->serializeOrderFull($order),
        ]);
    }
}
