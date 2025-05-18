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
    public function handle($request, Closure $next, $role)
    {
        if (!auth()->user()->hasRole($role)) {
            $message = match($role) {
                'admin' => 'Accès refusé. Vous devez être connecté et administrateur pour effectuer cette action.',
                'chefdept' => 'Accès refusé. Vous devez être au moins chef de département pour effectuer cette action.',
                default => 'Accès refusé. Vous n\'avez pas les permissions nécessaires.'
            };
            
            return response()->json(['status' => 401, 'message' => $message], 401);
        }
        return $next($request);
    }
}
