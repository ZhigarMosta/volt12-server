<?php

namespace App\EventListener;

use App\Entity\Catalog;
use App\Entity\CatalogGroup;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class CatalogGroupDeletionListener
{
    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router
    ) {}

    public function onPreDelete(ResourceControllerEvent $event): void
    {
        $catalogGroup = $event->getSubject();

        if (!$catalogGroup instanceof CatalogGroup) {
            return;
        }

        $relations = [];

        if (!$catalogGroup->getCatalogCharacteristics()->isEmpty()) {
            $relations[] = 'Характеристики каталога (' . $catalogGroup->getCatalogCharacteristics()->count() . ' шт.)';
        }

        if (!empty($relations)) {
            $message = sprintf(
                'Ошибка удаления! Группа "%s" привязан к следующим таблицам: %s. Сначала удалите привязки (или сами сущности), затем попробуйте снова.',
                $catalogGroup->getName(),
                implode(', ', $relations)
            );

            $event->stop($message);

            $redirectUrl = $this->router->generate('app_admin_catalog_index');

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
