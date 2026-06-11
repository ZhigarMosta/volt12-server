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

}
