@extends('layouts.dashboard')

@section('title', 'Configuración de Agenda')
@section('header', 'Configuración de Citas')

@push('css')
<style>
    .day-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.75rem;
        transition: all 0.2s ease;
    }
    .day-card:hover {
        border-color: #c5a059;
    }
    .day-card.active {
        border-color: #c5a059;
        background: #fefcf5;
    }
    .day-card.inactive {
        opacity: 0.5;
        background: #f1f5f9;
    }
    .hour-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .hour-tag:hover {
        border-color: #c5a059;
        background: #fefcf5;
    }
    .hour-tag .remove-hour {
        color: #94a3b8;
        font-size: 0.6rem;
        cursor: pointer;
        transition: color 0.2s ease;
    }
    .hour-tag .remove-hour:hover {
        color: #ef4444;
    }
    .hour-tag.add-hour {
        border-style: dashed;
        border-color: #94a3b8;
        color: #94a3b8;
    }
    .hour-tag.add-hour:hover {
        border-color: #c5a059;
        color: #c5a059;
        background: #fefcf5;
    }
    .hour-tag.active-hour {
        border-color: #c5a059;
        background: #fefcf5;
    }
    .period-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 1rem;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        <div class="p-6 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-calendar-gear text-mso-gold"></i>
                    Configuración de Agenda
                </h3>
                <p class="text-sm text-slate-500 mt-1">Configura el límite de citas y horas disponibles para cada día</p>
            </div>
            <div>
                <span class="text-xs text-slate-400">
                    <i class="ph ph-info"></i>
                    @if($settings->is_active)
                        <span class="text-green-600">● Agenda activa</span>
                    @else
                        <span class="text-red-600">● Agenda inactiva</span>
                    @endif
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-6 mt-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
                <i class="ph ph-check-circle text-xl"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2">
                <i class="ph ph-warning-circle text-xl"></i>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('citas.configuracion.update') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-lg">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ $settings->is_active ? 'checked' : '' }}
                    class="w-5 h-5 rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
                <label for="is_active" class="text-sm font-medium text-slate-700">
                    <i class="ph ph-check-circle mr-1"></i>
                    Agenda activa (los clientes pueden agendar citas)
                </label>
            </div>

            <div class="period-card">
                <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">
                    <i class="ph ph-calendar text-slate-400 mr-2"></i> Período de Validez de la Agenda
                </h4>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="apply_always" id="apply_always" value="1"
                            {{ $settings->apply_always ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-slate-300 text-mso-gold focus:ring-mso-gold"
                            onchange="togglePeriodFields(this.checked)">
                        <label for="apply_always" class="text-sm font-medium text-slate-700">
                            Aplicar siempre (sin restricción de fechas)
                        </label>
                    </div>
                    <div id="periodFields" class="{{ $settings->apply_always ? 'hidden' : '' }} grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de inicio</label>
                            <input type="date" name="valid_from" value="{{ $settings->valid_from ? $settings->valid_from->format('Y-m-d') : '' }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de fin</label>
                            <input type="date" name="valid_to" value="{{ $settings->valid_to ? $settings->valid_to->format('Y-m-d') : '' }}"
                                   class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Si no seleccionas fechas específicas, la configuración se aplicará siempre.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-clock text-slate-400 mr-1"></i>
                        Duración por cita (minutos)
                    </label>
                    <select name="slot_duration" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                        <option value="15" {{ $settings->slot_duration == 15 ? 'selected' : '' }}>15 minutos</option>
                        <option value="30" {{ $settings->slot_duration == 30 ? 'selected' : '' }}>30 minutos</option>
                        <option value="45" {{ $settings->slot_duration == 45 ? 'selected' : '' }}>45 minutos</option>
                        <option value="60" {{ $settings->slot_duration == 60 ? 'selected' : '' }}>60 minutos</option>
                        <option value="90" {{ $settings->slot_duration == 90 ? 'selected' : '' }}>90 minutos</option>
                        <option value="120" {{ $settings->slot_duration == 120 ? 'selected' : '' }}>120 minutos</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-clock-counter-clockwise text-slate-400 mr-1"></i>
                        Tiempo entre citas (minutos)
                    </label>
                    <input type="number" name="break_duration"
                           value="{{ $settings->break_duration }}"
                           min="0" max="60"
                           class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <i class="ph ph-bell text-slate-400 mr-1"></i>
                        Recordatorio (minutos antes)
                    </label>
                    <select name="reminder_minutes" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                        <option value="15" {{ $settings->reminder_minutes == 15 ? 'selected' : '' }}>15 minutos</option>
                        <option value="30" {{ $settings->reminder_minutes == 30 ? 'selected' : '' }}>30 minutos</option>
                        <option value="60" {{ $settings->reminder_minutes == 60 ? 'selected' : '' }}>1 hora</option>
                        <option value="120" {{ $settings->reminder_minutes == 120 ? 'selected' : '' }}>2 horas</option>
                        <option value="1440" {{ $settings->reminder_minutes == 1440 ? 'selected' : '' }}>1 día</option>
                    </select>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b flex items-center justify-between">
                    <span><i class="ph ph-calendar text-slate-400 mr-2"></i> Configuración por Día</span>
                    <span class="text-xs text-slate-400 font-normal">Configura el límite de citas y horas para cada día</span>
                </h4>

                @php
                    $days = [
                        'monday' => 'Lunes',
                        'tuesday' => 'Martes',
                        'wednesday' => 'Miércoles',
                        'thursday' => 'Jueves',
                        'friday' => 'Viernes',
                        'saturday' => 'Sábado',
                        'sunday' => 'Domingo',
                    ];
                    $dailyConfig = $settings->daily_config ?? [];
                    $allHours = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00'];
                @endphp

                <div class="space-y-4">
                    @foreach($days as $key => $label)
                    @php
                        $dayConfig = $dailyConfig[$key] ?? ['max' => 0, 'hours' => []];
                        $max = $dayConfig['max'] ?? 0;
                        $hours = $dayConfig['hours'] ?? [];
                        $isActive = $max > 0;
                    @endphp
                    <div class="day-card {{ $isActive ? 'active' : 'inactive' }}" data-day="{{ $key }}">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                        <input type="checkbox"
                                               class="day-toggle rounded border-slate-300 text-mso-gold focus:ring-mso-gold"
                                               data-day="{{ $key }}"
                                               {{ $isActive ? 'checked' : '' }}
                                               onchange="toggleDay('{{ $key }}', this.checked)">
                                        <span class="font-bold">{{ $label }}</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400">Límite de citas:</span>
                                        <input type="number"
                                               name="daily_config[{{ $key }}][max]"
                                               value="{{ $max }}"
                                               min="0" max="50"
                                               class="w-16 border border-slate-200 rounded px-2 py-1 text-sm text-center focus:ring-2 focus:ring-mso-gold focus:border-mso-gold"
                                               id="max-input-{{ $key }}"
                                               onchange="updateDayStatus('{{ $key }}')">
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400" id="hours-count-{{ $key }}">
                                    {{ count($hours) }} horas configuradas
                                </span>
                            </div>

                            <div class="pl-8">
                                <div class="flex flex-wrap gap-1.5" id="hours-container-{{ $key }}">
                                    @foreach($hours as $hour)
                                    <span class="hour-tag active-hour" data-hour="{{ $hour }}">
                                        {{ $hour }}
                                        <span class="remove-hour" onclick="removeHour('{{ $key }}', '{{ $hour }}')">
                                            <i class="ph ph-x"></i>
                                        </span>
                                        <input type="hidden" name="daily_config[{{ $key }}][hours][]" value="{{ $hour }}">
                                    </span>
                                    @endforeach
                                    <span class="hour-tag add-hour" onclick="showAddHourInput('{{ $key }}')">
                                        <i class="ph ph-plus"></i> Agregar hora
                                    </span>
                                </div>
                                <div id="add-hour-input-{{ $key }}" class="hidden flex items-center gap-1.5 mt-1.5">
                                    <select id="new-hour-{{ $key }}" class="border border-slate-200 rounded px-2 py-1 text-sm w-32 focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                                        <option value="">Seleccionar hora...</option>
                                        @foreach($allHours as $hour)
                                            <option value="{{ $hour }}">{{ $hour }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="addHour('{{ $key }}')"
                                            class="bg-mso-blue text-white px-2 py-1 rounded text-xs hover:bg-slate-800 transition-colors">
                                        <i class="ph ph-check"></i> Agregar
                                    </button>
                                    <button type="button" onclick="hideAddHourInput('{{ $key }}')"
                                            class="text-slate-400 hover:text-slate-600 text-sm">
                                        <i class="ph ph-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-bold text-slate-700 pb-2 border-b flex-1">
                        <i class="ph ph-calendar-x text-slate-400 mr-2"></i> Días No Laborables (Excepciones)
                    </h4>
                    <button type="button" onclick="addExceptionRow()" class="text-sm bg-mso-blue text-white px-3 py-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                        <i class="ph ph-plus"></i> Agregar
                    </button>
                </div>
                <div id="exceptions-container">
                    @php $exceptions = $settings->exceptions ?? []; @endphp
                    @if(count($exceptions) > 0)
                        @foreach($exceptions as $index => $exception)
                        <div class="exception-row flex items-center gap-3 mb-2 p-3 bg-slate-50 rounded-lg">
                            <input type="date" name="exceptions[{{ $index }}][date]" value="{{ $exception['date'] }}"
                                   class="border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                            <input type="text" name="exceptions[{{ $index }}][reason]" value="{{ $exception['reason'] }}"
                                   placeholder="Motivo (ej: Vacaciones)"
                                   class="flex-1 border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
                            <button type="button" onclick="this.closest('.exception-row').remove()" class="text-red-500 hover:text-red-700">
                                <i class="ph ph-x text-xl"></i>
                            </button>
                        </div>
                        @endforeach
                    @else
                        <p class="text-sm text-slate-400">No hay días no laborables registrados.</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-lg">
                <input type="checkbox" name="notify_client" id="notify_client" value="1"
                    {{ $settings->notify_client ? 'checked' : '' }}
                    class="w-5 h-5 rounded border-slate-300 text-mso-gold focus:ring-mso-gold">
                <label for="notify_client" class="text-sm font-medium text-slate-700">
                    <i class="ph ph-bell-simple mr-1"></i>
                    Notificar al cliente cuando se agende o cambie una cita
                </label>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="{{ route('citas.index') }}" class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-mso-blue text-white px-8 py-2.5 rounded-lg hover:bg-slate-800 font-bold shadow-lg transition-all hover:-translate-y-0.5">
                    <i class="ph ph-floppy-disk mr-1"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    function togglePeriodFields(checked) {
        const periodFields = document.getElementById('periodFields');
        if (checked) {
            periodFields.classList.add('hidden');
        } else {
            periodFields.classList.remove('hidden');
        }
    }

    function toggleDay(day, checked) {
        const card = document.querySelector(`.day-card[data-day="${day}"]`);
        const maxInput = document.getElementById(`max-input-${day}`);

        if (checked) {
            card.classList.remove('inactive');
            card.classList.add('active');
            if (parseInt(maxInput.value) === 0) {
                maxInput.value = 5;
            }
        } else {
            card.classList.remove('active');
            card.classList.add('inactive');
            maxInput.value = 0;
            const container = document.getElementById(`hours-container-${day}`);
            container.querySelectorAll('.hour-tag:not(.add-hour)').forEach(el => el.remove());
            document.getElementById(`hours-count-${day}`).textContent = '0 horas configuradas';
        }
    }

    function updateDayStatus(day) {
        const card = document.querySelector(`.day-card[data-day="${day}"]`);
        const maxInput = document.getElementById(`max-input-${day}`);
        const checkbox = card.querySelector('.day-toggle');
        const max = parseInt(maxInput.value) || 0;

        if (max > 0) {
            card.classList.remove('inactive');
            card.classList.add('active');
            checkbox.checked = true;
        } else {
            card.classList.remove('active');
            card.classList.add('inactive');
            checkbox.checked = false;
            const container = document.getElementById(`hours-container-${day}`);
            container.querySelectorAll('.hour-tag:not(.add-hour)').forEach(el => el.remove());
            document.getElementById(`hours-count-${day}`).textContent = '0 horas configuradas';
        }
    }

    function showAddHourInput(day) {
        document.getElementById(`add-hour-input-${day}`).classList.remove('hidden');
        document.getElementById(`new-hour-${day}`).focus();
    }

    function hideAddHourInput(day) {
        document.getElementById(`add-hour-input-${day}`).classList.add('hidden');
        document.getElementById(`new-hour-${day}`).value = '';
    }

    function addHour(day) {
        const select = document.getElementById(`new-hour-${day}`);
        const hour = select.value;

        if (!hour) {
            alert('Selecciona una hora válida.');
            return;
        }

        const container = document.getElementById(`hours-container-${day}`);
        const maxInput = document.getElementById(`max-input-${day}`);
        const max = parseInt(maxInput.value) || 0;

        if (max === 0) {
            alert('Primero configura el límite de citas para este día.');
            return;
        }

        const existing = container.querySelectorAll('.hour-tag:not(.add-hour)');
        for (let el of existing) {
            if (el.textContent.trim() === hour) {
                alert('Esta hora ya está agregada.');
                select.value = '';
                hideAddHourInput(day);
                return;
            }
        }

        const tag = document.createElement('span');
        tag.className = 'hour-tag active-hour';
        tag.dataset.hour = hour;
        tag.innerHTML = `
            ${hour}
            <span class="remove-hour" onclick="removeHour('${day}', '${hour}')">
                <i class="ph ph-x"></i>
            </span>
            <input type="hidden" name="daily_config[${day}][hours][]" value="${hour}">
        `;

        const addBtn = container.querySelector('.add-hour');
        container.insertBefore(tag, addBtn);

        const count = container.querySelectorAll('.hour-tag:not(.add-hour)').length;
        document.getElementById(`hours-count-${day}`).textContent = `${count} horas configuradas`;

        select.value = '';
        hideAddHourInput(day);
    }

    function removeHour(day, hour) {
        const container = document.getElementById(`hours-container-${day}`);
        const tags = container.querySelectorAll('.hour-tag:not(.add-hour)');
        for (let tag of tags) {
            if (tag.dataset.hour === hour) {
                tag.remove();
                break;
            }
        }
        const count = container.querySelectorAll('.hour-tag:not(.add-hour)').length;
        document.getElementById(`hours-count-${day}`).textContent = `${count} horas configuradas`;
    }

    function addExceptionRow() {
        const container = document.getElementById('exceptions-container');
        const rowCount = container.querySelectorAll('.exception-row').length;

        const emptyMsg = container.querySelector('p.text-slate-400');
        if (emptyMsg) emptyMsg.remove();

        const row = document.createElement('div');
        row.className = 'exception-row flex items-center gap-3 mb-2 p-3 bg-slate-50 rounded-lg';
        row.innerHTML = `
            <input type="date" name="exceptions[${rowCount}][date]"
                   class="border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
            <input type="text" name="exceptions[${rowCount}][reason]"
                   placeholder="Motivo (ej: Vacaciones)"
                   class="flex-1 border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-mso-gold focus:border-mso-gold">
            <button type="button" onclick="this.closest('.exception-row').remove()" class="text-red-500 hover:text-red-700">
                <i class="ph ph-x text-xl"></i>
            </button>
        `;
        container.appendChild(row);
    }
</script>
@endpush
@endsection
