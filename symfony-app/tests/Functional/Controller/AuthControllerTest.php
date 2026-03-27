<?php

declare(strict_types=1);

namespace Functional\Controller;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->connection = static::getContainer()->get(Connection::class);

        $this->connection->executeStatement('DELETE FROM auth_tokens');
        $this->connection->executeStatement('DELETE FROM users');

        $this->connection->insert('users', [
            'id' => 1,
            'username' => 'alice',
            'email' => 'alice@example.com',
            'name' => null,
            'last_name' => null,
            'age' => null,
            'bio' => null,
        ]);

        $this->connection->insert('users', [
            'id' => 2,
            'username' => 'bob',
            'email' => 'bob@example.com',
            'name' => null,
            'last_name' => null,
            'age' => null,
            'bio' => null,
        ]);

        $this->connection->insert('auth_tokens', [
            'token' => 'valid-bob-token',
            'user_id' => 2,
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    #[DataProvider('invalidLoginAttemptsProvider')]
    public function testLoginFailsForInvalidAttempts(
        string $username,
        string $token
    ): void {
        $this->client->request(
            'GET',
            sprintf('/auth/%s/%s', rawurlencode($username), rawurlencode($token))
        );

        self::assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
        self::assertSame('Unauthorized', $this->client->getResponse()->getContent());
    }

    public static function invalidLoginAttemptsProvider(): iterable
    {
        yield 'Token belongs to another user' => [
            'username' => 'alice',
            'token' => 'valid-bob-token',
        ];

        yield 'SQL Injection in username' => [
            'username' => "' OR 1=1 -- ",
            'token' => 'valid-bob-token',
        ];

        yield 'SQL Injection in token' => [
            'username' => 'alice',
            'token' => "' OR 1=1 -- ",
        ];
    }
}
