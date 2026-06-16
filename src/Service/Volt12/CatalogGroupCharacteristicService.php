<?php

namespace App\Service\Volt12;

use App\Provider\ProductCodeProvider;
use App\Entity\CatalogCharacteristic;
use App\Repository\CatalogGroupCharacteristicRepository;
use Psr\Log\LoggerInterface;

class CatalogGroupCharacteristicService
{
  public function __construct(
    private CatalogCharacteristicRepository $catalogCharacteristicRepository,
    private LoggerInterface                 $logger
    )
    {
    }

    public function getCatalogGroupCharacteristicsByCatalogGroupId(int $catalogGroupId): array
    {
    
    }
}
