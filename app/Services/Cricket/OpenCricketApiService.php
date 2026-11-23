<?php

namespace App\Services\Cricket;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenCricketApiService
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $connectTimeout;
    protected int $retries;
    protected int $retryDelayMs;

    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?int $timeout = null,
        ?int $retries = null,
        ?int $connectTimeout = null
    ) {
        $this->apiKey = $apiKey ?? config('services.open_cricket.key');
        $this->baseUrl = rtrim((string) ($baseUrl ?? config('services.open_cricket.base_url', 'https://opencricketapi.com/v1/')), '/');
        $this->timeout = max(1, $timeout ?? (int) config('services.open_cricket.timeout', 10));
        $this->connectTimeout = max(1, $connectTimeout ?? (int) config('services.open_cricket.connect_timeout', 5));
        $this->retries = max(0, $retries ?? (int) config('services.open_cricket.retries', 2));
        $this->retryDelayMs = max(50, (int) config('services.open_cricket.retry_delay_ms', 200));
    }

    /**
     * Check whether the API key is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Get the configured base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the configured timeout in seconds.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Test connectivity to the OpenCricketAPI service.
     *
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null}
     */
    public function testConnection(): array
    {
        return $this->getMatches(['limit' => 1]);
    }

    /**
     * Fetch cricket matches with optional filters.
     *
     * Supported query parameters by OpenCricketAPI:
     * - limit (int, e.g. 10)
     * - cursor (string)
     * - format (string, e.g. T20, ODI, Test)
     * - status (string, if supported by plan)
     * - date / from / to (string)
     *
     * @param array<string, mixed> $params
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null}
     */
    public function getMatches(array $params = []): array
    {
        return $this->request('GET', 'matches', $params);
    }

    /**
     * Fetch a single match details by match ID.
     *
     * @param string $matchId (e.g. "match_cs_1554980")
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null}
     */
    public function getMatch(string $matchId): array
    {
        $cleanId = trim($matchId);

        if ($cleanId === '') {
            return $this->buildError('Match ID is required.', 'invalid_parameter');
        }

        return $this->request('GET', "matches/{$cleanId}");
    }

    /**
     * Fetch detailed scorecard for a specific match.
     *
     * Returns full innings breakdown including batting, bowling,
     * fall of wickets, extras, and did_not_bat rosters.
     *
     * @param string $matchId (e.g. "match_cs_1554980")
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null}
     */
    public function getScorecard(string $matchId): array
    {
        $cleanId = trim($matchId);

        if ($cleanId === '') {
            return $this->buildError('Match ID is required to fetch a scorecard.', 'invalid_parameter');
        }

        return $this->request('GET', "matches/{$cleanId}/scorecard");
    }

    /**
     * Execute an authenticated HTTP request against OpenCricketAPI.
     *
     * Handles:
     * - Missing API credentials
     * - Rate limiting (HTTP 429)
     * - Client errors (HTTP 4xx)
     * - Server errors (HTTP 5xx)
     * - Network connection / timeout exceptions
     * - Malformed / invalid JSON responses
     * - Diagnostic logging (with credentials strictly redacted)
     *
     * @param string $method GET|POST
     * @param string $endpoint Relative path, e.g. "matches" or "matches/123/scorecard"
     * @param array<string, mixed> $query URL query parameters
     * @param array<string, mixed> $payload Request body for POST/PUT
     * @return array{success: bool, status: int|null, data: mixed, meta: array|null, error: string|null, error_code: string|null}
     */
    public function request(string $method, string $endpoint, array $query = [], array $payload = []): array
    {
        if (! $this->isConfigured()) {
            Log::warning('OpenCricketAPI request aborted: CRICKET_API_KEY is not configured in environment.');

            return $this->buildError(
                'OpenCricketAPI key is not configured. Please define CRICKET_API_KEY in your .env file.',
                'api_key_missing'
            );
        }

        $url = $this->buildUrl($endpoint);
        $method = strtoupper(trim($method));

        try {
            $client = $this->buildClient();

            /** @var Response $response */
            $response = match ($method) {
                'POST' => $client->withQueryParameters($query)->post($url, $payload),
                'PUT' => $client->withQueryParameters($query)->put($url, $payload),
                'DELETE' => $client->withQueryParameters($query)->delete($url, $payload),
                default => $client->get($url, $query),
            };

            return $this->handleResponse($response, $endpoint);
        } catch (ConnectionException $e) {
            Log::error('OpenCricketAPI connection failure / timeout', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return $this->buildError(
                'Connection to OpenCricketAPI timed out or could not be established.',
                'timeout',
                null
            );
        } catch (Throwable $e) {
            Log::error('OpenCricketAPI unexpected request failure', [
                'endpoint' => $endpoint,
                'error_type' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return $this->buildError(
                'An unexpected error occurred while communicating with OpenCricketAPI: '.$e->getMessage(),
                'request_failed',
                null
            );
        }
    }

    /**
     * Construct a configured Laravel HTTP client instance.
     */
    protected function buildClient(): PendingRequest
    {
        $client = Http::timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->withToken($this->apiKey)
            ->acceptJson();

        if ($this->retries > 0) {
            $client->retry($this->retries, $this->retryDelayMs, function (Throwable $e, ?PendingRequest $request) {
                // Retry only on connection exceptions or 5xx server errors
                if ($e instanceof ConnectionException) {
                    return true;
                }

                if ($e instanceof RequestException && $e->response && $e->response->serverError()) {
                    return true;
                }

                return false;
            }, throw: false);
        }

        return $client;
    }

    /**
     * Parse and normalize the HTTP response from OpenCricketAPI.
     *
     * @return array{success: bool, status: int, data: mixed, meta: array|null, error: string|null, error_code: string|null}
     */
    protected function handleResponse(Response $response, string $endpoint): array
    {
        $status = $response->status();
        $rawBody = $response->body();

        // 1. Validate JSON decoding
        $decoded = json_decode($rawBody, true);
        $jsonError = json_last_error();

        if ($jsonError !== JSON_ERROR_NONE && ! empty($rawBody)) {
            Log::warning('OpenCricketAPI returned invalid JSON response', [
                'endpoint' => $endpoint,
                'status' => $status,
                'json_error' => json_last_error_msg(),
            ]);

            return $this->buildError(
                'Invalid JSON response received from OpenCricketAPI.',
                'invalid_json',
                $status
            );
        }

        $decoded = is_array($decoded) ? $decoded : [];

        // 2. Handle HTTP 200 / 2xx Success
        if ($response->successful()) {
            return [
                'success' => true,
                'status' => $status,
                'data' => $decoded['data'] ?? $decoded,
                'meta' => $decoded['meta'] ?? null,
                'error' => null,
                'error_code' => null,
            ];
        }

        // 3. Handle HTTP 429 Rate Limiting
        if ($status === 429) {
            $retryAfter = $response->header('Retry-After');
            $message = $decoded['error']['message'] ?? 'API rate limit exceeded. Please slow down requests.';

            Log::warning('OpenCricketAPI rate limit hit (HTTP 429)', [
                'endpoint' => $endpoint,
                'retry_after' => $retryAfter,
            ]);

            return [
                'success' => false,
                'status' => 429,
                'data' => null,
                'meta' => array_merge($decoded['meta'] ?? [], array_filter(['retry_after' => $retryAfter])),
                'error' => $message,
                'error_code' => 'rate_limited',
            ];
        }

        // 4. Handle standard API client errors (400, 401, 403, 404, etc.)
        $errorMessage = $decoded['error']['message'] ?? $this->defaultErrorMessageForStatus($status);
        $errorCode = $decoded['error']['code'] ?? ('http_'.$status);

        Log::warning('OpenCricketAPI responded with an error', [
            'endpoint' => $endpoint,
            'status' => $status,
            'error_code' => $errorCode,
            'message' => $errorMessage,
        ]);

        return $this->buildError($errorMessage, $errorCode, $status, $decoded['meta'] ?? null);
    }

    /**
     * Resolve a fully qualified URL for an API endpoint.
     */
    protected function buildUrl(string $endpoint): string
    {
        $cleanEndpoint = ltrim($endpoint, '/');

        return "{$this->baseUrl}/{$cleanEndpoint}";
    }

    /**
     * Helper to assemble a standardized error payload.
     *
     * @return array{success: false, status: int|null, data: null, meta: array|null, error: string, error_code: string}
     */
    protected function buildError(string $message, string $code, ?int $status = null, ?array $meta = null): array
    {
        return [
            'success' => false,
            'status' => $status,
            'data' => null,
            'meta' => $meta,
            'error' => $message,
            'error_code' => $code,
        ];
    }

    /**
     * Fallback HTTP status message generator.
     */
    protected function defaultErrorMessageForStatus(int $status): string
    {
        return match ($status) {
            400 => 'Bad request: The request parameters were invalid.',
            401 => 'Unauthorized: OpenCricketAPI key is invalid or expired.',
            403 => 'Forbidden: The requested resource is not available on your plan.',
            404 => 'Resource not found in OpenCricketAPI.',
            500 => 'OpenCricketAPI internal server error.',
            502, 503, 504 => 'OpenCricketAPI service is temporarily unavailable.',
            default => "OpenCricketAPI error (HTTP {$status}).",
        };
    }
}
