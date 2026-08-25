<?php

namespace App\Controller\Volt12;

use App\Entity\User;
use App\Service\Volt12\QuickOrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class QuickOrderController extends AbstractController
{
    public function __construct(
        private QuickOrderService $quickOrderService,
        private RateLimiterFactory $quickOrderSubmitLimiter
    )
    {
    }

    /**
     * Заказ «в один клик» со страницы товара. Публичная ручка: авторизация не
     * нужна, но если пользователь узнан по cookie — заказ привяжется к нему.
     */
    #[Route('/quick_order', name: 'volt12_quick_order', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $limiter = $this->quickOrderSubmitLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['success' => false, 'errors' => ['Too many requests']], 429);
        }

        $data = $request->toArray();

        $errors = $this->quickOrderService->validate($data);
        if ($errors !== []) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        $item = $this->quickOrderService->findOrderableItem((int) $data['catalog_item_id']);
        if (!$item) {
            return $this->json(['success' => false, 'errors' => ['catalog_item not found']], 404);
        }

        $order = $this->quickOrderService->create($data, $item, User::getAppUser($request));

        // Письма — после сохранения и не влияют на ответ: заказ уже в админке.
        $this->quickOrderService->notify($order);

        return $this->json([
            'success' => true,
            'order_id' => $order->getId(),
        ], 201);
    }
}
