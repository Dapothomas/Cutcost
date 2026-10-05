<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BachsClient
{
    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = [], ?string $idempotencyKey = null): array
    {
        return $this->send('POST', $path, $body, $idempotencyKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $path): array
    {
        return $this->send('GET', $path);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function delete(string $path, array $body = []): array
    {
        return $this->send('DELETE', $path, $body);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $body = [], ?string $idempotencyKey = null): array
    {
        $secret = config('bachs.secret');

        if (blank($secret)) {
            throw new RuntimeException('Bachs secret key is missing. Set BACHS_SECRET.');
        }

        $request = $this->request($secret);

        if ($idempotencyKey) {
            $request = $request->withHeaders(['Idempotency-Key' => $idempotencyKey]);
        }

        $response = $request->send($method, ltrim($path, '/'), $body === [] ? [] : ['json' => $body]);

        if ($response->failed()) {
            $detail = $response->json('detail') ?: $response->body();

            throw new RuntimeException('Bachs request failed: '.$detail);
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    private function request(string $secret): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('bachs.base_url'), '/'))
            ->withToken($secret)
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }
}
