<?php

namespace App\Service;

use Symfony\Component\String\Slugger\AsciiSlugger;

class SlugGenerator
{
    private AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger('ru');
    }

    public function generate(string $text): string
    {
        return $this->slugger->slug($text)->lower()->toString();
    }

    /**
     * Генерирует slug из текста; при коллизии добавляет суффикс -2, -3 и т.д.
     *
     * @param callable(string): bool $isTaken возвращает true, если slug уже занят
     */
    public function generateUnique(string $text, callable $isTaken, string $fallback = 'item'): string
    {
        $base = $this->generate($text);
        if ($base === '') {
            $base = $fallback;
        }

        $slug = $base;
        for ($suffix = 2; $isTaken($slug); $suffix++) {
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }
}
