<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireSelectedAirline
{
    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('selected_airline_id')) {
            return redirect()->route('aircraft_types.index')
                ->with('error', 'Please select an airline first.');
        }

        return $next($request);
    }
}
