<?php

namespace App\Repository;

use App\Provider\ProductCodeProvider;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ServiceGroupRepository extends EntityRepository
{
    public function findAllWithServiceCount(): array
    {
        // счётчик учитывает только видимые на витрине услуги: опубликованные и с картинкой
        return $this->createQueryBuilder('sg')
            ->select('sg.id, sg.name, sg.position, COUNT(s.id) as service_count')
            ->leftJoin('sg.services', 's', 'WITH', "s.is_published = true AND s.img_link IS NOT NULL AND s.img_link != ''")
            ->where('sg.product_code IN (:storefrontCodes)')
            ->setParameter('storefrontCodes', [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY])
            ->groupBy('sg.id')
            ->orderBy('sg.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
