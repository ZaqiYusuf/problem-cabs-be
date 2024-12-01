<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $level)
{
    $user = Auth::user();

    if ($user->level !== $level) { // Pastikan kolom dan nilai sesuai
        return response()->json([
            'message' => 'Unauthorized: Invalid level',
        ], 403);
    }

    return $next($request);
}

    
}


