<?php

namespace App\EventListener;

use App\Entity\CatalogItem;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogItemResourceListener
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
        /** @var CatalogItem $item */
        $item = $event->getSubject();

        if (!$item instanceof CatalogItem) {
            return;
        }

        $errors = [];

        if ($error = $this->getSlugError($item)) {
            $errors[] = $error;
        }

        if ($error = $this->getPositionError($item)) {
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

    private function getSlugError(CatalogItem $item): ?string
    {
        $slug = $item->getSlug();
        if (!$slug) {
            return null;
        }

        $repository = $this->entityManager->getRepository(CatalogItem::class);
        $existing = $repository->findOneBy(['slug' => $slug]);

        if ($existing && $existing->getId() !== $item->getId()) {
            return sprintf(
                'Ошибка! Slug "%s" уже занят товаром "%s" (ID: %d).',
                $slug,
                $existing->getName(),
                $existing->getId()
            );
        }

        return null;
    }

    private function getPositionError(CatalogItem $item): ?string
    {
        $position = $item->getPosition();
        $catalog = $item->getCatalog();

        if ($position === null || $catalog === null) {
            return null;
        }

        $repository = $this->entityManager->getRepository(CatalogItem::class);

        $existing = $repository->findOneBy([
            'position' => $position,
            'catalog' => $catalog,
        ]);

        if ($existing && $existing->getId() !== $item->getId()) {
            return sprintf(
                'Ошибка! Позиция %d уже занята товаром "%s" в каталоге "%s".',
                $position,
                $existing->getName(),
                $existing->getCatalog()->getName()
            );
        }

        return null;
    }
}
