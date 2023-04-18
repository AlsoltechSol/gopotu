<?php

namespace App\Http\Middleware;

use Closure;
use App\Guest;

class BothApiGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::guard('api')->check()) {
            if (\Auth::guard('api')->user()->status == 0) {
                if ($request->is('api/*')) {
                    return response()->json(['status' => 'unauthenticated', 'message' => "Your account has been blocked", 'data' => null], 401);
                } else if ($request->expectsJson()) {
                    return response()->json(['status' => 'Your account has been deactivated, to activate the account contact or write to us'], 300);
                } else {
                    \Auth::logout();
                    return redirect()->route('login')->with('warning', 'Your account has been deactivated, to activate the account contact or write to us');
                }
            }

            $request['user_id'] = \Auth::guard('api')->id();
        } elseif ($request->header('guest-token')) {
            $guest = Guest::where('token', $request->header('guest-token'))->first();
            if (!$guest) {
                return response()->json(['status' => 'unauthenticated', 'message' => 'unauthenticated', 'data' => []]);
            }

            $request['guest_id'] = $guest->id;
        } else {
            return response()->json(['status' => 'unauthenticated', 'message' => 'unauthenticated', 'data' => []]);
        }

        return $next($request);
    }
}
