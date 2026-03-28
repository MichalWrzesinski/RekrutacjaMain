<?php

declare(strict_types=1);

namespace App\Dto\Input;

final class SavePhoenixTokenInput
{
    public function __construct(
        public readonly string $token,
    ) {
    }
}
