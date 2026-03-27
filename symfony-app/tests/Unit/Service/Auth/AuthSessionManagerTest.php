<?php

declare(strict_types=1);

namespace Unit\Service\Auth;

use App\Dto\Output\AuthUserOutput;
use App\Service\Auth\AuthSessionManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class AuthSessionManagerTest extends TestCase
{
    public function testLoginStoresAuthenticatedUserInSession(): void
    {
        $authenticatedUser = new AuthUserOutput(1, 'alice');

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects(self::once())
            ->method('migrate')
            ->with(true);

        $session
            ->expects(self::exactly(2))
            ->method('set')
            ->willReturnCallback(
                static function (string $key, mixed $value): void {
                    static $calls = 0;
                    $calls++;

                    if ($calls === 1) {
                        TestCase::assertSame('user_id', $key);
                        TestCase::assertSame(1, $value);
                    }

                    if ($calls === 2) {
                        TestCase::assertSame('username', $key);
                        TestCase::assertSame('alice', $value);
                    }
                }
            );

        (new AuthSessionManager())->login($authenticatedUser, $session);
    }

    public function testLogoutClearsSession(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects(self::once())
            ->method('clear');

        (new AuthSessionManager())->logout($session);
    }
}
