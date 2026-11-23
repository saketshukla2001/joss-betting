<?php

namespace Tests\Feature;

use App\Support\CricketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CricketStatusTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('statusCases')]
    public function test_status_is_normalized(string $raw, string $group, string $label): void
    {
        $normalized = CricketStatus::normalize($raw);

        $this->assertSame($group, $normalized['group'], "group for {$raw}");
        $this->assertSame($label, $normalized['label'], "label for {$raw}");
        $this->assertSame($raw, $normalized['raw']);
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string}>
     */
    public static function statusCases(): array
    {
        return [
            ['NS', CricketStatus::UPCOMING, 'Not started'],
            ['Not Started', CricketStatus::UPCOMING, 'Not started'],
            ['Postp.', CricketStatus::UPCOMING, 'Postponed'],
            ['Postponed', CricketStatus::UPCOMING, 'Postponed'],
            ['Delayed', CricketStatus::UPCOMING, 'Delayed'],
            ['1st Innings', CricketStatus::LIVE, '1st Innings'],
            ['Innings Break', CricketStatus::LIVE, 'Innings Break'],
            ['Stumps', CricketStatus::LIVE, 'Stumps'],
            ['Stump Day 2', CricketStatus::LIVE, 'Stumps'],
            ['Int.', CricketStatus::LIVE, 'Interrupted'],
            ['Interrupted', CricketStatus::LIVE, 'Interrupted'],
            ['Finished', CricketStatus::COMPLETED, 'Finished'],
            ['Odisha won by 13 runs', CricketStatus::COMPLETED, 'Completed'],
            ['Drawn', CricketStatus::COMPLETED, 'Drawn'],
            ['Tied', CricketStatus::COMPLETED, 'Tied'],
            ['No result', CricketStatus::COMPLETED, 'No result'],
            ['Aban.', CricketStatus::COMPLETED, 'Abandoned'],
            ['Abandoned', CricketStatus::COMPLETED, 'Abandoned'],
            ['Cancl.', CricketStatus::COMPLETED, 'Cancelled'],
            ['Cancelled', CricketStatus::COMPLETED, 'Cancelled'],
            ['Match drawn', CricketStatus::COMPLETED, 'Drawn'],
            ['Match tied', CricketStatus::COMPLETED, 'Tied'],
            ['Match starts at Nov 23, 08:00 GMT', CricketStatus::UPCOMING, 'Not started'],
            ['Titans won by 294 runs', CricketStatus::COMPLETED, 'Completed'],
            ['Something odd', CricketStatus::OTHER, 'Something odd'],
        ];
    }
}
