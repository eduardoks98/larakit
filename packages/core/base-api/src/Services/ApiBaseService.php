<?php

namespace Eduardoks98\BaseApi\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

abstract class ApiBaseService
{
    /**
     * Base URL da API externa.
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * Headers padrão para todas as requisições.
     *
     * @var array
     */
    protected array $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    /**
     * Timeout em segundos.
     *
     * @var int
     */
    protected int $timeout;

    /**
     * Número de tentativas em caso de falha.
     *
     * @var int
     */
    protected int $retryAttempts;

    /**
     * Delay entre tentativas (milliseconds).
     *
     * @var int
     */
    protected int $retryDelay;

    /**
     * Guzzle HTTP client.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->timeout = config('base-api.http_client.timeout', 30);
        $this->retryAttempts = config('base-api.http_client.retry_attempts', 3);
        $this->retryDelay = config('base-api.http_client.retry_delay', 1000);

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => $this->headers,
        ]);
    }

    /**
     * Executar requisição GET.
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     * @throws \Exception
     */
    protected function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, [
            'query' => $query,
        ]);
    }

    /**
     * Executar requisição POST.
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Executar requisição PUT.
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Executar requisição DELETE.
     *
     * @param string $endpoint
     * @return array
     * @throws \Exception
     */
    protected function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Executar requisição HTTP com retry logic.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $options
     * @return array
     * @throws \Exception
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $attempt = 0;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = $this->client->request($method, $endpoint, $options);

                return [
                    'status' => $response->getStatusCode(),
                    'data' => json_decode($response->getBody()->getContents(), true),
                    'headers' => $response->getHeaders(),
                ];
            } catch (GuzzleException $e) {
                $attempt++;

                if ($attempt >= $this->retryAttempts) {
                    Log::error('API Request Failed', [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);

                    throw new \Exception("API request failed after {$this->retryAttempts} attempts: " . $e->getMessage());
                }

                // Wait before retry
                usleep($this->retryDelay * 1000);
            }
        }

        throw new \Exception('Unexpected error in API request');
    }
}
