<?php

namespace App\Service\Profile;

interface PhoenixApiClientInterface
{
    public function fetchPhotos(string $token): array;
}