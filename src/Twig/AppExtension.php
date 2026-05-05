<?php

namespace App\Twig;

use App\Utils\Sort;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sort_modal', [Sort::class, 'getModal']),
        ];
    }
}
