<?php

namespace App\Repository;

use App\Entity\Catalog;
use App\Entity\FeedbackFromMap;
use Doctrine\ORM\AbstractQuery;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class FeedbackFromMapRepository extends EntityRepository
{
    public function findWithLimit(array $codes)
    {
        return $this->createQueryBuilder('f')
            ->select('f.user_name, f.map, f.star_count, f.message, f.feedback_link')
            ->where('f.product_code IN (:productCode)')
            ->setParameter('productCode', $codes)
            ->orderBy('f.position')
            ->setMaxResults(FeedbackFromMap::LIMIT_MAIN)
            ->getQuery()
            ->getResult();
    }
}
