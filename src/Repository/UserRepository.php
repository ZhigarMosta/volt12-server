<?php

namespace App\Repository;

use App\Entity\User;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByAuthToken(string $token): ?User
    {
        return $this->findOneBy(['auth_token' => $token]);
    }
}
