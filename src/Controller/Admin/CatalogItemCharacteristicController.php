<?php

namespace App\Controller\Admin;

use App\Entity\CatalogItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/crud')]
class CatalogItemCharacteristicController extends AbstractController
{
    #[Route('/catalog_characteristics_by_catalog/{id}', name: 'admin_crud_catalog_characteristics_by_catalog', methods: ['GET'])]
    public function catalogCharacteristicsByCatalog(CatalogItem $item): JsonResponse
    {
        $catalog = $item->getCatalog();
        if (!$catalog)
        {
            return $this->json([]);
        }
        $data = [];
        foreach ($catalog->getCharacteristics() as $char) {
            $data[] = [
                'id' => $char->getId(),
                'name' => $char->getName(),
            ];
        }
        return $this->json($data);
    }
}
