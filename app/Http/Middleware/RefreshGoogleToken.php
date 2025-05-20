<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\GoogleCalendarService;
use Symfony\Component\HttpFoundation\Response;

class RefreshGoogleToken
{
    protected $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next): Response
    {
        if ($request->user() && $account = $request->user()->googleAccount) {
            if ($account->token_expires_at && $account->token_expires_at->isPast()) {
                try {
                    $newToken = $this->googleCalendar->refreshToken($account);
                    $account->update([
                        'token' => encrypt($newToken['accessToken']),
                        'expires_in' => now()->addSeconds($newToken['expiresIn']),
                        'is_active' => true
                    ]);
                } catch (\Exception $e) {
                    $account->update(['is_active' => false]);
                    logger()->error('Token refresh failed: ' . $e->getMessage());
                    return redirect()->route('calendar.connect');
                }
            }
        }

        return $next($request);
    }
}
