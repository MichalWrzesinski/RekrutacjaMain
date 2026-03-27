<?php

declare(strict_types=1);

namespace App\Query\Auth;

use App\Dto\Input\LoginByTokenInput;
use App\Dto\Output\AuthUserOutput;
use Doctrine\DBAL\Connection;

final class FindAuthUserByTokenQuery implements FindAuthUserByTokenQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function execute(LoginByTokenInput $input): ?AuthUserOutput
    {
        $userData = $this->connection->fetchAssociative(
            <<<'SQL'
                SELECT u.id, u.username
                FROM users u
                INNER JOIN auth_tokens a ON a.user_id = u.id
                WHERE u.username = :username
                  AND a.token = :token
                LIMIT 1
            SQL,
            [
                'username' => $input->username,
                'token' => $input->token,
            ],
        );

        if ($userData === false) {
            return null;
        }

        return new AuthUserOutput(
            (int) $userData['id'],
            (string) $userData['username'],
        );
    }
}
