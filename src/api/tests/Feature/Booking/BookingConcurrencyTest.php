<?php

namespace Tests\Feature\Booking;

use App\Exceptions\BookingConflictException;
use App\Services\BookingService;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingConcurrencyTest extends TestCase
{
    /**
     * No database refresh trait — child processes spawned by Concurrency::driver('process')
     * connect to the database independently and cannot see data held inside an open
     * transaction. We manage state manually with committed inserts and explicit cleanup.
     */
    private int $resourceId;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::withoutForeignKeyConstraints(function () {
            DB::table('bookings')->truncate();
            DB::table('resources')->truncate();
        });

        // Committed insert: visible to every child process immediately
        $this->resourceId = DB::table('resources')->insertGetId([
            'name'        => 'Concurrency Test Resource',
            'description' => null,
            'capacity'    => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::withoutForeignKeyConstraints(function () {
            DB::table('bookings')->truncate();
            DB::table('resources')->truncate();
        });

        parent::tearDown();
    }

    /**
     * Spawns 5 real OS processes that race to book the same slot simultaneously.
     *
     * Each process runs BookingService::book() which opens a DB transaction and
     * issues SELECT … FOR UPDATE on the resource row. MySQL serialises the
     * processes at that lock point — only one proceeds to INSERT while the rest
     * wait, then detect the conflict and throw BookingConflictException.
     *
     * Using Concurrency::driver('process') is intentional: the sync driver
     * (default in tests) executes closures sequentially in the parent process
     * and would not exercise the DB lock at all.
     */
    public function test_only_one_booking_is_created_when_five_processes_race_for_the_same_slot(): void
    {
        $resourceId = $this->resourceId;

        $tasks = [];

        for ($i = 1; $i <= 5; $i++) {
            $tasks[] = function () use ($resourceId, $i): string {
                try {
                    app(BookingService::class)->book([
                        'resource_id'   => $resourceId,
                        'start_at'      => '2026-06-01 14:00:00',
                        'end_at'        => '2026-06-05 12:00:00',
                        'customer_name' => "Klient {$i}",
                    ]);

                    return 'success';
                } catch (BookingConflictException) {
                    return 'conflict';
                }
            };
        }

        $results = Concurrency::driver('process')->run($tasks);

        $successCount  = count(array_filter($results, fn(string $r) => $r === 'success'));
        $conflictCount = count(array_filter($results, fn(string $r) => $r === 'conflict'));

        // Guard against unexpected results (e.g. PDOException, missing connection)
        // that would return neither 'success' nor 'conflict', silently skewing counts.
        $this->assertCount(5, $results, 'All five processes must return a result.');
        $this->assertSame(5, $successCount + $conflictCount, 'Every process must return either "success" or "conflict" — no unexpected exceptions.');

        $this->assertSame(1, $successCount,  'Exactly one booking should succeed.');
        $this->assertSame(4, $conflictCount, 'Remaining four should be rejected as conflicts.');
        $this->assertDatabaseCount('bookings', 1);
    }
}
