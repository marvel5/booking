<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->book($request->validated());

        return (new BookingResource($booking))
            ->response()
            ->setStatusCode(201);
    }
}
