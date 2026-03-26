<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class BookingConflictException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'The selected time slot is already booked.',
        ], 422);
    }
}
