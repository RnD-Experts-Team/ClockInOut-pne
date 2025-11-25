<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {        $request->authenticate();

        $request->session()->regenerate();

        // Retrieve the authenticated user
        $user = Auth::user();

        // Ensure the role column exists and redirect accordingly
        if ($user->role == 'admin') {
            return redirect()->route('admin.clocking');
        } elseif ($user->role == 'store_manager') {
            return redirect()->route('native.requests.index');
        } elseif ($user->role == 'user') {
            return redirect()->route('clocking.index');
        }
        
        // Fallback for any other role (or missing role)
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
