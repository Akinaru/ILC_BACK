<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
            abort(response()->json(['status' => 401, 'message' => 'Accès refusé. Vous devez être connecté pour effectuer cette action.'], 401));
    }
}
