<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * Middleware de caché de páginas.
 *
 * Almacena en caché las respuestas exitosas de peticiones GET no AJAX
 * para mejorar el rendimiento de la aplicación. Genera una clave de caché
 * única basada en el ID del usuario, sus roles y la URL completa de la petición.
 *
 * Si la respuesta ya está en caché, la retorna directamente sin ejecutar
 * el controlador. Si no está en caché, ejecuta la petición normalmente
 * y almacena la respuesta durante 300 segundos (5 minutos).
 *
 * Se aplica únicamente a peticiones GET normales (no AJAX) para evitar
 * cachear peticiones asíncronas o mutaciones de datos.
 *
 * Se ejecuta en el ciclo de petición después de la autenticación.
 *
 * @package App\Http\Middleware
 */
class CachePage
{
    /**
     * Maneja la petición verificando si existe una respuesta en caché.
     *
     * Para peticiones GET no AJAX, genera una clave basada en usuario, roles y URL.
     * Si existe caché válida, la retorna. Si no, ejecuta la petición y almacena el resultado.
     *
     * @param \Illuminate\Http\Request $request Objeto de petición HTTP de Laravel.
     * @param Closure $next Callback que continúa el pipeline de middleware.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse Respuesta HTTP o redirección.
     */
    public function handle($request, Closure $next)
    {
        if (!$request->isMethod('get') || $request->ajax()) {
            return $next($request);
        }

        $userId = Auth::id() ?? 'guest';
        /** @var User|null $user */
        $user = Auth::user();
        $userRoles = $user?->getRoleNames()?->implode(',', '') ?? '';
        $key = 'page_' . md5($userId . '_' . $userRoles . '_' . $request->fullUrl());

        if (Cache::has($key)) {
            return response(Cache::get($key));
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($key, $response->getContent(), 300);
        }

        return $response;
    }
}
