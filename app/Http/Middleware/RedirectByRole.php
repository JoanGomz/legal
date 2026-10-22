<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user->hasRole('Asesor') && !$request->is('asesor/*')) {
            return redirect('/asesor/dashboard');
        }

        return $next($request);
    }
}
