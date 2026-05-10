<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $rol)
    {
        // Pre-Middleware Action
        if (!$request->user()->hasRole($rol)) {
            $response = [
                'status' => false,
                'message' => 'Esta acción no está autorizada.',
            ];
            return response()->json($response, 401);
        }

        $response = $next($request);

        // Post-Middleware Action
        return $response;
    }
}
