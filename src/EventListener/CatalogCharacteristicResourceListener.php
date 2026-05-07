<?php

namespace App\EventListener;

use App\Entity\CatalogCharacteristic;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogCharacteristicResourceListener
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
        $characteristic = $event->getSubject();
        if (!$characteristic instanceof CatalogCharacteristic) {
            return;
        }

        if ($characteristic->getPosition() !== null) {
            return;
        }

        $repository = $this->entityManager->getRepository(CatalogCharacteristic::class);
        $maxPosition = null;

        if ($characteristic->getCatalogGroup()) {
            $maxPosition = $repository->createQueryBuilder('cc')
                ->select('MAX(cc.position)')
                ->where('cc.catalogGroup = :group')
                ->setParameter('group', $characteristic->getCatalogGroup())
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $catalog = $characteristic->getCatalog();
            if ($catalog) {
                $maxPosition = $repository->createQueryBuilder('cc')
                    ->select('MAX(cc.position)')
                    ->where('cc.catalog = :catalog')
                    ->andWhere('cc.catalogGroup IS NULL')
                    ->setParameter('catalog', $catalog)
                    ->getQuery()
                    ->getSingleScalarResult();
            }
        }

        $characteristic->setPosition((int)$maxPosition + 1);
    }

    private function validate(ResourceControllerEvent $event): void
    {
        $characteristic = $event->getSubject();
        if (!$characteristic instanceof CatalogCharacteristic) {
            return;
        }

        $errors = [];

        if ($error = $this->getPositionError($characteristic)) {
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

    private function getPositionError(CatalogCharacteristic $characteristic): ?string
    {
        $position = $characteristic->getPosition();
        if ($position === null) {
            return null;
        }

        $repository = $this->entityManager->getRepository(CatalogCharacteristic::class);

        if ($characteristic->getCatalogGroup()) {
            $existing = $repository->findOneBy([
                'position' => $position,
                'catalogGroup' => $characteristic->getCatalogGroup(),
            ]);

            if ($existing && $existing->getId() !== $characteristic->getId()) {
                return sprintf(
                    'Ошибка! Позиция %d уже занята характеристикой "%s" в группе "%s".',
                    $position,
                    $existing->getName(),
                    $existing->getCatalogGroup()->getName()
                );
            }
        } else {
            $qb = $repository->createQueryBuilder('cc')
                ->where('cc.catalog = :catalog')
                ->andWhere('cc.catalogGroup IS NULL')
                ->andWhere('cc.position = :position')
                ->andWhere('cc.id != :id')
                ->setParameter('catalog', $characteristic->getCatalog())
                ->setParameter('position', $position)
                ->setParameter('id', $characteristic->getId() ?? 0)
                ->getQuery()
                ->getResult();

            if (!empty($qb)) {
                return sprintf(
                    'Ошибка! Позиция %d уже занята другой характеристикой без группы в этом каталоге.',
                    $position
                );
            }
        }

        return null;
    }
}
