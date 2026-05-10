<?php

namespace App\Controller\Admin;

use App\Service\Admin\UserCrudService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/crud/user')]
class UserCrudController extends AbstractController
{
    public function __construct(
        private UserCrudService $userCrudService
    ) {}

    #[Route('', name: 'admin_user_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(['success' => true, 'users' => $this->userCrudService->list()]);
    }

    #[Route('/{id}', name: 'admin_user_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $user = $this->userCrudService->get($id);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
        }
        return $this->json(['success' => true, 'user' => $user]);
    }

    #[Route('', name: 'admin_user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return $this->json(['success' => false, 'error' => 'Заполните обязательные поля'], 400);
        }

        try {
            $user = $this->userCrudService->create($data);
            return $this->json(['success' => true, 'user' => $user], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'admin_user_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $user = $this->userCrudService->update($id, $data);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
            }
            return $this->json(['success' => true, 'user' => $user]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        if ($this->userCrudService->delete($id)) {
            return $this->json(['success' => true]);
        }
        return $this->json(['success' => false, 'error' => 'Пользователь не найден'], 404);
    }
}
