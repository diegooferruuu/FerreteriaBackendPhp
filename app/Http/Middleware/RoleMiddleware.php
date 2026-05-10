<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->user()->hasRole($role)) {
            $response = [
                'status' => false,
                'message' => 'Está tratando de ingresar a una página no autorizada.',
            ];
            return response()->json($response, 403);
        }
        return $next($request);
    }
}
