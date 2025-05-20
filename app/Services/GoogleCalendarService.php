<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\GoogleAccount;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Laravel\Socialite\Facades\Socialite;

class GoogleCalendarService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
    }

    public function refreshToken(GoogleAccount $account): array
    {
        try {
            $this->client->setAccessToken(json_encode([
                'access_token' => decrypt($account->token),
                'refresh_token' => decrypt($account->refresh_token),
                'expires_in' => Carbon::parse($account->token_expires_at)->diffInSeconds(now())
            ]));

            $newToken = $this->client->fetchAccessTokenWithRefreshToken(
                decrypt($account->refresh_token)
            );

            return [
                'access_token' => $newToken['access_token'],
                'expires_in' => $newToken['expires_in'],
                'created' => $newToken['created'] ?? now()->timestamp
            ];

        } catch (\Exception $e) {
            Log::error("Token refresh failed for account {$account->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Initialize authenticated Calendar service
     */
    protected function getCalendarService(GoogleAccount $account): Calendar
    {
        $this->client->setAccessToken(json_encode([
            'access_token' => decrypt($account->token),
            'expires_in' => Carbon::parse($account->token_expires_at)->diffInSeconds(now())
        ]));

        // Automatically refresh token if expired
        if ($this->client->isAccessTokenExpired()) {
            $newToken = $this->refreshToken($account);
            $account->update([
                'token' => encrypt($newToken['access_token']),
                'token_expires_at' => Carbon::createFromTimestamp(
                    $newToken['created'] + $newToken['expires_in']
                )
            ]);
        }

        return new Calendar($this->client);
    }

    public function getAvailableSlots($date, $timezone)
    {

        // Convert date to vendor's timezone
        $vendorAccount = GoogleAccount::first();
        $vendorTimezone = $vendorAccount->timezone ?? config('app.timezone', 'Africa/Lagos');

        $startOfDay = Carbon::parse($date, $timezone)
            ->setTimezone($vendorTimezone)
            ->startOfDay();

        $endOfDay = $startOfDay->copy()->endOfDay();

        // Get existing events
        $events = Event::get($startOfDay, $endOfDay);

        // Generate all possible slots (8am-5pm)
        $slots = [];
        $current = $startOfDay->copy()->setHour(8)->setMinute(0);
        $endTime = $startOfDay->copy()->setHour(17)->setMinute(0);

        while ($current <= $endTime) {
            $slotEnd = $current->copy()->addHour();

            $isAvailable = true;
            foreach ($events as $event) {
                $eventStart = Carbon::parse($event->start->dateTime);
                $eventEnd = Carbon::parse($event->end->dateTime);

                if ($current->between($eventStart, $eventEnd) ||
                    $slotEnd->between($eventStart, $eventEnd)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $slots[] = [
                    'start' => $current->copy()->setTimezone($timezone),
                    'end' => $slotEnd->copy()->setTimezone($timezone),
                    'formatted' => $current->setTimezone($timezone)->format('g:i A')
                ];
            }

            $current->addHour();
        }

        return $slots;
    }

    /**
     * Create calendar event
     */
    public function createEvent1(GoogleAccount $user, $eventDetails)
    {
        $googleClient = new Client();
        $googleClient->setAccessToken(decrypt($user->token));

        if ($googleClient->isAccessTokenExpired()) {
            $googleClient->fetchAccessTokenWithRefreshToken(decrypt($user->refresh_token));
            $user->token = encrypt($googleClient->getAccessToken());
            $user->save();
        }

        $event = new Event;
        $event->name = $eventDetails['visitor_name'];
        $event->description = $eventDetails['description']??null;
        $event->startDateTime = Carbon::parse($eventDetails['start_time']);
        $event->endDateTime = Carbon::parse($eventDetails['end_time']);
        $event->save();
    }

    public function createEvent(GoogleAccount $account, array $eventData): Event
    {
        $service = $this->getCalendarService($account);

        $event = new Event([
            'summary' => $eventData['summary'],
            'description' => $eventData['description'] ?? null,
            'start' => [
                'dateTime' => Carbon::parse($eventData['start'])->toIso8601String(),
                'timeZone' => $eventData['timezone']??config('app.timezone', 'Africa/Lagos'),
            ],
            'end' => [
                'dateTime' => Carbon::parse($eventData['end'])->toIso8601String(),
                'timeZone' => $eventData['timezone']??config('app.timezone', 'Africa/Lagos'),
            ],
            'attendees' => array_map(function ($attendee) {
                return ['email' => $attendee['email']];
            }, $eventData['attendees'] ?? []),
        ]);

        return $service->events->insert(
            $account->calendar_id ?? 'primary',
            $event,
            ['sendUpdates' => 'all']
        );
    }

    public function getEvent($booking)
    {
        
    }
}
