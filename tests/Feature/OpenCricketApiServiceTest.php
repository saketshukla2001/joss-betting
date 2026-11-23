<?php

namespace Tests\Feature;

use App\Services\Cricket\OpenCricketApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenCricketApiServiceTest extends TestCase
{
    public function test_service_detects_when_api_key_is_missing(): void
    {
        $service = new OpenCricketApiService(apiKey: '');

        $this->assertFalse($service->isConfigured());

        $result = $service->getMatches();

        $this->assertFalse($result['success']);
        $this->assertSame('api_key_missing', $result['error_code']);
        $this->assertNull($result['status']);
    }

    public function test_service_fetches_matches_successfully(): void
    {
        Http::fake([
            'https://opencricketapi.com/v1/matches*' => Http::response([
                'data' => [
                    [
                        'id' => 'match_cs_1554980',
                        'format' => 'T20',
                        'teams' => [
                            ['name' => 'Zimbabwe'],
                            ['name' => 'West Indies'],
                        ],
                    ],
                ],
                'meta' => [
                    'count' => 1,
                ],
            ], 200),
        ]);

        $service = new OpenCricketApiService(apiKey: 'dummy_test_key');

        $result = $service->getMatches(['limit' => 1]);

        $this->assertTrue($result['success']);
        $this->assertSame(200, $result['status']);
        $this->assertCount(1, $result['data']);
        $this->assertSame('match_cs_1554980', $result['data'][0]['id']);
        $this->assertSame(1, $result['meta']['count']);
    }

    public function test_service_fetches_scorecard_successfully(): void
    {
        Http::fake([
            'https://opencricketapi.com/v1/matches/match_cs_1554980/scorecard' => Http::response([
                'data' => [
                    [
                        'innings' => 1,
                        'team' => 'Zimbabwe',
                        'total' => ['runs' => 142, 'wickets' => 6, 'overs' => '20'],
                        'batting' => [],
                        'bowling' => [],
                    ],
                ],
                'meta' => ['request_id' => 'req_test_123'],
            ], 200),
        ]);

        $service = new OpenCricketApiService(apiKey: 'dummy_test_key');

        $result = $service->getScorecard('match_cs_1554980');

        $this->assertTrue($result['success']);
        $this->assertSame(200, $result['status']);
        $this->assertSame(1, $result['data'][0]['innings']);
        $this->assertSame(142, $result['data'][0]['total']['runs']);
    }

    public function test_service_handles_scorecard_validation_for_empty_match_id(): void
    {
        $service = new OpenCricketApiService(apiKey: 'dummy_test_key');

        $result = $service->getScorecard('   ');

        $this->assertFalse($result['success']);
        $this->assertSame('invalid_parameter', $result['error_code']);
    }

    public function test_service_handles_not_found_error_404(): void
    {
        Http::fake([
            'https://opencricketapi.com/v1/matches/invalid_id/scorecard' => Http::response([
                'error' => [
                    'code' => 'match_not_found',
                    'message' => 'No match with id invalid_id.',
                ],
                'meta' => ['request_id' => 'req_err_404'],
            ], 404),
        ]);

        $service = new OpenCricketApiService(apiKey: 'dummy_test_key');

        $result = $service->getScorecard('invalid_id');

        $this->assertFalse($result['success']);
        $this->assertSame(404, $result['status']);
        $this->assertSame('match_not_found', $result['error_code']);
        $this->assertStringContainsString('No match with id invalid_id', $result['error']);
    }

    public function test_service_handles_rate_limit_429(): void
    {
        Http::fake([
            'https://opencricketapi.com/v1/matches*' => Http::response([
                'error' => [
                    'code' => 'rate_limit_exceeded',
                    'message' => 'Rate limit exceeded.',
                ],
                'meta' => ['request_id' => 'req_err_429'],
            ], 429, ['Retry-After' => '15']),
        ]);

        $service = new OpenCricketApiService(apiKey: 'dummy_test_key');

        $result = $service->getMatches();

        $this->assertFalse($result['success']);
        $this->assertSame(429, $result['status']);
        $this->assertSame('rate_limited', $result['error_code']);
        $this->assertSame('15', $result['meta']['retry_after']);
    }

    public function test_service_handles_timeout_connection_exception(): void
    {
        Http::fake([
            'https://opencricketapi.com/v1/matches*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out');
            },
        ]);

        $service = new OpenCricketApiService(apiKey: 'dummy_test_key');

        $result = $service->getMatches();

        $this->assertFalse($result['success']);
        $this->assertNull($result['status']);
        $this->assertSame('timeout', $result['error_code']);
        $this->assertStringContainsString('timed out', $result['error']);
    }

    public function test_service_handles_invalid_json_response(): void
    {
        Http::fake([
            'https://opencricketapi.com/v1/matches*' => Http::response('<html>Gateway Error</html>', 502),
        ]);

        $service = new OpenCricketApiService(apiKey: 'dummy_test_key');

        $result = $service->getMatches();

        $this->assertFalse($result['success']);
        $this->assertSame(502, $result['status']);
        $this->assertSame('invalid_json', $result['error_code']);
    }
}
