<?php

namespace App\Services;

use App\Exceptions\BookingConflictException;
use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a booking if the slot is available.
     *
     * Concurrency strategy:
     *   1. Lock the resource row with SELECT … FOR UPDATE.
     *      This is the stable anchor — it always exists, so the lock is
     *      acquired even when there are no bookings yet for this resource.
     *      All concurrent transactions for the same resource are serialised here.
     *   2. After the lock is held, check for overlapping bookings (no lock
     *      needed on that query — we own the resource row).
     *   3. Insert only if no conflict found.
     *
     * @param  array{resource_id: int, start_at: string, end_at: string, customer_name: string}  $data
     *
     * @throws BookingConflictException
     */
    public function book(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            // Lock the resource row — serialises all concurrent booking attempts
            // for this resource, including the first booking (empty-set problem).
            Resource::whereKey($data['resource_id'])->lockForUpdate()->firstOrFail();

            $conflict = Booking::where('resource_id', $data['resource_id'])
                ->where('start_at', '<', $data['end_at'])
                ->where('end_at', '>', $data['start_at'])
                ->exists();

            if ($conflict) {
                throw new BookingConflictException();
            }

            return Booking::create($data);
        });
    }
}
