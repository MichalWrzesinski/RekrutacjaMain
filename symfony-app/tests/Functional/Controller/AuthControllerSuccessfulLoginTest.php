<?php

declare(strict_types=1);

namespace Functional\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthControllerSuccessfulLoginTest extends WebTestCase
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

        $this->connection->insert('auth_tokens', [
            'token' => 'valid-alice-token',
            'user_id' => 1,
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function testLoginSucceedsForValidCredentials(): void
    {
        $this->client->request('GET', '/auth/alice/valid-alice-token');

        self::assertResponseRedirects('/');

        $session = $this->client->getRequest()->getSession();

        self::assertSame(1, $session->get('user_id'));
        self::assertSame('alice', $session->get('username'));
    }
}
