<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Webkul\Employee\Models\Employee;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('filament.staff.auth.login');
        }

        $user = Auth::user();
        
        // Check if the user has an associated employee record
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            Auth::logout();
            return redirect()->route('filament.staff.auth.login')
                ->withErrors(['email' => 'Access denied. Only employees can access the staff panel.']);
        }

        return $next($request);
    }
}