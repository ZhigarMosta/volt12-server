<?php

namespace App\Controller\Volt12;

use App\Entity\User;
use App\Repository\CatalogItemRepository;
use App\Service\Volt12\CompareService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/volt12/compare')]
class CompareController extends AbstractController
{
    public function __construct(
        private CompareService $compareService,
        private CatalogItemRepository $catalogItemRepository
    ) {}

    private function getAppUser(Request $request): ?User
    {
        return $request->attributes->get('_app_user');
    }

    #[Route('', name: 'volt12_compare_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        return $this->json(['success' => true, 'items' => $this->compareService->list($user)]);
    }

    #[Route('', name: 'volt12_compare_add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
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

        $item = $this->compareService->add($user, $catalogItem);

        return $this->json(['success' => true, 'item' => $item]);
    }

    #[Route('/{id}', name: 'volt12_compare_remove', methods: ['DELETE'])]
    public function remove(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        if (!$this->compareService->remove($user, $id)) {
            return $this->json(['success' => false, 'error' => 'Товар не найден в сравнении'], 404);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/clear', name: 'volt12_compare_clear', methods: ['DELETE'])]
    public function clear(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        $this->compareService->clear($user);
        return $this->json(['success' => true]);
    }
}
