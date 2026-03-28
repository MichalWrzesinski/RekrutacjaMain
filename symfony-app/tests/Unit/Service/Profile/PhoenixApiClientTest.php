<?php

declare(strict_types=1);

namespace Unit\Service\Profile;

use App\Exception\InvalidPhoenixApiTokenException;
use App\Service\Profile\PhoenixApiClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class PhoenixApiClientTest extends TestCase
{
    public function testFetchPhotosReturnsPhotosFromApiResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);

        $response
            ->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'photos' => [
                    ['id' => 1, 'photo_url' => 'https://example.com/photo-1.jpg'],
                    ['id' => 2, 'photo_url' => 'https://example.com/photo-2.jpg'],
                ],
            ]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->with(
                'GET',
                'http://phoenix:4000/api/photos',
                [
                    'headers' => [
                        'access-token' => 'valid-token',
                        'accept' => 'application/json',
                    ],
                ],
            )
            ->willReturn($response);

        $client = new PhoenixApiClient($httpClient, 'http://phoenix:4000');

        $result = $client->fetchPhotos('valid-token');

        self::assertCount(2, $result);
        self::assertSame('https://example.com/photo-1.jpg', $result[0]['photo_url']);
    }

    public function testFetchPhotosThrowsExceptionForUnauthorizedResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_UNAUTHORIZED);

        $response
            ->expects(self::never())
            ->method('toArray');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn($response);

        $client = new PhoenixApiClient($httpClient, 'http://phoenix:4000');

        $this->expectException(InvalidPhoenixApiTokenException::class);

        $client->fetchPhotos('invalid-token');
    }

    public function testFetchPhotosReturnsEmptyArrayWhenPhotosKeyIsMissing(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);

        $response
            ->expects(self::once())
            ->method('toArray')
            ->willReturn([]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn($response);

        $client = new PhoenixApiClient($httpClient, 'http://phoenix:4000');

        self::assertSame([], $client->fetchPhotos('valid-token'));
    }
}
