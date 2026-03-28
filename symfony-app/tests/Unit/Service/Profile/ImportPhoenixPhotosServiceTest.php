<?php

declare(strict_types=1);

namespace Unit\Service\Profile;

use App\Entity\Photo;
use App\Entity\User;
use App\Exception\InvalidPhoenixApiTokenException;
use App\Repository\PhotoRepository;
use App\Service\Profile\ImportPhoenixPhotosService;
use App\Service\Profile\PhoenixApiClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ImportPhoenixPhotosServiceTest extends TestCase
{
    public function testImportThrowsExceptionWhenUserDoesNotHaveToken(): void
    {
        $user = new User();
        $user->setUsername('alice');
        $user->setEmail('alice@example.com');
        $user->setPhoenixApiToken(null);

        $phoenixApiClient = $this->createMock(PhoenixApiClientInterface::class);
        $phoenixApiClient
            ->expects(self::never())
            ->method('fetchPhotos');

        $photoRepository = $this->createMock(PhotoRepository::class);
        $photoRepository
            ->expects(self::never())
            ->method('existsByUserAndImageUrl');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::never())
            ->method('persist');

        $entityManager
            ->expects(self::never())
            ->method('flush');

        $service = new ImportPhoenixPhotosService(
            $phoenixApiClient,
            $photoRepository,
            $entityManager,
        );

        $this->expectException(InvalidPhoenixApiTokenException::class);

        $service->import($user);
    }

    public function testImportPersistsOnlyNewPhotosWithNonEmptyImageUrl(): void
    {
        $user = new User();
        $user->setUsername('alice');
        $user->setEmail('alice@example.com');
        $user->setPhoenixApiToken('valid-token');

        $phoenixApiClient = $this->createMock(PhoenixApiClientInterface::class);
        $phoenixApiClient
            ->expects(self::once())
            ->method('fetchPhotos')
            ->with('valid-token')
            ->willReturn([
                ['id' => 1, 'photo_url' => 'https://example.com/photo-1.jpg'],
                ['id' => 2, 'photo_url' => '   '],
                ['id' => 3, 'photo_url' => 'https://example.com/photo-2.jpg'],
                ['id' => 4],
                'invalid-row',
            ]);

        $photoRepository = $this->createMock(PhotoRepository::class);
        $photoRepository
            ->expects(self::exactly(2))
            ->method('existsByUserAndImageUrl')
            ->willReturnCallback(
                static function (User $passedUser, string $imageUrl) use ($user): bool {
                    TestCase::assertSame($user, $passedUser);

                    return $imageUrl === 'https://example.com/photo-2.jpg';
                }
            );

        $persistedPhotos = [];

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->willReturnCallback(
                static function (object $entity) use (&$persistedPhotos): void {
                    TestCase::assertInstanceOf(Photo::class, $entity);
                    $persistedPhotos[] = $entity;
                }
            );

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new ImportPhoenixPhotosService(
            $phoenixApiClient,
            $photoRepository,
            $entityManager,
        );

        $importedCount = $service->import($user);

        self::assertSame(1, $importedCount);
        self::assertCount(1, $persistedPhotos);
        self::assertSame('https://example.com/photo-1.jpg', $persistedPhotos[0]->getImageUrl());
        self::assertSame($user, $persistedPhotos[0]->getUser());
    }

    public function testImportFlushesEvenWhenNoPhotosWereImported(): void
    {
        $user = new User();
        $user->setUsername('alice');
        $user->setEmail('alice@example.com');
        $user->setPhoenixApiToken('valid-token');

        $phoenixApiClient = $this->createMock(PhoenixApiClientInterface::class);
        $phoenixApiClient
            ->expects(self::once())
            ->method('fetchPhotos')
            ->with('valid-token')
            ->willReturn([]);

        $photoRepository = $this->createMock(PhotoRepository::class);
        $photoRepository
            ->expects(self::never())
            ->method('existsByUserAndImageUrl');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::never())
            ->method('persist');

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new ImportPhoenixPhotosService(
            $phoenixApiClient,
            $photoRepository,
            $entityManager,
        );

        self::assertSame(0, $service->import($user));
    }
}
