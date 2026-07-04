<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\Volt12\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/client_users')]
class UserPasswordController extends AbstractController
{
    public function __construct(
        private UserService $userService,
    ) {}

    #[Route('/{id}/change-password', name: 'app_admin_user_change_password', methods: ['POST'])]
    public function changePassword(User $user, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $password = $data['password'] ?? '';

        if ($password === '') {
            return $this->json(['success' => false, 'error' => 'Введите пароль'], 400);
        }

        try {
            $this->userService->changePassword($user, $password);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }

        return $this->json(['success' => true]);
    }
}
