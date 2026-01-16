<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');

        if ($locale && in_array($locale, ['ar', 'en'])) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.fallback_locale', 'en'));
        }

        return $next($request);
    }
}
