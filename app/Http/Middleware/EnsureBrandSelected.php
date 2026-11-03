<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBrandSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user?->hasRole('SuperAdmin')
            && ! session('brand_id')
            && ! $request->routeIs('brand.picker')
        ) {
            // Recuerda a dónde quería ir, para mandarlo ahí después de elegir
            if ($request->isMethod('get')) {
                session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('brand.picker');
        }

        return $next($request);
    }
}