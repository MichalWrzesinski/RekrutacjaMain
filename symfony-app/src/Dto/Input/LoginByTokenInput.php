<?php

declare(strict_types=1);

namespace App\Dto\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class LoginByTokenInput
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $username,
        #[Assert\NotBlank]
        public readonly string $token,
    ) {
    }
}
