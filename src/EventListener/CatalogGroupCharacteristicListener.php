<?php

namespace App\EventListener;

use App\Entity\CatalogGroupCharacteristic;
use App\Entity\CatalogItemCharacteristic;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogGroupCharacteristicListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack
    ) {}

    public function onPreCreate(ResourceControllerEvent $event): void
    {
        $this->checkDouble($event);
    }

    public function onPreUpdate(ResourceControllerEvent $event): void
    {
        $this->checkDouble($event);
    }

    private function checkDouble(ResourceControllerEvent $event): void
    {
        $item = $event->getSubject();

        if (!$item instanceof CatalogGroupCharacteristic) {
            return;
        }

        $catalogGroup = $item->getCatalogGroup();
        $catalogCharacteristic = $item->getCatalogCharacteristic();

        if ($catalogGroup === null || $catalogCharacteristic === null) {
            return;
        }

        $repository = $this->entityManager->getRepository(CatalogGroupCharacteristic::class);

        $existing = $repository->findOneBy([
            'catalogGroup' => $catalogGroup,
            'catalogCharacteristic' => $catalogCharacteristic,
        ]);

        if ($existing && $existing->getId() !== $item->getId()) {

            $message = sprintf(
                'Ошибка! У Группы "%s" уже есть характеристика "%s"',
                $existing->getCatalogGroup()->getName(),
                $existing->getCatalogCharacteristic()->getName()
            );

            $event->stop($message);

            $event->stop($message);
            $redirectUrl = $this->requestStack->getCurrentRequest()->headers->get('referer');
            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
