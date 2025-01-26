<?php

declare(strict_types=1);

namespace App\Twig;

use App\Repository\DatumRepository;
use Twig\Extension\RuntimeExtensionInterface;

class DatumRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly DatumRepository $datumRepository)
    {
    }

    public function getListValuesFromDatumLabelAndType(?string $label, string $type): array
    {
        return $this->datumRepository->findAllUniqueListValues($label, $type);
    }
}
