<?php

namespace App\EventListener;

use App\Service\Volt12\UserService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class UserFromCookieListener
{
    public function __construct(
        private UserService $userService
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $token = $request->cookies->get('auth_token');

        if (!$token) {
            $authHeader = $request->headers->get('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
            }
        }

        if (!$token) {
            return;
        }

        $user = $this->userService->getUserByAuthToken($token);
        if (!$user) {
            return;
        }

        $request->attributes->set('_app_user', $user);
    }
}
