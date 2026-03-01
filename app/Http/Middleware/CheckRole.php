<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
            return redirect('/login');
        }

        $user = auth()->user();
        $userRole = $user->role->name ?? null;

        // Flatten roles array (handle comma-separated roles)
        $allowedRoles = [];
        foreach ($roles as $role) {
            if (str_contains($role, ',')) {
                $allowedRoles = array_merge($allowedRoles, array_map('trim', explode(',', $role)));
            } else {
                $allowedRoles[] = trim($role);
            }
        }

        if (!in_array($userRole, $allowedRoles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Required role: ' . implode(', ', $allowedRoles),
                ], 403);
            }
            return redirect('/dashboard')->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
