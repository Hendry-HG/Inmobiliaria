{{-- Aviso de consentimiento de cookies (conforme a ePrivacy / RGPD / LOPDP) --}}
@php
    $consentCookie = isset($_COOKIE['mso_cookie_consent']) ? json_decode($_COOKIE['mso_cookie_consent'], true) : null;
@endphp

<div id="cookie-consent-banner"
     class="fixed bottom-0 inset-x-0 z-[100] hidden transition-all duration-500"
     aria-live="polite">
    <div class="mx-auto max-w-3xl px-4 pb-5">
        <div class="bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl shadow-2xl p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-mso-gold/15 text-mso-gold flex items-center justify-center flex-shrink-0">
                    <i class="ph ph-cookie text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-slate-800 mb-1">Utilizamos cookies</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Usamos cookies esenciales para el funcionamiento y la seguridad del sitio. Puedes aceptarlas
                        todas o solo las necesarias. Consulta nuestra
                        <a href="{{ route('legal.cookies') }}" class="text-mso-gold underline hover:underline-offset-2">Política de Cookies</a> y
                        <a href="{{ route('legal.privacy') }}" class="text-mso-gold underline hover:underline-offset-2">Política de Privacidad</a>.
                    </p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('legal.cookies') }}"
                   class="text-sm text-slate-500 hover:text-slate-700 underline underline-offset-2">Configurar</a>
                <button type="button" onclick="setCookieConsent('essential')"
                        class="px-4 py-2.5 text-sm font-semibold text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Solo necesarias
                </button>
                <button type="button" onclick="setCookieConsent('all')"
                        class="px-5 py-2.5 text-sm font-bold bg-mso-blue text-white rounded-lg hover:bg-slate-800 shadow transition-colors">
                    Aceptar todas
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    (function() {
        const COOKIE_NAME = 'mso_cookie_consent';

        function readCookie(name) {
            const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
            return match ? decodeURIComponent(match[1]) : null;
        }

        function writeCookie(name, value, days) {
            const expires = days ? '; expires=' + new Date(Date.now() + days * 864e5).toUTCString() : '';
            document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
        }

        window.setCookieConsent = function(level) {
            const payload = {
                level: level,
                necessary: true,
                analytics: level === 'all',
                preferences: level === 'all',
                date: new Date().toISOString()
            };
            writeCookie(COOKIE_NAME, JSON.stringify(payload), 365);
            const banner = document.getElementById('cookie-consent-banner');
            if (banner) {
                banner.classList.add('hidden');
            }
        };

        window.resetCookieConsent = function() {
            writeCookie(COOKIE_NAME, '', -365);
            const banner = document.getElementById('cookie-consent-banner');
            if (banner) {
                banner.classList.remove('hidden');
                banner.style.transform = 'translateY(0)';
                banner.style.opacity = '1';
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        document.addEventListener('DOMContentLoaded', function() {
            const banner = document.getElementById('cookie-consent-banner');
            if (!banner) return;

            if (!readCookie(COOKIE_NAME)) {
                banner.classList.remove('hidden');
                banner.style.opacity = '1';
                banner.style.transform = 'translateY(0)';
            }
        });
    })();
</script>
@endpush
