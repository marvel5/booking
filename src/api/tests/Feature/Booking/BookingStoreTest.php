<?php

namespace Tests\Feature\Booking;

use App\Models\Resource;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BookingStoreTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Resource $resource;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resource = Resource::factory()->create();
    }

    public function test_successful_booking_returns_201_with_booking_data(): void
    {
        $response = $this->postJson('/api/v1/bookings', [
            'resource_id' => $this->resource->id,
            'start_at' => '2026-06-01 14:00:00',
            'end_at' => '2026-06-05 12:00:00',
            'customer_name' => 'Jan Kowalski',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'resource_id', 'start_at', 'end_at', 'customer_name', 'created_at'],
            ])
            ->assertJsonPath('data.customer_name', 'Jan Kowalski')
            ->assertJsonPath('data.resource_id', $this->resource->id);

        $this->assertDatabaseHas('bookings', [
            'resource_id' => $this->resource->id,
            'customer_name' => 'Jan Kowalski',
        ]);
    }

    public function test_missing_required_fields_returns_422(): void
    {
        $this->postJson('/api/v1/bookings', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id', 'start_at', 'end_at', 'customer_name']);
    }

    public function test_nonexistent_resource_returns_422(): void
    {
        $this->postJson('/api/v1/bookings', [
            'resource_id' => 99999,
            'start_at' => '2026-06-01 14:00:00',
            'end_at' => '2026-06-05 12:00:00',
            'customer_name' => 'Jan Kowalski',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

    public function test_end_at_before_start_at_returns_422(): void
    {
        $this->postJson('/api/v1/bookings', [
            'resource_id' => $this->resource->id,
            'start_at' => '2026-06-05 12:00:00',
            'end_at' => '2026-06-01 14:00:00',
            'customer_name' => 'Jan Kowalski',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['end_at']);
    }
}
