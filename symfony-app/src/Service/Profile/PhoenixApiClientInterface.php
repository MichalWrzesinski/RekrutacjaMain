<?php

namespace App\Service\Profile;

interface PhoenixApiClientInterface
{
    /** @return array<int, array<string, mixed>> */
    public function fetchPhotos(string $token): array;
}