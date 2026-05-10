<?php

namespace App\EventListener;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class ServiceResourceListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->validate($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->validate($event);
    }

    private function validate(ResourceControllerEvent $event): void
    {
        $service = $event->getSubject();
        if (!$service instanceof Service) {
            return;
        }

        $errors = [];

        if ($error = $this->getPositionError($service)) {
            $errors[] = $error;
        }

        if (empty($errors)) {
            return;
        }

        $message = implode(' ' . PHP_EOL, $errors);

        $event->stop($message);

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $referer = $request->headers->get('referer');
            $event->setResponse(new RedirectResponse($referer));
        }
    }

    private function getPositionError(Service $service): ?string
    {
        $position = $service->getPosition();
        $group = $service->getServiceGroup();

        if ($position === null || $group === null) {
            return null;
        }

        $repository = $this->entityManager->getRepository(Service::class);

        $existing = $repository->findOneBy([
            'position' => $position,
            'serviceGroup' => $group,
        ]);

        if ($existing && $existing->getId() !== $service->getId()) {
            return sprintf(
                'Ошибка! Позиция %d уже занята услугой "%s" в группе "%s".',
                $position,
                $existing->getName(),
                $existing->getServiceGroup()->getName()
            );
        }

        return null;
    }
}
