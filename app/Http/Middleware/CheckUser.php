<?php

namespace App\Http\Middleware;

use Closure;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (\Auth::guard($guard)->check()) {
            if (\Auth::guard($guard)->user()->status == 0) {
                if ($request->is('api/*')) {
                    return response()->json(['status' => 'unauthenticated', 'message' => "Your account has been blocked", 'data' => null], 401);
                } else if ($request->expectsJson()) {
                    return response()->json(['status' => 'Your account has been deactivated, to activate the account contact or write to us'], 300);
                } else {
                    \Auth::logout();
                    return redirect()->route('login')->with('warning', 'Your account has been deactivated, to activate the account contact or write to us');
                }
            }
        }

        return $next($request);
    }
}
