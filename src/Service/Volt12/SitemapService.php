<?php

namespace App\Service\Volt12;

use App\Provider\ProductCodeProvider;
use App\Repository\CatalogItemRepository;
use App\Repository\CatalogRepository;
use App\Repository\ServiceRepository;

class SitemapService
{
    public function __construct(
        private CatalogRepository $catalogRepository,
        private CatalogItemRepository $catalogItemRepository,
        private ServiceRepository $serviceRepository,
    ) {}

    /**
     * Все публичные URL сайта для sitemap.xml: категории, опубликованные товары
     * и услуги, кроме закрытых noindex.
     *
     * @return array<int, array{loc: string, lastmod?: string}>
     */
    public function getUrls(): array
    {
        $codes = [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY];
        $urls = [];

        $catalogs = $this->catalogRepository->createQueryBuilder('c')
            ->select('c.slug')
            ->where('c.product_code IN (:codes)')
            ->andWhere('c.seo.noindex = false')
            ->setParameter('codes', $codes)
            ->getQuery()
            ->getResult();

        foreach ($catalogs as $row) {
            if ($row['slug']) {
                $urls[] = ['loc' => '/catalog/' . $row['slug']];
            }
        }

        $items = $this->catalogItemRepository->createQueryBuilder('i')
            ->select('i.slug, i.updatedAt')
            ->where('i.is_published = true')
            ->andWhere('i.product_code IN (:codes)')
            ->andWhere('i.seo.noindex = false')
            ->setParameter('codes', $codes)
            ->getQuery()
            ->getResult();

        foreach ($items as $row) {
            if ($row['slug']) {
                $urls[] = $this->url('/product/' . $row['slug'], $row['updatedAt']);
            }
        }

        $services = $this->serviceRepository->createQueryBuilder('s')
            ->select('s.slug, s.updatedAt')
            ->where('s.is_published = true')
            ->andWhere('s.seo.noindex = false')
            ->getQuery()
            ->getResult();

        foreach ($services as $row) {
            if ($row['slug']) {
                $urls[] = $this->url('/services/' . $row['slug'], $row['updatedAt']);
            }
        }

        return $urls;
    }

    private function url(string $loc, ?\DateTimeInterface $lastmod): array
    {
        $url = ['loc' => $loc];
        if ($lastmod) {
            $url['lastmod'] = $lastmod->format(DATE_ATOM);
        }

        return $url;
    }
}
