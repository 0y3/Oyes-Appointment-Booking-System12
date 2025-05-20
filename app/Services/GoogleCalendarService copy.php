<?php

namespace App\Services;

use App\Models\GoogleAccount;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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

    /**
     * Refresh expired access token
     */
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
     * Get list of user's calendars
     */
    public function listCalendars(GoogleAccount $account): array
    {
        $service = $this->getCalendarService($account);
        
        $calendarList = $service->calendarList->listCalendarList();
        
        return array_map(function ($calendar) {
            return [
                'id' => $calendar->getId(),
                'summary' => $calendar->getSummary(),
                'timeZone' => $calendar->getTimeZone()
            ];
        }, iterator_to_array($calendarList->getItems()));
    }

    /**
     * Get primary calendar timezone
     */
    public function getPrimaryCalendarTimezone(GoogleAccount $account): string
    {
        $service = $this->getCalendarService($account);
        $calendar = $service->calendars->get('primary');
        return $calendar->getTimeZone();
    }

    /**
     * Create calendar event
     */
    public function createEvent(GoogleAccount $account, array $eventData): Event
    {
        $service = $this->getCalendarService($account);
        $event = new Event([
            'summary' => $eventData['summary'],
            'description' => $eventData['description'] ?? null,
            'start' => [
                'dateTime' => Carbon::parse($eventData['start'])->toIso8601String(),
                'timeZone' => $eventData['timezone'],
            ],
            'end' => [
                'dateTime' => Carbon::parse($eventData['end'])->toIso8601String(),
                'timeZone' => $eventData['timezone'],
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

    /**
     * Get all future events for sync
     */
    public function getFutureEvents(GoogleAccount $account): array
    {
        $service = $this->getCalendarService($account);
        
        $optParams = [
            'timeMin' => now()->toIso8601String(),
            'maxResults' => 250,
            'singleEvents' => true,
            'orderBy' => 'startTime'
        ];
        
        $events = $service->events->listEvents(
            $account->calendar_id ?? 'primary',
            $optParams
        );

        return iterator_to_array($events->getItems());
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

    /**
     * Revoke Google access
     */
    public function revokeToken(GoogleAccount $account): bool
    {
        try {
            $this->client->setAccessToken(decrypt($account->token));
            return $this->client->revokeToken();
        } catch (\Exception $e) {
            Log::error("Failed to revoke token: " . $e->getMessage());
            return false;
        }
    }
}