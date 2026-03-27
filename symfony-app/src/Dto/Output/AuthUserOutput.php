<?php

declare(strict_types=1);

namespace App\Dto\Output;

final class AuthUserOutput
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
    ) {
    }
}
