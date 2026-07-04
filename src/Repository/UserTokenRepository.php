<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserToken;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class UserTokenRepository extends EntityRepository
{
    public function findByTokenAndType(string $token, string $type): ?UserToken
    {
        return $this->findOneBy(['token' => $token, 'type' => $type]);
    }

    public function deleteByToken(string $token): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->execute();
    }

    public function deleteByUserAndType(User $user, string $type): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.user = :user')
            ->andWhere('t.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->getQuery()
            ->execute();
    }
}
