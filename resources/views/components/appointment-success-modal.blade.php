{{--
    Modal de éxito al agendar una cita.

    Se incluye UNA vez por página en los formularios de agendar cita
    (modulos/catalogo/show.blade.php y modulos/propiedades/show.blade.php).

    API pública:
      - window.showAppointmentSuccessModal(message)
      - window.closeAppointmentSuccessModal()
--}}
@once('appointment-success-modal')

<style>
    #appt-success-overlay {
        position: fixed;
        inset: 0;
        z-index: 9998;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        padding: 1rem;
    }
    #appt-success-overlay.appt-open {
        display: flex;
    }
    #appt-success-card {
        background: #ffffff;
        border-radius: 16px;
        max-width: 420px;
        width: 100%;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        animation: appt-success-in .25s ease-out;
        text-align: center;
    }
    @keyframes appt-success-in {
        from { opacity: 0; transform: translateY(14px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    #appt-success-icon {
        width: 4rem;
        height: 4rem;
        margin: 2rem auto 1rem;
        border-radius: 9999px;
        background: #ecfdf5;
        color: #16a34a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
    }
    #appt-success-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 1.5rem .5rem;
    }
    #appt-success-message {
        font-size: .9rem;
        color: #475569;
        line-height: 1.5;
        margin: 0 1.5rem 1.5rem;
    }
    #appt-success-actions {
        padding: 1rem 1.5rem 1.5rem;
        display: flex;
        flex-direction: column;
        gap: .6rem;
    }
    #appt-success-actions a {
        background: #c5a059;
        color: #0f172a;
        font-weight: 700;
        font-size: .9rem;
        padding: .7rem 1.5rem;
        border-radius: 10px;
        text-decoration: none;
        transition: background .2s ease;
    }
    #appt-success-actions a:hover {
        background: #b8923f;
    }
    #appt-success-actions button {
        background: #f1f5f9;
        color: #334155;
        font-weight: 600;
        font-size: .85rem;
        padding: .65rem 1.5rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background .2s ease;
    }
    #appt-success-actions button:hover {
        background: #e2e8f0;
    }
</style>

<div id="appt-success-overlay" aria-hidden="true">
    <div id="appt-success-card" role="dialog" aria-modal="true" aria-labelledby="appt-success-title">
        <div id="appt-success-icon"><i class="ph ph-check-circle"></i></div>
        <h3 id="appt-success-title">¡Cita Agendada!</h3>
        <p id="appt-success-message"></p>
        <div id="appt-success-actions">
            <a href="{{ route('citas.index') }}" id="appt-success-link">Ver mis citas</a>
            <button type="button" id="appt-success-close">Seguir navegando</button>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        function showAppointmentSuccessModal(message) {
            var overlay = document.getElementById('appt-success-overlay');
            var messageEl = document.getElementById('appt-success-message');
            if (!overlay || !messageEl) return;
            messageEl.textContent = message || 'Tu cita ha sido registrada.';
            overlay.classList.add('appt-open');
            overlay.setAttribute('aria-hidden', 'false');
        }

        function closeAppointmentSuccessModal() {
            var overlay = document.getElementById('appt-success-overlay');
            if (overlay) {
                overlay.classList.remove('appt-open');
                overlay.setAttribute('aria-hidden', 'true');
            }
        }

        window.showAppointmentSuccessModal = showAppointmentSuccessModal;
        window.closeAppointmentSuccessModal = closeAppointmentSuccessModal;

        var overlay = document.getElementById('appt-success-overlay');
        if (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === this) closeAppointmentSuccessModal();
            });
        }

        var closeBtn = document.getElementById('appt-success-close');
        if (closeBtn) closeBtn.addEventListener('click', closeAppointmentSuccessModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAppointmentSuccessModal();
        });
    })();
</script>
@endonce
