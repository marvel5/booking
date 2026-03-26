<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BookingOverlapTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Resource $resource;

    /** Existing booking: 2026-06-10 14:00 → 2026-06-15 12:00 */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resource = Resource::factory()->create();

        Booking::factory()
            ->for($this->resource)
            ->from('2026-06-10 14:00:00', '2026-06-15 12:00:00')
            ->create();
    }

    private function attemptBooking(string $startAt, string $endAt): TestResponse
    {
        return $this->postJson('/api/v1/bookings', [
            'resource_id' => $this->resource->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'customer_name' => 'Testowy Klient',
        ]);
    }

    public function test_rejects_booking_where_start_overlaps_existing(): void
    {
        // New booking starts inside the existing one
        $this->attemptBooking('2026-06-12 10:00:00', '2026-06-17 12:00:00')
            ->assertStatus(422)
            ->assertJsonPath('message', 'The selected time slot is already booked.');
    }

    public function test_rejects_booking_where_end_overlaps_existing(): void
    {
        // New booking ends inside the existing one
        $this->attemptBooking('2026-06-08 14:00:00', '2026-06-12 12:00:00')
            ->assertStatus(422)
            ->assertJsonPath('message', 'The selected time slot is already booked.');
    }

    public function test_rejects_booking_that_fully_covers_existing(): void
    {
        // New booking completely wraps the existing one
        $this->attemptBooking('2026-06-09 14:00:00', '2026-06-16 12:00:00')
            ->assertStatus(422)
            ->assertJsonPath('message', 'The selected time slot is already booked.');
    }

    public function test_rejects_booking_that_is_fully_inside_existing(): void
    {
        // New booking is completely inside the existing one
        $this->attemptBooking('2026-06-11 14:00:00', '2026-06-13 12:00:00')
            ->assertStatus(422)
            ->assertJsonPath('message', 'The selected time slot is already booked.');
    }

    public function test_allows_booking_starting_exactly_when_existing_ends(): void
    {
        // Adjacent booking (starts exactly when existing ends) — no conflict
        $this->attemptBooking('2026-06-15 12:00:00', '2026-06-18 12:00:00')
            ->assertStatus(201);
    }

    public function test_allows_booking_ending_exactly_when_existing_starts(): void
    {
        // Adjacent booking (ends exactly when existing starts) — no conflict
        $this->attemptBooking('2026-06-07 14:00:00', '2026-06-10 14:00:00')
            ->assertStatus(201);
    }

    public function test_allows_booking_on_different_resource(): void
    {
        $otherResource = Resource::factory()->create();

        $this->postJson('/api/v1/bookings', [
            'resource_id' => $otherResource->id,
            'start_at' => '2026-06-10 14:00:00',
            'end_at' => '2026-06-15 12:00:00',
            'customer_name' => 'Inny Klient',
        ])->assertStatus(201);
    }
}
