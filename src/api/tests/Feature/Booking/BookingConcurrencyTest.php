<?php

namespace Tests\Feature\Booking;

use App\Exceptions\BookingConflictException;
use App\Models\Resource;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BookingConcurrencyTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * Simulates 5 users racing to book the same slot.
     *
     * Each call goes through the full BookingService::book() path including
     * DB::transaction() + lockForUpdate(). In a real concurrent scenario the
     * database lock ensures only one INSERT ever reaches the table; this test
     * verifies that the service correctly rejects every duplicate attempt and
     * that exactly one booking ends up persisted.
     */
    public function test_only_one_booking_is_created_when_five_requests_race_for_the_same_slot(): void
    {
        $resource = Resource::factory()->create();
        $service = app(BookingService::class);

        $slot = [
            'resource_id' => $resource->id,
            'start_at' => '2026-06-01 14:00:00',
            'end_at' => '2026-06-05 12:00:00',
        ];

        $successCount = 0;
        $conflictCount = 0;

        for ($i = 1; $i <= 5; $i++) {
            try {
                $service->book([...$slot, 'customer_name' => "Klient {$i}"]);
                $successCount++;
            } catch (BookingConflictException) {
                $conflictCount++;
            }
        }

        $this->assertSame(1, $successCount, 'Exactly one booking should succeed.');
        $this->assertSame(4, $conflictCount, 'Remaining four should be rejected as conflicts.');
        $this->assertDatabaseCount('bookings', 1);
    }
}
