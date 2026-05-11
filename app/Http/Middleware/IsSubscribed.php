<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if (! $user || ! $user->is_subscribed) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Abonnement requis'], 403);
            }

            return redirect('/dashboard')->with('error', 'Abonnement requis pour accéder à cette page.');
        }

        return $next($request);
    }
}
