<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasGoogleCalendar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        // Check if user is admin and doesn't have Google Calendar connected
        if ($user && !$user->googleAccount) {
            return redirect()->route('calendar.connect')
                ->with('message', 'Please connect your Google Calendar to access admin features');
        }

        return $next($request);
    }
}
