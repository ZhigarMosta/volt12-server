<?php

namespace App\EventListener;

use App\Entity\ServiceGroup;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class ServiceGroupDeletionListener
{
    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router
    ) {}

    public function onPreDelete(ResourceControllerEvent $event): void
    {
        $serviceGroup = $event->getSubject();

        if (!$serviceGroup instanceof ServiceGroup) {
            return;
        }

        $relations = [];

        if (!$serviceGroup->getServices()->isEmpty()) {
            $relations[] = 'Услуги (' . $serviceGroup->getServices()->count() . ' шт.)';
        }

        if (!empty($relations)) {
            $message = sprintf(
                'Ошибка удаления! Группа "%s" привязан к следующим таблицам: %s. Сначала удалите привязки (или сами сущности), затем попробуйте снова.',
                $serviceGroup->getName(),
                implode(', ', $relations)
            );

            $event->stop($message);

            $redirectUrl = $this->router->generate('app_admin_service_group_index');

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
