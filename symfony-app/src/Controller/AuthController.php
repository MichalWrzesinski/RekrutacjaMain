<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/auth/{username}/{token}', name: 'auth_login', methods: ['GET'])]
    public function login(string $username, string $token, Connection $connection, Request $request): Response
    {
        $tokenData = $connection->fetchAssociative(
            'SELECT * FROM auth_tokens WHERE token = :token',
            ['token' => $token],
        );

        if (!$tokenData) {
            return new Response('Unauthorized', 401);
        }

        $userData = $connection->fetchAssociative(
            'SELECT * FROM users WHERE username = :username AND id = :id',
            [
                'username' => $username,
                'id' => (int) $tokenData['user_id'],
            ],
        );

        if (!$userData) {
            return new Response('Unauthorized', 401);
        }

        $session = $request->getSession();
        $session->migrate(true);
        $session->set('user_id', $userData['id']);
        $session->set('username', $username);

        $this->addFlash('success', 'Welcome back, ' . $username . '!');

        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear();

        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
