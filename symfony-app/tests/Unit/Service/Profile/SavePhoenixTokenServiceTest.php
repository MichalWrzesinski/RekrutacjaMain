<?php

declare(strict_types=1);

namespace Unit\Service\Profile;

use App\Dto\Input\SavePhoenixTokenInput;
use App\Entity\User;
use App\Service\Profile\SavePhoenixTokenService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class SavePhoenixTokenServiceTest extends TestCase
{
    public function testSavesNormalizedToken(): void
    {
        $user = new User();
        $user->setUsername('alice');
        $user->setEmail('alice@example.com');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new SavePhoenixTokenService($entityManager);

        $service->save($user, new SavePhoenixTokenInput('  token-123  '));

        self::assertSame('token-123', $user->getPhoenixApiToken());
    }

    public function testSavesNullWhenTokenIsBlank(): void
    {
        $user = new User();
        $user->setUsername('alice');
        $user->setEmail('alice@example.com');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new SavePhoenixTokenService($entityManager);

        $service->save($user, new SavePhoenixTokenInput('   '));

        self::assertNull($user->getPhoenixApiToken());
    }
}
