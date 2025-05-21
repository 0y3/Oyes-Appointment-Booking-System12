<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingService;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function getAvailableSlots(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            // 'timezone' => 'required|string'
        ]);

        // return $this->bookingService->getAvailableSlots($validated);
        return $this->bookingService->getAvailableSlotsGoogle($validated);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'visitor_name' => 'required|string',
            'visitor_email' => 'required|email',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            // 'timezone' => 'required|string'
        ]);

        $request->user()?$validated['user_id'] = $request->user()->id:$validated['user_id'] = 1;
        $validated['timezone'] = config('app.timezone', 'Africa/Lagos');

        $booking = $this->bookingService->createBooking($validated);

        return response()->json('Appointment created successfully', 201);
    }

    public function index()
    {
        return \App\Models\Booking::all();
    }
}
