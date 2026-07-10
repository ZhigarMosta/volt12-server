<?php

namespace App\Controller\Volt12;

use App\Service\SiteSettingService;
use App\Service\Volt12\SitemapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class SeoController extends AbstractController
{
    public function __construct(
        private SiteSettingService $siteSettingService,
        private SitemapService $sitemapService,
    ) {}

    /**
     * Содержимое robots.txt (редактируется в админке).
     * Без ".txt" в URL: php -S отдаёт пути с точкой как статику и не доходит до Symfony.
     */
    #[Route('/robots', name: 'volt12_robots_txt', methods: ['GET'])]
    public function robots(): Response
    {
        return new Response($this->siteSettingService->getRobotsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    #[Route('/sitemap', name: 'volt12_sitemap', methods: ['GET'])]
    public function sitemap(): JsonResponse
    {
        return $this->json(['items' => $this->sitemapService->getUrls()]);
    }
}
