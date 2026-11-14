<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Langue de l'interface : français par défaut, anglais si l'utilisateur l'a choisi (mémorisé en session).
 */
class SetLocale
{
    public const SUPPORTED = ['fr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'fr');

        App::setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : 'fr');

        return $next($request);
    }
}
