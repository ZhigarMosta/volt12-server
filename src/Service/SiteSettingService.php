<?php

namespace App\Service;

use App\Entity\SiteSetting;
use Doctrine\ORM\EntityManagerInterface;

class SiteSettingService
{
    public const DEFAULT_ROBOTS_TXT = <<<TXT
User-agent: *
Disallow: /cart
Disallow: /checkout
Disallow: /profile
Disallow: /orders
Disallow: /compare
Disallow: /favorites
TXT;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function get(string $code): ?string
    {
        $setting = $this->entityManager->getRepository(SiteSetting::class)->findOneBy(['code' => $code]);

        return $setting?->getValue();
    }

    public function getUpdatedAt(string $code): ?\DateTimeInterface
    {
        $setting = $this->entityManager->getRepository(SiteSetting::class)->findOneBy(['code' => $code]);

        return $setting?->getUpdatedAt();
    }

    public function set(string $code, ?string $value): void
    {
        $repository = $this->entityManager->getRepository(SiteSetting::class);

        $setting = $repository->findOneBy(['code' => $code]);
        if (!$setting) {
            $setting = new SiteSetting();
            $setting->setCode($code);
            $this->entityManager->persist($setting);
        }

        $setting->setValue($value);
        $this->entityManager->flush();
    }

    public function getRobotsTxt(): string
    {
        $value = $this->get(SiteSetting::ROBOTS_TXT);

        return ($value !== null && trim($value) !== '') ? $value : self::DEFAULT_ROBOTS_TXT;
    }
}
