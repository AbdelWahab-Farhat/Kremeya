<?php
namespace App\Services\DarbAssabil;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DarbAssabilClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $accountId;
    private string $apiVersion;

    public function __construct()
    {
        $this->baseUrl    = config('darb_assabil.base_url');
        $this->apiKey     = config('darb_assabil.api_key');
        $this->accountId  = config('darb_assabil.account_id');
        $this->apiVersion = config('darb_assabil.api_version');
    }

    /**
     * Get configured HTTP client with headers.
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => "apikey {$this->apiKey}",
                'X-ACCOUNT-ID'  => $this->accountId,
                'X-API-VERSION' => $this->apiVersion,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(30);
    }

    /**
     * Make a POST request.
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('post', $endpoint, $data);
    }

    /**
     * Make a GET request.
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('get', $endpoint, $query);
    }

    /**
     * Make a PATCH request.
     */
    public function patch(string $endpoint, array $data = []): array
    {
        return $this->request('patch', $endpoint, $data);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $endpoint): array
    {
        return $this->request('delete', $endpoint);
    }

    /**
     * Execute HTTP request and handle response.
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = match ($method) {
                'get'    => $this->client()->get($endpoint, $data),
                'post'   => $this->client()->post($endpoint, $data),
                'patch'  => $this->client()->patch($endpoint, $data),
                'delete' => $this->client()->delete($endpoint),
            };

            return $this->handleResponse($response, $method, $endpoint);
        } catch (\Exception $e) {
            Log::error('Darb Assabil API Error', [
                'method'   => $method,
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Handle API response.
     */
    protected function handleResponse(Response $response, string $method, string $endpoint): array
    {
        $body = $response->json();

        if ($response->successful() && ($body['status'] ?? false)) {
            return [
                'success'  => true,
                'data'     => $body['data'] ?? null,
                'messages' => $body['messages'] ?? [],
            ];
        }

        Log::warning('Darb Assabil API Request Failed', [
            'method'   => $method,
            'endpoint' => $endpoint,
            'status'   => $response->status(),
            'body'     => $body,
        ]);

        return [
            'success'     => false,
            'error'       => $body['messages'][0]['message'] ?? 'Unknown error',
            'data'        => $body['data'] ?? null,
            'status_code' => $response->status(),
        ];
    }

    /**
     * Check if the integration is properly configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey)
        && ! empty($this->accountId)
        && ! empty($this->baseUrl);
    }
}
