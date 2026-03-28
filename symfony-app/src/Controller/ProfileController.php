<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Input\SavePhoenixTokenInput;
use App\Exception\InvalidPhoenixApiTokenException;
use App\Resolver\Auth\SessionUserResolver;
use App\Service\Profile\ImportPhoenixPhotosService;
use App\Service\Profile\SavePhoenixTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly SessionUserResolver $sessionUserResolver,
        private readonly SavePhoenixTokenService $savePhoenixTokenService,
        private readonly ImportPhoenixPhotosService $importPhoenixPhotosService,
    ) {
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function profile(Request $request): Response
    {
        $user = $this->sessionUserResolver->resolve($request->getSession());
        if ($user === null) {
            return $this->redirectToRoute('home');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/phoenix-token', name: 'profile_phoenix_token_save', methods: ['POST'])]
    public function savePhoenixToken(Request $request): Response
    {
        $user = $this->sessionUserResolver->resolve($request->getSession());
        if ($user === null) {
            return $this->redirectToRoute('home');
        }

        if (!$this->isCsrfTokenValid('save_phoenix_api_token', (string) $request->request->get('_token', ''))) {
            $this->addFlash('error', 'Invalid form token.');
            return $this->redirectToRoute('profile');
        }

        $this->savePhoenixTokenService->save(
            $user,
            new SavePhoenixTokenInput((string) $request->request->get('phoenix_api_token', ''))
        );

        $this->addFlash('success', 'Phoenix API token has been saved.');

        return $this->redirectToRoute('profile');
    }

    #[Route('/profile/phoenix-import', name: 'profile_phoenix_photos_import', methods: ['POST'])]
    public function importPhoenixPhotos(Request $request): Response
    {
        $user = $this->sessionUserResolver->resolve($request->getSession());
        if ($user === null) {
            return $this->redirectToRoute('home');
        }

        if (!$this->isCsrfTokenValid('import_phoenix_photos', (string) $request->request->get('_token', ''))) {
            $this->addFlash('error', 'Invalid form token.');
            return $this->redirectToRoute('profile');
        }

        try {
            $importedCount = $this->importPhoenixPhotosService->import($user);
        } catch (InvalidPhoenixApiTokenException) {
            $this->addFlash('error', 'Invalid Phoenix API token.');
            return $this->redirectToRoute('profile');
        }

        $this->addFlash('success', sprintf('%d photos have been imported.', $importedCount));

        return $this->redirectToRoute('profile');
    }
}
