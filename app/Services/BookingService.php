<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\GoogleAccount;
use App\Mail\BookingConfirmation;
use Illuminate\Support\Facades\Mail;
use App\Services\GoogleCalendarService;

class BookingService
{
    protected $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    public function createBooking($data)
    {
        // Create booking in database
        $booking = Booking::create($data);


        // Add to Google Calendar
        $googleUser = GoogleAccount::where('user_id',$data['user_id'])->whereNotNull('token')->first();
        if($googleUser){
            $googleEventData = [
                'summary' => "Appointment with {$data['visitor_name']}",
                'start' => $data['start_time'],
                'end' => $data['end_time'],
                'timezone' => $data['timezone'],
                'description' => $data['description'] ?? null,
                'attendees' => [
                    ['email' => $data['visitor_email']]
                ]
            ];
            $event = $this->googleCalendar->createEvent($googleUser,$googleEventData);
            $event ? $booking->updateOrFail(['google_event_id' => $event->id]) : '';
        }

        // Send confirmation email
        Mail::to($booking->visitor_email)->send(new BookingConfirmation($booking));

        return $booking;
    }

    public function getAvailableSlots($data, $timezone = null,$slotDurationinMin = 60)
    {
        // Set default timezone if not provided
        $timezone = $timezone ?? config('app.timezone', 'Africa/Lagos');
        $selectedDate = Carbon::parse($data['date'], $timezone)->setTimezone(config('app.timezone'));
        // Fetch bookings for this date
        $bookings = Booking::whereDate('start_time', $selectedDate->toDateString())->get();

        $workStartMin = 480; //8hr * 60
        $workEndMin   = 1020; //17hr * 60

        $slots = [];
        $now = Carbon::now(config('app.timezone'));

        for ($minute = $workStartMin; $minute + $slotDurationinMin <= $workEndMin; $minute += $slotDurationinMin) {

            $slotStart = $selectedDate->copy()->addMinutes($minute);
            $slotEnd = $slotStart->copy()->addMinutes($slotDurationinMin);

            // Skip past slots (only for today)
            if ($selectedDate->isToday() && $slotStart->lte($now)) {
                continue;
            }

            // Check if slot overlaps with any booking
            $isBooked = $bookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                return $slotStart->lt($booking->end_time) && $slotEnd->gt($booking->start_time);
            });

            if (!$isBooked) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'label' => $slotStart->format('g:i A'),
                ];
            }
        }

        return $slots;
    }

    public function getAvailableSlotsGoogle($data, $timezone = null,$slotDurationinMin = 60)
    {
        $user_id = $data['user_id']??1;
        $googleUser = GoogleAccount::where('user_id',$user_id)->whereNotNull('token')->first();


        $timezone = $timezone ?? config('app.timezone', 'Africa/Lagos');
        $selectedDate = Carbon::parse($data['date'], $timezone)->setTimezone(config('app.timezone'));
        $googleFilter = [
            'timemin' => $selectedDate->startOfDay()->toIso8601String(),
            // 'timemax' => $selectedDate->endOfDay()->toIso8601String(),
            'timezone' => $timezone
        ];

        $googleBookings  = collect($this->googleCalendar->getEvents($googleUser, $googleFilter));

        $workStartMin = 480; //8hr * 60
        $workEndMin   = 1140; //19hr * 60

        $slots = [];
        $now = Carbon::now(config('app.timezone'));

         for ($minute = $workStartMin; $minute + $slotDurationinMin <= $workEndMin; $minute += $slotDurationinMin) {

            $slotStart = $selectedDate->copy()->addMinutes($minute);
            $slotEnd = $slotStart->copy()->addMinutes($slotDurationinMin);

            // Skip past slots (only for today)
            if ($selectedDate->isToday() && $slotStart->lte($now)) {
                continue;
            }

            // Check if slot overlaps with any booking
            $isBooked = $googleBookings->contains(function ($googleBookings) use ($slotStart, $slotEnd) {
                return $slotStart->lt($googleBookings['end']['dateTime']) && $slotEnd->gt($googleBookings['start']['dateTime']);
            });

            if (!$isBooked) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'label' => $slotStart->format('g:i A'),
                ];
            }
        }

        return $slots;
    }
}
