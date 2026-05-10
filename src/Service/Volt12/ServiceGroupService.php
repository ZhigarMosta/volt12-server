<?php

namespace App\Service\Volt12;

use App\Entity\ServiceGroup;
use App\Repository\ServiceGroupRepository;

class ServiceGroupService
{
    public function __construct(
        private ServiceGroupRepository $serviceGroupRepository,
    ) {}

    public function getAll()
    {
        return $this->serviceGroupRepository->findAll();
    }

    public function getServiceGroupById(int $id): ?ServiceGroup
    {
        return $this->serviceGroupRepository->find($id);
    }
}
