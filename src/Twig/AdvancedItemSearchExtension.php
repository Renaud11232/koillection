<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdvancedItemSearchExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getListValuesFromDatumLabelAndType', [AdvancedItemSearchRuntime::class, 'getListValuesFromDatumLabelAndType']),
            new TwigFunction('getUserCollections', [AdvancedItemSearchRuntime::class, 'getUserCollections']),
        ];
    }
}
