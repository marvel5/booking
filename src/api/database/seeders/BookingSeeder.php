<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ResourceSeeder::class);

        $resource = fn(string $name): Resource => Resource::where('name', $name)->firstOrFail();

        $bookings = [
            [
                'resource_id' => $resource('Apartament 101')->id,
                'start_at' => '2026-04-01 14:00:00',
                'end_at' => '2026-04-05 12:00:00',
                'customer_name' => 'Jan Kowalski',
            ],
            [
                'resource_id' => $resource('Apartament 101')->id,
                'start_at' => '2026-04-10 14:00:00',
                'end_at' => '2026-04-14 12:00:00',
                'customer_name' => 'Anna Nowak',
            ],
            [
                'resource_id' => $resource('Apartament 102')->id,
                'start_at' => '2026-04-03 14:00:00',
                'end_at' => '2026-04-06 12:00:00',
                'customer_name' => 'Piotr Wiśniewski',
            ],
            [
                'resource_id' => $resource('Apartament 201')->id,
                'start_at' => '2026-04-15 14:00:00',
                'end_at' => '2026-04-20 12:00:00',
                'customer_name' => 'Maria Zielińska',
            ],
        ];

        foreach ($bookings as $booking) {
            Booking::create($booking);
        }

        Resource::all()->each(function (Resource $resource) {
            Booking::factory()
                ->count(3)
                ->for($resource)
                ->create();
        });
    }
}
