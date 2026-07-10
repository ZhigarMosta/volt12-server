<?php

namespace App\Controller\Volt12;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\Service\Volt12\ServiceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12/services')]
class ServiceController extends AbstractController
{
    public function __construct(
        private ServiceService $serviceService,
        private ServiceRepository $serviceRepository
    ) {}

    #[Route('/footer', name: 'volt12_services_footer', methods: ['GET'])]
    public function footer(): JsonResponse
    {
        $services = $this->serviceRepository->findFooterServices();

        return $this->json([
            'items' => array_map(fn(Service $s) => [
                'slug' => $s->getSlug(),
                'name' => $s->getName(),
            ], $services),
        ]);
    }

    #[Route('/{slug}', name: 'volt12_service_by_slug', methods: ['GET'])]
    public function bySlug(string $slug): JsonResponse
    {
        $service = $this->serviceRepository->findBySlug($slug);
        if (!$service) {
            return $this->json(['success' => false, 'error' => 'Услуга не найдена'], 404);
        }

        $related = $this->serviceRepository->findRelatedByName(
            $service->getName(),
            $service->getId(),
            4
        );

        return $this->json([
            'success' => true,
            'item' => [
                'id' => $service->getId(),
                'name' => $service->getName(),
                'slug' => $service->getSlug(),
                'description' => $service->getDescription(),
                'short_description' => $service->getShortDescription(),
                'position' => $service->getPosition(),
                'is_published' => $service->getIsPublished(),
                'img_link' => $service->getImgLink(),
                'img_alt' => $service->getImgAlt(),
                'img_title' => $service->getImgTitle(),
                'service_group_id' => $service->getServiceGroup()?->getId(),
                'seo' => $service->getSeo()->toArray(),
            ],
            'related' => array_map(fn(Service $s) => [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'slug' => $s->getSlug(),
                'img_link' => $s->getImgLink(),
                'img_alt' => $s->getImgAlt(),
                'img_title' => $s->getImgTitle(),
                'short_description' => $s->getShortDescription(),
            ], $related),
        ]);
    }

    #[Route('', name: 'volt12_services', methods: ['POST'])]
    public function services(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $serviceGroupId = $data['service_group_id'] ?? null;
        $search = $data['search'] ?? '';
        $page = (int)($data['page'] ?? 1);
        $limit = (int)($data['limit'] ?? 10);

        $paginator = $this->serviceService->list($serviceGroupId, $search, $page, $limit);

        $totalItems = count($paginator);
        $totalPages = (int)ceil($totalItems / $limit);

        $items = [];
        foreach ($paginator as $service) {
            $items[] = [
                'id' => $service->getId(),
                'name' => $service->getName(),
                'slug' => $service->getSlug(),
                'description' => $service->getDescription(),
                'short_description' => $service->getShortDescription(),
                'position' => $service->getPosition(),
                'img_link' => $service->getImgLink(),
                'img_alt' => $service->getImgAlt(),
                'img_title' => $service->getImgTitle(),
                'service_group_id' => $service->getServiceGroup()?->getId(),
            ];
        }

        $groups = $this->serviceService->getGroups();

        return $this->json([
            'items' => $items,
            'groups' => $groups,
            'meta' => [
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'limit' => $limit,
            ],
        ]);
    }
}
