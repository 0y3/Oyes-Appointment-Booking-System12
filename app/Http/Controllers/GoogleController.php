<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GoogleAccount;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Inertia\Inertia;
use App\Jobs\SyncExistingEvents;

class GoogleController extends Controller
{
    // protected $googleCalendar;

    // public function __construct(GoogleCalendarService $googleCalendar)
    // {
    //     $this->googleCalendar = $googleCalendar;
    // }

    /**
     * Redirect to Google OAuth
     */
    public function connectToGoogle()
    {
        return Socialite::driver('google')
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent select_account',
                'hd' => '*', // Allow any domain
            ])
            ->scopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ])
            ->redirect();

        // return Socialite::driver('google')
        //     ->scopes(['https://www.googleapis.com/auth/calendar'])
        //     ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback(Request $request)
    {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->email)->first();
            $account = GoogleAccount::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'user_id' => auth()->user() ? auth()->user()->id : null,
                    'google_id' => $googleUser->id,
                    'token' => encrypt($googleUser->token),
                    'refresh_token' => encrypt($googleUser->refreshToken),
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
                    'timezone' => $googleUser->user['timezone'] ?? config('app.timezone', 'Africa/Lagos'),
                    'is_active' => true
                ]
            );
            return to_route('dashboard')->with('success', 'Google account connected successfully!');

    }


    public function selectCalendar(Request $request)
    {
        $validated = $request->validate([
            'calendar_id' => 'required|string',
            'timezone' => 'required|timezone'
        ]);

        $request->user()->googleAccount()->update($validated);

        // Sync existing events
        // dispatch(new SyncExistingEvents($request->user()));

        return redirect()->route('dashboard');
    }
    /**
     * Disconnect Google Calendar
     */
    public function disconnect(Request $request)
    {
        $request->user()->googleAccount()->delete();

        return back()->with([
            'message' => 'Google Calendar disconnected'
        ]);
    }
}
