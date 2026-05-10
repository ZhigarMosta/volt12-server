<?php

namespace App\Service\Volt12;

use App\Entity\Service;
use App\Repository\ServiceRepository;

class ServiceService
{
    public function __construct(
        private ServiceRepository $serviceRepository,
    ) {}

    public function getAll()
    {
        return $this->serviceRepository->findAll();
    }

    public function getServiceById(int $id): ?Service
    {
        return $this->serviceRepository->find($id);
    }
}
