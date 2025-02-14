<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Check if the user is authenticated.
        if (!Auth::check()) {
            // Redirect to the login page if not authenticated.
            return redirect()->route('login');
        }

        // Check if the authenticated user has the required role.
        if (Auth::user()->role !== $role) {
            // Redirect based on the user's actual role.
            if (Auth::user()->role === 'admin') {
                return redirect()->route('dashboard');
            } elseif (Auth::user()->role === 'user') {
                return redirect('/clocking');
            } else {
                // For any other role, you can choose to abort or handle it as needed.
                abort(403, 'Unauthorized access.');
            }
        }

        // If the user is authenticated and has the expected role, allow the request to proceed.
        return $next($request);
    }
}
