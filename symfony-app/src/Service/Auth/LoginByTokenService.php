<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Dto\Input\LoginByTokenInput;
use App\Dto\Output\AuthUserOutput;
use App\Exception\Auth\InvalidLoginByTokenCredentialsException;
use App\Query\Auth\FindAuthUserByTokenQueryInterface;

final class LoginByTokenService
{
    public function __construct(
        private readonly FindAuthUserByTokenQueryInterface $findAuthUserByTokenQuery,
    ) {
    }

    public function execute(LoginByTokenInput $input): AuthUserOutput
    {
        $authUser = $this->findAuthUserByTokenQuery->execute($input);

        if ($authUser === null) {
            throw new InvalidLoginByTokenCredentialsException('Invalid credentials.');
        }

        return $authUser;
    }
}
