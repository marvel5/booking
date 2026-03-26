<?php

namespace App\Services;

use App\Exceptions\BookingConflictException;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a booking if the slot is available, using a pessimistic lock
     * to prevent race conditions when multiple requests arrive simultaneously.
     *
     * @param  array{resource_id: int, start_at: string, end_at: string, customer_name: string}  $data
     *
     * @throws BookingConflictException
     */
    public function book(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $conflict = Booking::where('resource_id', $data['resource_id'])
                ->where('start_at', '<', $data['end_at'])
                ->where('end_at', '>', $data['start_at'])
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw new BookingConflictException();
            }

            return Booking::create($data);
        });
    }
}
