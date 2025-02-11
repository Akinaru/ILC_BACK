<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequestsJsonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ThrottleRequestsException $e) {
            // Retourne une réponse JSON personnalisée
            return response()->json([
                'data' => [
                    'error' => 'Une erreur est survenue, veuillez patienter.',
                    'message' => 'Détail : trop de requêtes en un certain temps.',
                ]
            ], 429); // 429 est le code HTTP pour trop de tentatives
        }
    }
}
