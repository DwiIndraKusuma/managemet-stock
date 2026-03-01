<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // If already authenticated, redirect to dashboard
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Set session from token (called after API login)
     */
    public function setSession(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = PersonalAccessToken::findToken($request->token);
        
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }

        // Login user via session with remember
        Auth::login($token->tokenable, true);
        
        // Regenerate session ID for security
        $request->session()->regenerate();
        
        return response()->json(['success' => true, 'message' => 'Session set successfully']);
    }

    /**
     * Logout and destroy session
     */
    public function logout(Request $request)
    {
        // Logout from session
        Auth::logout();
        
        // Invalidate session
        $request->session()->invalidate();
        
        // Regenerate CSRF token
        $request->session()->regenerateToken();
        
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }
}
