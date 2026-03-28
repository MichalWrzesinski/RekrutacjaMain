<?php

declare(strict_types=1);

namespace App\Resolver\Auth;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionUserResolver
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function resolve(SessionInterface $session): ?User
    {
        $userId = $session->get('user_id');
        if (!is_int($userId) && !is_string($userId)) {
            return null;
        }

        $user = $this->userRepository->findById((int) $userId);
        if (!$user instanceof User) {
            $session->clear();
            return null;
        }

        return $user;
    }
}
