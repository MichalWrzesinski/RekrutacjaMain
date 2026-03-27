<?php

declare(strict_types=1);

namespace Unit\Service\Auth;

use App\Dto\Input\LoginByTokenInput;
use App\Dto\Output\AuthUserOutput;
use App\Exception\Auth\InvalidLoginByTokenCredentialsException;
use App\Query\Auth\FindAuthUserByTokenQueryInterface;
use App\Service\Auth\LoginByTokenService;
use PHPUnit\Framework\TestCase;

final class LoginByTokenServiceTest extends TestCase
{
    public function testReturnsAuthenticatedUserWhenCredentialsAreValid(): void
    {
        $input = new LoginByTokenInput('alice', 'valid-token');
        $expectedUser = new AuthUserOutput(1, 'alice');

        $query = $this->createMock(FindAuthUserByTokenQueryInterface::class);
        $query
            ->expects(self::once())
            ->method('execute')
            ->with($input)
            ->willReturn($expectedUser);

        self::assertSame($expectedUser, (new LoginByTokenService($query))->execute($input));
    }

    public function testThrowsExceptionWhenCredentialsAreInvalid(): void
    {
        $input = new LoginByTokenInput('alice', 'invalid-token');

        $query = $this->createMock(FindAuthUserByTokenQueryInterface::class);
        $query
            ->expects(self::once())
            ->method('execute')
            ->with($input)
            ->willReturn(null);

        $this->expectException(InvalidLoginByTokenCredentialsException::class);

        (new LoginByTokenService($query))->execute($input);
    }
}
