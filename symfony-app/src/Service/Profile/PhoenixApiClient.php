<?php

declare(strict_types=1);

namespace App\Service\Profile;

use App\Exception\InvalidPhoenixApiTokenException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PhoenixApiClient implements PhoenixApiClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(string:PHOENIX_BASE_URL)%')]
        private readonly string $phoenixBaseUrl,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function fetchPhotos(string $token): array
    {
        $response = $this->httpClient->request('GET', rtrim($this->phoenixBaseUrl, '/') . '/api/photos', [
            'headers' => [
                'access-token' => $token,
                'accept' => 'application/json',
            ],
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode === Response::HTTP_UNAUTHORIZED) {
            throw new InvalidPhoenixApiTokenException('Invalid Phoenix API token.');
        }

        $data = $response->toArray();
        $photos = $data['photos'] ?? [];

        return is_array($photos) ? $photos : [];
    }
}
