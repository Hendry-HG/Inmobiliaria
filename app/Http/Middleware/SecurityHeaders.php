<?php
// app/Http/Middleware/SecurityHeaders.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware que inyecta headers HTTP de seguridad en todas las respuestas.
 *
 * Este middleware se ejecuta en cada peticion HTTP entrante y agrega multiples
 * headers de seguridad para proteger la aplicacion contra ataques comunes:
 * - Clickjacking (X-Frame-Options, frame-ancestors)
 * - MIME sniffing (X-Content-Type-Options)
 * - XSS reflejado (X-XSS-Protection)
 * - Ataques de inferencia por referrer (Referrer-Policy)
 * - MitM en HTTPS (HSTS)
 * - Acceso no autorizado a APIs del navegador (Permissions-Policy)
 * - Inyeccion de contenido (Content-Security-Policy)
 */
class SecurityHeaders
{
    /**
     * Intercepta cada peticion HTTP y agrega los headers de seguridad a la respuesta.
     *
     * Flujo:
     * 1. Permite que la peticion continüe hacia el siguiente handler/controller
     *    almacenando la respuesta en $response.
     * 2. Agrega los headers de seguridad basicos a la respuesta.
     * 3. En produccion, agrega HSTS para forzar conexiones HTTPS.
     * 4. Configura la politica de permisos del navegador.
     * 5. Construye y agrega el Content Security Policy completo.
     * 6. Retorna la respuesta con todos los headers inyectados.
     *
     * @param Request $request La peticion HTTP entrante.
     * @param Closure $next Callback que ejecuta el siguiente middleware o controller.
     * @return \Illuminate\Http\Response Respuesta HTTP con headers de seguridad agregados.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // --- HEADERS DE SEGURIDAD BASICOS ---

        // X-Frame-Options: DENY
        // Impide que la pagina sea cargada dentro de un iframe, frame o embed de cualquier origen.
        // Previene ataques de clickjacking donde un atacante superpone la pagina en un iframe
        // oculto para engañar al usuario a hacer clic en elementos no deseados.
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-Content-Type-Options: nosniff
        // Impide que el navegador "adivine" el tipo MIME de un archivo basandose en su contenido.
        // Sin este header, el navegador podria interpretar un archivo malicioso como un script
        // o ejecutar un archivo subido como si fuera una imagen. Obliga a respetar el Content-Type
        // declarado por el servidor.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection: 1; mode=block
        // Activa el filtro de XSS integrado en navegadores antiguos (IE, Chrome antiguo).
        // El valor '1' activa el filtro y 'mode=block' indica que si se detecta un ataque XSS,
        // el navegador debe bloquear la renderizacion completa de la pagina en lugar de
        // intentar "limpiar" el contenido (que a veces puede ser explotado).
        // Nota: En navegadores modernos este header esta deprecado en favor de CSP,
        // pero se mantiene por compatibilidad con navegadores legacy.
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: strict-origin-when-cross-origin
        // Controla cuanta informacion de la URL de origen se envia en el header Referer
        // cuando el usuario navega hacia otro dominio.
        // - Mismos origen: se envia la URL completa.
        // - Cross-origin desde HTTPS a HTTPS: se envia solo el origen (sin la ruta).
        // - Cross-origin desde HTTPS a HTTP: no se envia nada (protege credenciales en query strings).
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // --- HSTS (HTTP Strict Transport Security) - Solo en produccion ---

        // Strict-Transport-Security: max-age=31536000; includeSubDomains
        // Indica al navegador que solo debe acceder al sitio mediante HTTPS durante 1 ano
        // (31536000 segundos). Incluso si el usuario escribe HTTP o hace clic en un enlace HTTP,
        // el navegador forzara la conexion HTTPS automaticamente.
        // 'includeSubDomains' extiende esta politica a todos los subdominios (api.ejemplo.com, etc.).
        // Se habilita solo en produccion porque en desarrollo el sitio puede usar HTTP local.
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // --- Permissions-Policy ---

        // Restringe el acceso a APIs sensibles del navegador que la aplicacion no necesita.
        // Cada directiva con valor vacio () deniega el acceso completamente:
        // - geolocation: impide obtener la ubicacion GPS del usuario.
        // - microphone: impide acceder al microfono del dispositivo.
        // - camera: impide acceder a la camara del dispositivo.
        // - payment: impide usar la API de pagos del navegador (Web Payment API).
        // Esto reduce la superficie de ataque y protege la privacidad del usuario.
        $response->headers->set('Permissions-Policy', 
            'geolocation=(), microphone=(), camera=(), payment=()'
        );

        // --- Content Security Policy (CSP) ---

        // CSP es el header de seguridad mas potente y complejo. Define que recursos
        // (scripts, estilos, fuentes, imagenes, conexiones, etc.) pueden cargarse
        // y desde que origenes. Bloquea la inyeccion de contenido malicioso.

        // Selecciona el esquema WebSocket segun el entorno:
        // - ws: para desarrollo local (WebSocket inseguro).
        // - wss: para produccion (WebSocket sobre TLS encriptado).
        $wsScheme = config('app.env') === 'production' ? 'wss' : 'ws';

        $cspParts = [
            // default-src 'self'
            // Politica por defecto para todos los tipos de recursos no especificados.
            // Solo permite cargar recursos desde el mismo origen de la aplicacion.
            "default-src 'self'",

            // script-src 'self' 'unsafe-inline' 'unsafe-eval' [CDNs]
            // Controla que archivos JavaScript pueden ejecutarse.
            // - 'self': scripts desde el dominio de la aplicacion.
            // - 'unsafe-inline': permite scripts inline en HTML (necesario para algunos componentes Blade).
            // - 'unsafe-eval': permite eval() y new Function() (requerido por algunas librerias JS).
            // - cdn.tailwindcss.com: CDN de Tailwind CSS (solo en desarrollo/demo).
            // - unpkg.com: CDN para paquetes npm (usado para cargar librerias en el navegador).
            // - cdn.jsdelivr.net: CDN espejo de npm/GitHub (alternativa a unpkg).
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com https://cdn.jsdelivr.net",

            // style-src 'self' 'unsafe-inline' [CDNs de estilos y fuentes]
            // Controla que hojas de estilo CSS pueden cargarse.
            // - 'self': estilos desde el dominio de la aplicacion.
            // - 'unsafe-inline': permite estilos inline en HTML (necesario para estilos dinamicos).
            // - fonts.googleapis.com: servicio de Google Fonts para cargar hojas de estilo CSS.
            // - cdn.jsdelivr/unpkg:CDNs para librerias CSS externas.
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com",

            // font-src 'self' data: [CDNs de fuentes]
            // Controla desde donde se pueden cargar fuentes web (woff, woff2, ttf, etc.).
            // - 'self': fuentes alojadas en la aplicacion.
            // - data: permite fuentes incrustadas como data URIs (comun en iconos).
            // - fonts.gstatic.com: servidor de archivos de fuentes de Google Fonts.
            // - cdn.jsdelivr/unpkg: fuentes desdeCDNs externos.
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://unpkg.com",

            // img-src 'self' data: https: blob:
            // Controla desde donde se pueden cargar imagenes.
            // - 'self': imagenes del dominio de la aplicacion.
            // - data: permite imagenes en formato data URI (comun en avatares pequenos, icons).
            // - https: permite imagenes desde cualquier servidor HTTPS (necesario para URLs externas).
            // - blob: permite imagenes generadas dinamicamente con Blob URL (comun en editores de imagenes).
            "img-src 'self' data: https: blob:",

            // connect-src 'self' [WebSockets y dominios de conexion]
            // Controla a que URLs puede hacer peticiones de red (fetch, XHR, WebSocket, EventSource).
            // - 'self': conexiones al mismo dominio.
            // - reverb.laravel.com: servicio Laravel Reverb para WebSockets en produccion.
            // - wss://reverb.laravel.com: conexion WebSocket segura a Reverb en produccion.
            // - ws://localhost:* y ws://127.0.0.1:*: conexiones WebSocket locales para desarrollo.
            // - wss://... variantes de los mismos para produccion.
            // - cdn.jsdelivr.net: conexiones a CDN para cargar scripts/modulos dinamicamente.
            "connect-src 'self' https://reverb.laravel.com wss://reverb.laravel.com {$wsScheme}://localhost:* {$wsScheme}://127.0.0.1:* https://cdn.jsdelivr.net",

            // frame-ancestors 'none'
            // Equivalente moderno a X-Frame-Options: DENY. Impide que la pagina sea
            // embebida en iframe, frame, embed o object desde cualquier origen.
            // Es la forma recomendada por CSP para prevenir clickjacking.
            "frame-ancestors 'none'",

            // base-uri 'self'
            // Controla que valores puede tener el atributo href de la etiqueta <base>.
            // Restringe a 'self' para evitar que un atacante manipule la URL base
            // y redirija todas las solicitudes relativas a un servidor malicioso.
            "base-uri 'self'",

            // form-action 'self'
            // Controla hacia que URLs pueden enviarse formularios (atributo action).
            // Restringe a 'self' para evitar que los formularios sean redirigidos
            // a servidores externos que podrian robar datos (CSRF, phishing).
            "form-action 'self'",
        ];

        // Ensambla todas las directivas CSP en un solo header con separador '; '
        $response->headers->set('Content-Security-Policy', implode('; ', $cspParts));

        return $response;
    }
}