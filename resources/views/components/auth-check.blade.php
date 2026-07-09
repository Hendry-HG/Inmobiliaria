@props(['propertyId'])

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-24">
    <div class="bg-gradient-to-r from-mso-blue to-slate-700 h-16"></div>
    <div class="px-6 py-6 text-center">

        @auth
            <div class="mb-4">
                <p class="text-slate-600 text-sm mb-1">Hola, <span class="font-bold text-slate-800">{{ Auth::user()->full_name }}</span></p>
                <p class="text-mso-gold font-bold text-lg">¿Quieres visitar esta propiedad?</p>
            </div>

            <button onclick="toggleAppointmentForm()"
                    class="w-full bg-mso-gold text-mso-blue py-3 rounded-xl font-bold hover:bg-slate-800 hover:text-white transition-all shadow-md flex items-center justify-center gap-2 mb-4">
                <i class="ph ph-calendar-plus text-xl"></i> Agendar Visita
            </button>

            <div id="appointment-form-container" class="hidden text-left animate-fade-in border-t border-slate-100 pt-4 mt-4">
                <form action="{{ route('citas.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $propertyId }}">
                    <input type="hidden" name="date" id="authCheckDateInput">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre</label>
                        <input type="text" name="name" value="{{ Auth::user()->full_name }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-mso-gold outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-mso-gold outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono</label>
                        <input type="tel" name="phone" value="{{ Auth::user()->phone ?? '' }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-mso-gold outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fecha</label>
                            <input type="date" name="date_picker" id="authCheckDatePicker" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-mso-gold outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Hora</label>
                            <select name="time_picker" id="authCheckTimePicker" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-mso-gold outline-none">
                                <option value="">Hora</option>
                                <option value="09:00">09:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="13:00">01:00 PM</option>
                                <option value="14:00">02:00 PM</option>
                                <option value="15:00">03:00 PM</option>
                                <option value="16:00">04:00 PM</option>
                                <option value="17:00">05:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mensaje (Opcional)</label>
                        <textarea name="message" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-mso-gold outline-none resize-none"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-mso-blue text-white py-2 rounded-lg font-bold hover:bg-slate-700 transition-colors text-sm mt-2">
                        Confirmar Solicitud
                    </button>
                </form>
            </div>

        @else
            <div class="space-y-4">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mx-auto mb-2">
                    <i class="ph ph-lock-key text-3xl"></i>
                </div>

                <h3 class="text-xl font-bold text-slate-900">Agenda tu visita</h3>
                <p class="text-slate-500 text-sm">
                    Debes iniciar sesión para solicitar una cita con el asesor y guardar tus favoritos.
                </p>

                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
                   class="block w-full bg-mso-gold text-mso-blue py-3 rounded-xl font-bold hover:bg-slate-800 hover:text-white transition-all shadow-md text-center">
                    Iniciar Sesión / Registrarse
                </a>

                <p class="text-xs text-slate-400 mt-2">Es gratis y te toma menos de 1 minuto.</p>
            </div>
        @endauth
    </div>
</div>

@push('js')
<script>
    function toggleAppointmentForm() {
        const formContainer = document.getElementById('appointment-form-container');
        if(formContainer) {
            formContainer.classList.toggle('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#appointment-form-container form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const datePicker = document.getElementById('authCheckDatePicker');
                const timePicker = document.getElementById('authCheckTimePicker');
                const hiddenInput = document.getElementById('authCheckDateInput');

                if (datePicker && timePicker && hiddenInput) {
                    if(datePicker.value && timePicker.value) {
                        hiddenInput.value = datePicker.value + 'T' + timePicker.value + ':00';
                    } else {
                        e.preventDefault();
                        alert('Por favor selecciona fecha y hora para continuar.');
                    }
                }
            });
        }
    });
</script>
@endpush
