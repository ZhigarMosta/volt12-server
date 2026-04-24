<?php

namespace App\EventListener;

use App\Entity\Catalog;
use App\Entity\CatalogItem;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogResourceListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private CatalogImageListener $catalogImageListener,
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->uploadImage($event);
        $this->validate($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->uploadImage($event);
        $this->validate($event);
    }

    private function uploadImage(ResourceControllerEvent $event): void
    {
        $item = $event->getSubject();

        if (!$item instanceof Catalog) {
            return;
        }

        if ($item->getFile()) {
            $this->catalogImageListener->prePersist($item, null);
        }
    }

    private function validate(ResourceControllerEvent $event): void
    {
        /** @var Catalog $item */
        $item = $event->getSubject();

        if (!$item instanceof Catalog) {
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

    private function getSlugError(Catalog $item): ?string
    {
        $slug = $item->getSlug();
        if (!$slug) {
            return null;
        }

        $repository = $this->entityManager->getRepository(Catalog::class);
        $existing = $repository->findOneBy(['slug' => $slug]);

        if ($existing && $existing->getId() !== $item->getId()) {
            return sprintf(
                'Ошибка! Slug "%s" уже занят каталогом "%s"',
                $slug,
                $existing->getName(),
            );
        }

        return null;
    }

    private function getPositionError(Catalog $item): ?string
    {
        $position = $item->getPosition();

        if ($position === null) {
            return null;
        }

        $repository = $this->entityManager->getRepository(Catalog::class);

        $existing = $repository->findOneBy([
            'position' => $position,
        ]);

        if ($existing && $existing->getId() !== $item->getId()) {
            return sprintf(
                'Ошибка! Позиция %d уже занята каталогом "%s"',
                $position,
                $existing->getName(),
            );
        }

        return null;
    }
}
