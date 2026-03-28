<?php

declare(strict_types=1);

namespace App\Service\Profile;

use App\Dto\Input\SavePhoenixTokenInput;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class SavePhoenixTokenService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(User $user, SavePhoenixTokenInput $savePhoenixTokenInput): void
    {
        $normalizedToken = trim($savePhoenixTokenInput->token);
        $user->setPhoenixApiToken($normalizedToken !== '' ? $normalizedToken : null);
        $this->entityManager->flush();
    }
}
