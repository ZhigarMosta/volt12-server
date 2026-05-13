<?php

namespace App\Service\Volt12;

use App\Repository\ServiceGroupRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ServiceService
{
    public function __construct(
        private ServiceRepository $serviceRepository,
        private ServiceGroupRepository $serviceGroupRepository
    ) {}

    public function list(?int $serviceGroupId, ?string $search, int $page = 1, int $limit = 10): Paginator
    {
        return $this->serviceRepository->list($serviceGroupId, $search, $page, $limit);
    }

    public function getGroups(): array
    {
        return $this->serviceGroupRepository->findAllWithServiceCount();
    }
}
