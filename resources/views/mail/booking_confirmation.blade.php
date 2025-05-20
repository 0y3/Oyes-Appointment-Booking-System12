<x-mail::message>
# Appointment Confirmation

Hello {{ $booking->visitor_name }},

Your appointment has been booked successfully.

**Details:**
Date: {{ \Carbon\Carbon::parse($booking->start_time)->format('l, F j, Y') }}
Time: {{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}


@if($booking->description)
**Notes:**
{{ $booking->description }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
