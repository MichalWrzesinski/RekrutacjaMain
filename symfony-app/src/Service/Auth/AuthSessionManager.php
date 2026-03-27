<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Dto\Output\AuthUserOutput;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class AuthSessionManager
{
    public function login(AuthUserOutput $authenticatedUser, SessionInterface $session): void
    {
        $session->migrate(true);
        $session->set('user_id', $authenticatedUser->id);
        $session->set('username', $authenticatedUser->username);
    }

    public function logout(SessionInterface $session): void
    {
        $session->clear();
    }
}
