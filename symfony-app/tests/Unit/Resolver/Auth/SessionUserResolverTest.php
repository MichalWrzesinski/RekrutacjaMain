<?php

declare(strict_types=1);

namespace Unit\Resolver\Auth;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Resolver\Auth\SessionUserResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionUserResolverTest extends TestCase
{
    public function testReturnsNullWhenSessionDoesNotContainUserId(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->expects(self::never())
            ->method('findById');

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects(self::once())
            ->method('get')
            ->with('user_id')
            ->willReturn(null);

        $resolver = new SessionUserResolver($userRepository);

        self::assertNull($resolver->resolve($session));
    }

    public function testReturnsNullAndClearsSessionWhenUserDoesNotExist(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->expects(self::once())
            ->method('findById')
            ->with(1)
            ->willReturn(null);

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects(self::once())
            ->method('get')
            ->with('user_id')
            ->willReturn(1);

        $session
            ->expects(self::once())
            ->method('clear');

        $resolver = new SessionUserResolver($userRepository);

        self::assertNull($resolver->resolve($session));
    }

    public function testReturnsUserWhenSessionContainsValidUserId(): void
    {
        $user = new User();
        $user->setUsername('alice');
        $user->setEmail('alice@example.com');

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->expects(self::once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects(self::once())
            ->method('get')
            ->with('user_id')
            ->willReturn(1);

        $session
            ->expects(self::never())
            ->method('clear');

        $resolver = new SessionUserResolver($userRepository);

        self::assertSame($user, $resolver->resolve($session));
    }
}
