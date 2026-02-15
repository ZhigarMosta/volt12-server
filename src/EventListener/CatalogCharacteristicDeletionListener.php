<?php

namespace App\EventListener;

use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogItem;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class CatalogCharacteristicDeletionListener
{
    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router
    ) {}

    public function onPreDelete(ResourceControllerEvent $event): void
    {
        $catalog = $event->getSubject();

        if (!$catalog instanceof CatalogCharacteristic) {
            return;
        }

        $relations = [];

        if (!$catalog->getItemCharacteristics()->isEmpty()) {
            $relations[] = 'Характеристики (' . $catalog->getItemCharacteristics()->count() . ' шт.)';
        }

        if (!empty($relations)) {
            $message = sprintf(
                'Ошибка удаления! Характеристика каталога "%s" привязана к следующим таблицам: %s. Сначала удалите привязки (или сами сущности), затем попробуйте снова.',
                $catalog->getName(),
                implode(', ', $relations)
            );

            $event->stop(
                $message,
                ResourceControllerEvent::TYPE_ERROR
            );

            $redirectUrl = $this->router->generate('app_admin_catalog_characteristic_index');

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
