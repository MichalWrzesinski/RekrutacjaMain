<?php

declare(strict_types=1);

namespace App\Dto\Input;

final class PhotoFiltersInput
{
    public function __construct(
        public readonly string $location,
        public readonly string $camera,
        public readonly string $description,
        public readonly string $takenAt,
        public readonly string $username,
    ) {
    }
}
