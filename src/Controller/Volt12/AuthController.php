<?php

namespace App\Controller\Volt12;

use App\Entity\User;
use App\Service\Volt12\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/volt12/auth')]
class AuthController extends AbstractController
{
    private const COOKIE_NAME = 'auth_token';
    private const COOKIE_LIFETIME = 2592000;

    public function __construct(
        private UserService $userService
    ) {}

    #[Route('/register', name: 'volt12_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return $this->json(['success' => false, 'error' => 'Заполните обязательные поля'], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => 'Некорректный email'], 400);
        }

        try {
            $user = $this->userService->register(
                $data['name'],
                $data['email'],
                $data['password'],
                $data['phone'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }

        $response = $this->json(['success' => true, 'user' => $this->serializeUser($user)]);
        $response->headers->setCookie($this->createAuthCookie($user->getAuthToken()));

        return $response;
    }

    #[Route('/login', name: 'volt12_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['success' => false, 'error' => 'Заполните email и пароль'], 400);
        }

        $user = $this->userService->login($data['email'], $data['password']);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Неверный email или пароль'], 401);
        }

        $response = $this->json(['success' => true, 'user' => $this->serializeUser($user)]);
        $response->headers->setCookie($this->createAuthCookie($user->getAuthToken()));

        return $response;
    }

    #[Route('/logout', name: 'volt12_auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $user = $request->attributes->get('_app_user');
        if ($user) {
            $this->userService->logout($user);
        }

        $response = $this->json(['success' => true]);
        $response->headers->clearCookie(self::COOKIE_NAME, '/');

        return $response;
    }

    #[Route('/me', name: 'volt12_auth_me', methods: ['GET'])]
    public function me(Request $request): JsonResponse
    {
        $user = $request->attributes->get('_app_user');
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Не авторизован'], 401);
        }

        return $this->json(['success' => true, 'user' => $this->serializeUser($user)]);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
        ];
    }

    private function createAuthCookie(string $token): Cookie
    {
        return Cookie::create(
            self::COOKIE_NAME,
            $token,
            time() + self::COOKIE_LIFETIME,
            '/',
            null,
            true,
            true,
            false,
            Cookie::SAMESITE_LAX
        );
    }
}
