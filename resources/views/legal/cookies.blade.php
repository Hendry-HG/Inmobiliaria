{{-- Página pública de Política de Cookies --}}
@extends('layouts.landing')

@section('title', 'Política de Cookies')

@section('content')
<div class="max-w-4xl mx-auto px-6 lg:px-8 py-16">
    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-mso-gold transition-colors">Inicio</a>
        <span class="mx-2">/</span>
        <span class="text-slate-700">Política de Cookies</span>
    </nav>

    <h1 class="font-serif text-3xl md:text-4xl font-bold text-slate-900 mb-2">Política de Cookies</h1>
    <p class="text-slate-500 mb-10">Última actualización: {{ date('d \d\e F \d\e Y') }}</p>

    <div class="space-y-10 text-slate-700 leading-relaxed">

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">1. ¿Qué son las cookies?</h2>
            <p>
                Las cookies son pequeños archivos de texto que se almacenan en el dispositivo del usuario
                (ordenador, tablet o teléfono) al visitar un sitio web. Permiten al Sitio recordar información
                sobre su visita, como preferencias o el estado de su sesión, y son esenciales para el correcto
                funcionamiento de la Plataforma.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">2. Cookies utilizadas en este Sitio</h2>
            <p class="mb-4">
                En este Sitio utilizamos exclusivamente <strong>cookies técnicas o esenciales</strong>, necesarias
                para el funcionamiento de la Plataforma y la seguridad de la sesión. No instalamos cookies de
                publicidad ni de perfilado de terceros.
            </p>

            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Cookie</th>
                            <th class="px-4 py-3 font-semibold">Tipo</th>
                            <th class="px-4 py-3 font-semibold">Finalidad</th>
                            <th class="px-4 py-3 font-semibold">Duración</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">laravel_session</td>
                            <td class="px-4 py-3">Esencial</td>
                            <td class="px-4 py-3">Mantiene la sesión del usuario autenticado y la seguridad del Sitio.</td>
                            <td class="px-4 py-3">De sesión</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">XSRF-TOKEN</td>
                            <td class="px-4 py-3">Esencial</td>
                            <td class="px-4 py-3">Protege los formularios contra ataques CSRF (falsificación de solicitudes).</td>
                            <td class="px-4 py-3">De sesión</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">mso_cookie_consent</td>
                            <td class="px-4 py-3">Esencial / Preferencia</td>
                            <td class="px-4 py-3">Guarda sus preferencias de consentimiento de cookies para no volver a mostrar el aviso.</td>
                            <td class="px-4 py-3">365 días</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mt-4">
                Los recursos externos (fuentes de Google Fonts, iconos de Phosphor, estilos de Tailwind y CDN de
                imágenes) pueden realizar peticiones a servidores de terceros. Estos proveedores podrían almacenar
                datos técnicos; le recomendamos revisar sus respectivas políticas de privacidad.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">3. Base legal</h2>
            <p class="mb-3">
                El uso de cookies esenciales se ampara en la necesidad técnica para la prestación del servicio
                solicitado. Para cualquier cookie no esencial, solicitamos su consentimiento previo, conforme a:
            </p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Directiva 2002/58/CE (Directiva ePrivacy) y Reglamento (UE) 2016/679 (RGPD).</li>
                <li>Ley Orgánica de Protección de Datos Personales y Ley de Infogobierno de la República Bolivariana de Venezuela.</li>
                <li>Ley de Responsabilidad Social en Radio, Televisión y Medios Electrónicos.</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">4. ¿Cómo gestionar su consentimiento?</h2>
            <p class="mb-4">
                Al acceder al Sitio se muestra un aviso de cookies. Usted puede aceptar todas las cookies, aceptar
                únicamente las esenciales o revisar esta Política. Puede cambiar sus preferencias en cualquier momento:
            </p>

            <div class="flex flex-wrap gap-3">
                <button type="button"
                        onclick="resetCookieConsent()"
                        class="bg-mso-blue text-white px-6 py-2.5 rounded-lg hover:bg-slate-800 font-semibold transition-colors shadow">
                    Revisar mi consentimiento de cookies
                </button>
                <a href="{{ route('home') }}"
                   class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition-colors">
                    Volver al inicio
                </a>
            </div>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">5. Cómo desactivar las cookies en su navegador</h2>
            <p class="mb-3">También puede configurar su navegador para bloquear o eliminar cookies. Los pasos varían según el navegador:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Google Chrome:</strong> Configuración → Privacidad y seguridad → Cookies y otros datos de sitios.</li>
                <li><strong>Mozilla Firefox:</strong> Ajustes → Privacidad y seguridad → Cookies y datos del sitio.</li>
                <li><strong>Microsoft Edge:</strong> Configuración → Cookies y permisos del sitio.</li>
                <li><strong>Safari:</strong> Preferencias → Privacidad → Gestionar datos de sitios web.</li>
            </ul>
            <p class="mt-3">
                La desactivación de cookies esenciales puede impedir el correcto funcionamiento de la Plataforma,
                como el inicio de sesión o la gestión de citas.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">6. Actualización de esta Política</h2>
            <p>
                Podemos actualizar esta Política de Cookies para reflejar cambios normativos o técnicos. La fecha de la
                última versión se indicará al inicio de esta página.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">7. Contacto</h2>
            <p>
                Para consultas sobre el uso de cookies, contáctenos en
                <a href="mailto:msogruoinmobilirio.2023@gmail.com" class="text-mso-gold underline">msogruoinmobilirio.2023@gmail.com</a>.
            </p>
        </section>
    </div>
</div>
@endsection
