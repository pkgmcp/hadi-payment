<?php

declare(strict_types=1);

namespace Hadi\Payment;

use Hadi\Payment\Contracts\PaymentGatewayInterface;
use Hadi\Payment\Exceptions\InvalidConfigurationException;

/**
 * Base abstract class shared by every payment gateway in the Hadi Payment package.
 *
 * Each gateway provides a consistent four-step API:
 * initialize -> process -> verify -> refund.
 */
abstract class PaymentGateway implements PaymentGatewayInterface
{
    protected array $config;

    /**
     * PaymentGateway constructor.
     *
     * @param array $config Gateway specific configuration.
     * @throws InvalidConfigurationException If configuration is invalid.
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->validateConfig($this->config);
    }

    /**
     * Get the default configuration for the gateway.
     *
     * @return array
     */
    abstract protected function getDefaultConfig(): array;

    /**
     * Validate the gateway configuration.
     *
     * @param array $config
     * @throws InvalidConfigurationException If configuration is invalid.
     */
    abstract protected function validateConfig(array $config): void;

    /**
     * Initialize a payment transaction.
     *
     * @param array $data Transaction data (amount, orderId, callbackUrl, ...)
     * @return array
     */
    abstract public function initialize(array $data): array;

    /**
     * Process / execute a payment (typically a callback or direct execution).
     *
     * @param array $data Transaction data.
     * @return array
     */
    abstract public function process(array $data): array;

    /**
     * Verify a payment transaction.
     *
     * @param array $data Transaction data (paymentId / transactionId).
     * @return array
     */
    abstract public function verify(array $data): array;

    /**
     * Refund a payment transaction.
     *
     * @param array $data Refund data (paymentId, transactionId, amount, reason).
     * @return array
     */
    abstract public function refund(array $data): array;

    /**
     * Human friendly gateway name.
     */
    public function getGatewayName(): string
    {
        $class = static::class;
        $name = basename(str_replace('\\', '/', $class));

        return preg_replace('/Gateway$/', '', $name) ?: $class;
    }

    /**
     * Check if the gateway is properly configured (non-empty required keys).
     */
    public function isConfigured(): bool
    {
        try {
            $this->validateConfig($this->config);

            return true;
        } catch (InvalidConfigurationException) {
            return false;
        }
    }

    /**
     * Make an HTTP request using Guzzle when available, otherwise fall back to
     * a secure stream-context based client.
     *
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string $url The API endpoint URL.
     * @param array $data Request payload (sent as JSON for non-GET requests).
     * @param array $headers Request headers.
     * @return array Normalized response: ['body' => array, 'raw_response' => string, 'status_code' => int, 'headers' => array]
     * @throws \RuntimeException If the request fails.
     */
    protected function httpClient(string $method, string $url, array $data = [], array $headers = []): array
    {
        $method = strtoupper($method);

        if (class_exists(\GuzzleHttp\Client::class)) {
            return $this->guzzleRequest($method, $url, $data, $headers);
        }

        return $this->streamRequest($method, $url, $data, $headers);
    }

    /**
     * Perform the request with Guzzle.
     */
    private function guzzleRequest(string $method, string $url, array $data, array $headers): array
    {
        $client = new \GuzzleHttp\Client([
            'timeout' => $this->config['timeout'] ?? 30,
            'verify' => true,
        ]);

        $options = ['headers' => $headers];

        if ($method === 'GET') {
            $options['query'] = $data;
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options['json'] = $data;
        }

        try {
            $response = $client->request($method, $url, $options);
            $body = (string) $response->getBody();
            $statusCode = $response->getStatusCode();

            return $this->normalizeResponse($body, $statusCode, $response->getHeaders());
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new \RuntimeException("HTTP request failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Perform the request with a stream context (no external dependencies).
     */
    private function streamRequest(string $method, string $url, array $data, array $headers): array
    {
        $headerLines = [];
        foreach ($headers as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $headerLines[] = "{$key}: {$item}";
                }
            } else {
                $headerLines[] = "{$key}: {$value}";
            }
        }

        $contextOptions = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headerLines),
                'ignore_errors' => true,
                'timeout' => $this->config['timeout'] ?? 30,
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
            ],
        ];

        if (!in_array($method, ['GET', 'HEAD'], true)) {
            $contextOptions['http']['content'] = json_encode($data);
            $contextOptions['http']['header'] .= "\r\nContent-Type: application/json";
        } elseif ($data !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($data);
        }

        $context = stream_context_create($contextOptions);
        $response = @file_get_contents($url, false, $context);
        $responseHeaders = $http_response_header ?? [];

        if ($response === false) {
            $error = error_get_last();
            throw new \RuntimeException("HTTP request failed: " . ($error['message'] ?? 'Unknown error'));
        }

        $statusCode = 0;
        if (!empty($responseHeaders)) {
            sscanf($responseHeaders[0], 'HTTP/%*d.%*d %d', $statusCode);
        }

        return $this->normalizeResponse($response, $statusCode, $responseHeaders);
    }

    /**
     * Normalize a raw HTTP response into the standard array shape.
     */
    protected function normalizeResponse(string $response, int $statusCode, array $responseHeaders): array
    {
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'raw_response' => $response,
                'status_code' => $statusCode,
                'headers' => $responseHeaders,
            ];
        }

        return [
            'body' => $decoded,
            'raw_response' => $response,
            'status_code' => $statusCode,
            'headers' => $responseHeaders,
        ];
    }

    /**
     * Sanitize input data.
     *
     * @param array $data
     * @return array
     */
    protected function sanitize(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $data);
    }
}
