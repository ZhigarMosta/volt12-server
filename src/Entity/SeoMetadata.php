<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class SeoMetadata
{
    #[ORM\Column(name: 'meta_title', type: 'string', length: 255, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(name: 'meta_description', type: 'text', nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(name: 'meta_keywords', type: 'string', length: 255, nullable: true)]
    private ?string $metaKeywords = null;

    #[ORM\Column(name: 'noindex', type: 'boolean', options: ['default' => false])]
    private bool $noindex = false;

    #[ORM\Column(name: 'nofollow', type: 'boolean', options: ['default' => false])]
    private bool $nofollow = false;

    #[ORM\Column(name: 'canonical_url', type: 'string', length: 512, nullable: true)]
    private ?string $canonicalUrl = null;

    public function getMetaTitle(): ?string { return $this->metaTitle; }
    public function setMetaTitle(?string $metaTitle): void { $this->metaTitle = $metaTitle; }

    public function getMetaDescription(): ?string { return $this->metaDescription; }
    public function setMetaDescription(?string $metaDescription): void { $this->metaDescription = $metaDescription; }

    public function getMetaKeywords(): ?string { return $this->metaKeywords; }
    public function setMetaKeywords(?string $metaKeywords): void { $this->metaKeywords = $metaKeywords; }

    public function getNoindex(): bool { return $this->noindex; }
    public function setNoindex(bool $noindex): void { $this->noindex = $noindex; }

    public function getNofollow(): bool { return $this->nofollow; }
    public function setNofollow(bool $nofollow): void { $this->nofollow = $nofollow; }

    public function getCanonicalUrl(): ?string { return $this->canonicalUrl; }
    public function setCanonicalUrl(?string $canonicalUrl): void { $this->canonicalUrl = $canonicalUrl; }

    public function toArray(): array
    {
        return [
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'meta_keywords' => $this->metaKeywords,
            'noindex' => $this->noindex,
            'nofollow' => $this->nofollow,
            'canonical_url' => $this->canonicalUrl,
        ];
    }
}
