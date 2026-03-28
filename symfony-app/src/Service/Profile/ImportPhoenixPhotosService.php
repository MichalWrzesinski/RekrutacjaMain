<?php

declare(strict_types=1);

namespace App\Service\Profile;

use App\Entity\Photo;
use App\Entity\User;
use App\Exception\InvalidPhoenixApiTokenException;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ImportPhoenixPhotosService
{
    public function __construct(
        private readonly PhoenixApiClientInterface $phoenixApiClient,
        private readonly PhotoRepository $photoRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function import(User $user): int
    {
        $token = $user->getPhoenixApiToken();
        if ($token === null || trim($token) === '') {
            throw new InvalidPhoenixApiTokenException('Invalid Phoenix API token.');
        }

        $photos = $this->phoenixApiClient->fetchPhotos($token);
        $importedCount = 0;

        foreach ($photos as $photoData) {
            if (!is_array($photoData)) {
                continue;
            }

            $imageUrl = trim((string) ($photoData['photo_url'] ?? ''));
            if ($imageUrl === '') {
                continue;
            }

            if ($this->photoRepository->existsByUserAndImageUrl($user, $imageUrl)) {
                continue;
            }

            $photo = new Photo();
            $photo->setUser($user);
            $photo->setImageUrl($imageUrl);

            $this->entityManager->persist($photo);
            ++$importedCount;
        }

        $this->entityManager->flush();

        return $importedCount;
    }
}
