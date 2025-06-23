<?php

namespace App\Services\Contracts;

interface ExternalApiFakeClientInterface
{
    /**
     * Send a POST request to an external API endpoint
     *
     * @param string $endpoint
     * @param array $payload
     * @return array Response data from the API
     */
    public function post(string $endpoint, array $payload): array;
}
