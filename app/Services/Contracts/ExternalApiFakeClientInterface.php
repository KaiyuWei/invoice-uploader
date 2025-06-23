<?php

namespace App\Services\Contracts;

/**
 * Fake External API Client Interface
 *
 * This interface and its implementations are fake clients used for simulating
 * external API calls. They provide a consistent interface for testing API
 * integrations without making actual HTTP requests.
 *
 * In production environments, you would typically:
 * - Install and use official client packages (e.g., Guzzle, HTTP Client)
 * - Use SDKs provided by the external API vendor
 * - Implement real HTTP clients with proper authentication, retry logic, etc.
 *
 */
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
