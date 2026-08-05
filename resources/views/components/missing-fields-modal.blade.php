{{--
    Modal global de "campos incompletos".

    Se incluye UNA vez por página en los layouts (landing y dashboard).
    El JS se auto-asocia a TODOS los formularios del documento (incluidos
    los que se inyectan por AJAX, gracias a la delegación de eventos en
    la fase de captura sobre document).

    Si un formulario maneja su propia validación y no debe usar este modal,
    añade el atributo:  data-no-missing-modal  a la etiqueta <form>.

    API pública:
      - window.showMissingFieldsModal(items)  // items: array de strings o {labels:[...], first:elemento}
      - window.closeMissingFieldsModal()
--}}
@once('missing-fields-modal')

<style>
    #mf-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        padding: 1rem;
    }
    #mf-modal-overlay.mf-open {
        display: flex;
    }
    #mf-modal {
        background: #ffffff;
        border-radius: 16px;
        max-width: 420px;
        width: 100%;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        animation: mf-fade-up .2s ease-out;
    }
    @keyframes mf-fade-up {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    #mf-modal-header {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: 1.25rem 1.5rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }
    #mf-modal-icon {
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 9999px;
        background: #fef2f2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    #mf-modal-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 .15rem;
    }
    #mf-modal-subtitle {
        font-size: .8rem;
        color: #64748b;
        margin: 0;
    }
    #mf-modal-body {
        padding: 1.1rem 1.5rem;
        max-height: 50vh;
        overflow-y: auto;
    }
    #mf-missing-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }
    #mf-missing-list li {
        display: flex;
        align-items: center;
        gap: .55rem;
        font-size: .85rem;
        color: #1e293b;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: .5rem .75rem;
    }
    #mf-missing-list li i {
        color: #dc2626;
        font-size: 1rem;
        flex-shrink: 0;
    }
    #mf-modal-footer {
        padding: 1rem 1.5rem 1.25rem;
        text-align: right;
    }
    #mf-modal-close {
        background: #c5a059;
        color: #0f172a;
        font-weight: 700;
        font-size: .85rem;
        padding: .6rem 1.5rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background .2s ease;
    }
    #mf-modal-close:hover {
        background: #b8923f;
    }
</style>

<div id="mf-modal-overlay" aria-hidden="true">
    <div id="mf-modal" role="dialog" aria-modal="true" aria-labelledby="mf-modal-title">
        <div id="mf-modal-header">
            <div id="mf-modal-icon"><i class="ph ph-warning-circle"></i></div>
            <div>
                <h3 id="mf-modal-title">Faltan campos por completar</h3>
                <p id="mf-modal-subtitle">Revisa los campos marcados para continuar:</p>
            </div>
        </div>
        <div id="mf-modal-body">
            <ul id="mf-missing-list"></ul>
        </div>
        <div id="mf-modal-footer">
            <button type="button" id="mf-modal-close">Entendido</button>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        function cleanText(node) {
            return (node.textContent || '')
                .replace(/\s*\*\s*$/, '')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function getFieldLabel(el) {
            if (el.dataset && el.dataset.label) return el.dataset.label;

            if (el.id) {
                var label = null;
                try {
                    label = document.querySelector('label[for="' + CSS.escape(el.id) + '"]');
                } catch (e) { label = null; }
                if (label) {
                    var forText = cleanText(label);
                    if (forText) return forText;
                }
            }

            var wrap = el.closest ? el.closest('label') : null;
            if (wrap) {
                var clone = wrap.cloneNode(true);
                if (clone.querySelectorAll) {
                    clone.querySelectorAll('input,select,textarea,button').forEach(function (n) { n.remove(); });
                }
                var wrappedText = cleanText(clone);
                if (wrappedText) return wrappedText;
            }

            if (el.placeholder && el.placeholder.trim()) return el.placeholder.trim();

            if (el.name) {
                return el.name
                    .replace(/[\[\]]/g, ' ')
                    .replace(/[_.]+/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim()
                    .replace(/\b\w/g, function (c) { return c.toUpperCase(); });
            }

            return 'Campo requerido';
        }

        function isVisible(el) {
            while (el && el.nodeType === 1) {
                if (el.hidden) return false;
                if (el.style && (el.style.display === 'none' || el.style.visibility === 'hidden')) return false;
                if (el.classList && (el.classList.contains('hidden') || el.classList.contains('d-none'))) return false;
                el = el.parentElement;
            }
            return true;
        }

        function collectMissingFields(form) {
            var missing = [];
            var groups = {};
            var first = null;
            var fields = form.querySelectorAll('input, select, textarea');

            for (var i = 0; i < fields.length; i++) {
                var el = fields[i];
                var hasDataLabel = !!(el.dataset && el.dataset.label);
                if (el.disabled && !hasDataLabel) continue;

                var type = (el.type || '').toLowerCase();
                if (type === 'submit' || type === 'button' || type === 'reset' || type === 'image') continue;
                if (type === 'hidden' && !hasDataLabel) continue;

                var checkable = (type === 'checkbox' || type === 'radio');
                if (checkable && !el.required) continue;
                if (!checkable && !el.required) continue;
                if (!isVisible(el)) continue;

                if (checkable) {
                    var groupName = el.name;
                    if (!groupName) continue;
                    if (!groups[groupName]) groups[groupName] = { checked: false, el: el };
                    if (el.checked) groups[groupName].checked = true;
                    continue;
                }

                var value = (el.value || '').trim();
                var label = hasDataLabel ? el.dataset.label : getFieldLabel(el);

                if (type === 'email' || /email/i.test(el.name || '')) {
                    if (!value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        missing.push(label);
                        if (!first) first = el;
                    }
                    continue;
                }

                if (!value) {
                    missing.push(label);
                    if (!first) first = el;
                    continue;
                }

                var minlen = el.getAttribute('minlength');
                if (minlen && type !== 'number' && value.length < parseInt(minlen, 10)) {
                    missing.push(label);
                    if (!first) first = el;
                    continue;
                }

                var pattern = el.getAttribute('pattern');
                if (pattern) {
                    try {
                        var re = new RegExp('^(?:' + pattern + ')$');
                        if (!re.test(value)) {
                            missing.push(label);
                            if (!first) first = el;
                        }
                    } catch (e) { /* regex inválida: se ignora */ }
                }
            }

            for (var k in groups) {
                if (groups.hasOwnProperty(k) && !groups[k].checked) {
                    missing.push(getFieldLabel(groups[k].el));
                    if (!first) first = groups[k].el;
                }
            }

            return { labels: missing, first: first };
        }

        function showMissingFieldsModal(items) {
            var overlay = document.getElementById('mf-modal-overlay');
            var list = document.getElementById('mf-missing-list');
            if (!overlay || !list) return;

            var labels = Array.isArray(items) ? items : (items && items.labels ? items.labels : []);

            list.innerHTML = '';
            labels.forEach(function (label) {
                if (!label) return;
                label = label.charAt(0).toUpperCase() + label.slice(1);
                var li = document.createElement('li');
                var icon = document.createElement('i');
                icon.className = 'ph ph-x-circle';
                li.appendChild(icon);
                li.appendChild(document.createTextNode(label));
                list.appendChild(li);
            });

            overlay.classList.add('mf-open');
            overlay.setAttribute('aria-hidden', 'false');

            if (items && items.first && items.first.focus) {
                try { items.first.focus(); } catch (e) { /* ignore */ }
            }
        }

        function closeMissingFieldsModal() {
            var overlay = document.getElementById('mf-modal-overlay');
            if (overlay) {
                overlay.classList.remove('mf-open');
                overlay.setAttribute('aria-hidden', 'true');
            }
        }

        function handleSubmit(e) {
            var form = e.target && e.target.closest ? e.target.closest('form') : null;
            if (!form) return;
            if (form.hasAttribute('data-no-missing-modal')) return;

            var result = collectMissingFields(form);
            if (result.labels.length > 0) {
                e.preventDefault();
                e.stopImmediatePropagation();
                showMissingFieldsModal(result);
            }
        }

        window.showMissingFieldsModal = showMissingFieldsModal;
        window.closeMissingFieldsModal = closeMissingFieldsModal;

        document.addEventListener('submit', handleSubmit, true);

        var overlay = document.getElementById('mf-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === this) closeMissingFieldsModal();
            });
        }

        var closeBtn = document.getElementById('mf-modal-close');
        if (closeBtn) closeBtn.addEventListener('click', closeMissingFieldsModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMissingFieldsModal();
        });
    })();
</script>
@endonce
