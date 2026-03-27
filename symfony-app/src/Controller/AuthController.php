<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Input\LoginByTokenInput;
use App\Exception\Auth\InvalidLoginByTokenCredentialsException;
use App\Service\Auth\AuthSessionManager;
use App\Service\Auth\LoginByTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AuthController extends AbstractController
{
    #[Route('/auth/{username}/{token}', name: 'auth_login', methods: ['GET'])]
    public function login(
        string $username,
        string $token,
        LoginByTokenService $loginByTokenService,
        AuthSessionManager $authSessionManager,
        Request $request,
    ): Response {
        try {
            $authenticatedUser = $loginByTokenService->execute(new LoginByTokenInput($username, $token));
        } catch (InvalidLoginByTokenCredentialsException) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $authSessionManager->login($authenticatedUser, $request->getSession());
        $this->addFlash('success', 'Welcome back, ' . $authenticatedUser->username . '!');

        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(
        Request $request,
        AuthSessionManager $authSessionManager,
    ): Response {
        $authSessionManager->logout($request->getSession());
        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
