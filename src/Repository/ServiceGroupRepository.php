<?php

namespace App\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ServiceGroupRepository extends EntityRepository
{
    public function findAllWithServiceCount(): array
    {
        return $this->createQueryBuilder('sg')
            ->select('sg.id, sg.name, sg.position, sg.img_link, COUNT(s.id) as service_count')
            ->leftJoin('sg.services', 's')
            ->groupBy('sg.id')
            ->orderBy('sg.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
