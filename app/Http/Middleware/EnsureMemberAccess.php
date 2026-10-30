<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'user', 403, 'هذه الصفحة خاصة بالمشتركين.');

        return $next($request);
    }
}
