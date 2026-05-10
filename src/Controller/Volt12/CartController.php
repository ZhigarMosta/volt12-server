<?php

namespace App\Controller\Volt12;

use App\Entity\CatalogItem;
use App\Entity\User;
use App\Repository\CatalogItemRepository;
use App\Service\Volt12\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/volt12/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private CatalogItemRepository $catalogItemRepository
    ) {}

    private function getAppUser(Request $request): ?User
    {
        return $request->attributes->get('_app_user');
    }

    #[Route('', name: 'volt12_cart_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        return $this->json(['success' => true, 'items' => $this->cartService->list($user)]);
    }

    #[Route('', name: 'volt12_cart_add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $user = $this->getAppUser($request);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['catalog_item_id'])) {
            return $this->json(['success' => false, 'error' => 'catalog_item_id обязателен'], 400);
        }

        $catalogItem = $this->catalogItemRepository->find($data['catalog_item_id']);
        if (!$catalogItem) {
            return $this->json(['success' => false, 'error' => 'Товар не найден'], 404);
        }

        $count = max(1, (int)($data['count'] ?? 1));
        $item = $this->cartService->add($user, $catalogItem, $count);

        return $this->json(['success' => true, 'item' => $item]);
    }

    #[Route('/{id}', name: 'volt12_cart_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getAppUser($request);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['count'])) {
            return $this->json(['success' => false, 'error' => 'count обязателен'], 400);
        }

        $result = $this->cartService->updateCount($user, $id, (int)$data['count']);

        if ($result === null) {
            return $this->json(['success' => false, 'error' => 'Товар не найден в корзине'], 404);
        }

        return $this->json(['success' => true, 'item' => $result]);
    }

    #[Route('/{id}', name: 'volt12_cart_remove', methods: ['DELETE'])]
    public function remove(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        if (!$this->cartService->remove($user, $id)) {
            return $this->json(['success' => false, 'error' => 'Товар не найден в корзине'], 404);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/clear', name: 'volt12_cart_clear', methods: ['DELETE'])]
    public function clear(Request $request): JsonResponse
    {
        $user = $this->getAppUser($request);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $this->cartService->clear($user);
        return $this->json(['success' => true]);
    }
}
