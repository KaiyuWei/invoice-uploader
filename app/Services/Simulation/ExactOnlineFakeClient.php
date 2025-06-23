<?php

namespace App\Services\Simulation;

use App\Services\Contracts\ExternalApiFakeClientInterface;

class ExactOnlineFakeClient implements ExternalApiFakeClientInterface
{
    /**
     * The success rate of the request to ExactOnline api
     */
    const SUCCESS_RATE = 0.7;
    
    public function post(string $endpoint, array $payload): array
    {
        // simulating different response of the request to ExactOnline api
        if (mt_rand(0, 100) / 100 < self::SUCCESS_RATE) {
            return [
                'status' => 'success',
                'message' => 'Invoice sent to ExactOnline',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to send invoice to ExactOnline',
        ];
    }
}