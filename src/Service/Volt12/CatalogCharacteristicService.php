<?php

namespace App\Service\Volt12;

use App\Provider\ProductCodeProvider;
use App\Repository\CatalogCharacteristicRepository;
use App\Repository\CatalogItemRepository;
use Psr\Log\LoggerInterface;

class CatalogCharacteristicService
{
    public function __construct(
        private CatalogCharacteristicRepository $catalogCharacteristicRepository,
        private LoggerInterface $logger
    ) {}

    public function getCatalogCharacteristics(int $catalogId): array
    {
        $without_group = [];
        $with_group = [];
        foreach ($this->catalogCharacteristicRepository->list($catalogId,[ProductCodeProvider::CODE_VOLT12,ProductCodeProvider::CODE_ANY]) as $catalogCharacteristic) {
            if(is_null($catalogCharacteristic['group_name'])) {
                $without_group[] = $catalogCharacteristic;
                continue;
            }
            $with_group[$catalogCharacteristic['group_name']] = $catalogCharacteristic['name'];
        }
        $this->logger->debug('damp',$with_group);
        return $this->catalogCharacteristicRepository->list($catalogId,[ProductCodeProvider::CODE_VOLT12,ProductCodeProvider::CODE_ANY]); // TODO обновить логику до новых таблиц
    }
}
