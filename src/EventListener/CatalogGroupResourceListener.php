<?php

namespace App\EventListener;

use App\Entity\CatalogGroup;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogGroupResourceListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->assignPosition($event);
        $this->validate($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->assignPosition($event);
        $this->validate($event);
    }

    private function assignPosition(ResourceControllerEvent $event): void
    {
        $group = $event->getSubject();
        if (!$group instanceof CatalogGroup) {
            return;
        }

        if ($group->getPosition() !== null) {
            return;
        }

        $catalog = $group->getCatalog();
        if (!$catalog) {
            return;
        }

        $repository = $this->entityManager->getRepository(CatalogGroup::class);
        $maxPosition = $repository->createQueryBuilder('cg')
            ->select('MAX(cg.position)')
            ->where('cg.catalog = :catalog')
            ->setParameter('catalog', $catalog)
            ->getQuery()
            ->getSingleScalarResult();

        $group->setPosition((int)$maxPosition + 1);
    }

    private function validate(ResourceControllerEvent $event): void
    {
        $group = $event->getSubject();
        if (!$group instanceof CatalogGroup) {
            return;
        }

        $errors = [];

        if ($error = $this->getPositionError($group)) {
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

    private function getPositionError(CatalogGroup $group): ?string
    {
        $position = $group->getPosition();
        $catalog = $group->getCatalog();

        if ($position === null || $catalog === null) {
            return null;
        }

        $repository = $this->entityManager->getRepository(CatalogGroup::class);

        $existing = $repository->findOneBy([
            'position' => $position,
            'catalog' => $catalog,
        ]);

        if ($existing && $existing->getId() !== $group->getId()) {
            return sprintf(
                'Ошибка! Позиция %d уже занята группой "%s".',
                $position,
                $existing->getName()
            );
        }

        return null;
    }
}
