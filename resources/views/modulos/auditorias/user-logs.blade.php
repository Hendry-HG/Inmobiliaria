@extends('layouts.dashboard')

@section('title', 'Auditoría - Logs de Usuarios')
@section('header', 'Logs de Usuarios')

@push('styles')
<style>
    .user-search-result:hover {
        background-color: #f1f5f9;
    }
    .user-search-result.active {
        background-color: #fef3c7;
        border-left: 3px solid #c9a84c;
    }
    .audit-row:hover {
        background-color: #f8fafc;
    }
    .highlight-text {
        background-color: #fef3c7;
        padding: 0 2px;
        border-radius: 2px;
    }
    #userSearchResults::-webkit-scrollbar {
        width: 6px;
    }
    #userSearchResults::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    #userSearchResults::-webkit-scrollbar-thumb {
        background: #c9a84c;
        border-radius: 3px;
    }
    #userSearchResults::-webkit-scrollbar-thumb:hover {
        background: #b8943a;
    }
    .description-cell {
        max-width: 400px;
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.4;
    }
    .description-cell .highlight-change {
        background-color: #fef3c7;
        padding: 1px 4px;
        border-radius: 3px;
        font-weight: 500;
    }
    .description-cell .action-created {
        color: #16a34a;
        font-weight: 600;
    }
    .description-cell .action-updated {
        color: #2563eb;
        font-weight: 600;
    }
    .description-cell .action-deleted {
        color: #dc2626;
        font-weight: 600;
    }
    .description-cell .action-login {
        color: #7c3aed;
        font-weight: 600;
    }
    .description-cell .action-logout {
        color: #64748b;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ============================================ --}}
    {{-- FILTROS Y BUSCADOR DE USUARIOS --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form action="{{ route('audit-logs.user-logs') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            {{-- Buscador de usuarios --}}
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    <i class="ph ph-magnifying-glass text-mso-gold"></i> Buscar Usuario
                </label>
                <div class="relative">
                    <input type="text"
                           id="userSearchInput"
                           placeholder="Buscar por nombre o email..."
                           class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none pr-10"
                           autocomplete="off"
                           value="{{ request('user_id') && isset($selectedUser) ? $selectedUser->full_name . ' (' . $selectedUser->email . ')' : '' }}">
                    <div id="userSearchResults" class="absolute z-50 w-full bg-white border rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                        <!-- Resultados dinámicos -->
                    </div>
                    <input type="hidden" name="user_id" id="selectedUserId" value="{{ request('user_id') }}">
                    @if(request('user_id'))
                    <button type="button"
                            onclick="limpiarUsuarioSeleccionado()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 transition-colors">
                        <i class="ph ph-x-circle text-lg"></i>
                    </button>
                    @endif
                </div>
            </div>

            {{-- Fechas --}}
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded-lg p-2.5">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded-lg p-2.5">
            </div>

            {{-- Botones --}}
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="bg-mso-blue text-white px-6 py-2.5 rounded-lg hover:bg-slate-800 transition-colors flex-1 flex items-center justify-center gap-2">
                    <i class="ph ph-funnel"></i> Filtrar
                </button>
                <a href="{{ route('audit-logs.user-logs') }}" class="px-4 py-2.5 border rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-1">
                    <i class="ph ph-x"></i>
                </a>
            </div>
        </form>

        {{-- Tags de filtros activos --}}
        @php
            $activeFilters = [];
            if(request('user_id')) {
                $user = $users->firstWhere('id', request('user_id'));
                if($user) $activeFilters[] = ['label' => 'Usuario: ' . $user->full_name, 'param' => 'user_id'];
            }
            if(request('date_from')) $activeFilters[] = ['label' => 'Desde: ' . date('d/m/Y', strtotime(request('date_from'))), 'param' => 'date_from'];
            if(request('date_to')) $activeFilters[] = ['label' => 'Hasta: ' . date('d/m/Y', strtotime(request('date_to'))), 'param' => 'date_to'];
        @endphp

        @if(count($activeFilters) > 0)
        <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-slate-100">
            <span class="text-xs text-slate-500 font-medium">Filtros activos:</span>
            @foreach($activeFilters as $filter)
            <span class="inline-flex items-center gap-1 bg-mso-gold/10 text-mso-blue text-xs px-2.5 py-1 rounded-full">
                {{ $filter['label'] }}
                <button type="button"
                        onclick="eliminarFiltro('{{ $filter['param'] }}')"
                        class="hover:text-red-500 transition-colors ml-0.5">
                    <i class="ph ph-x text-xs font-bold"></i>
                </button>
            </span>
            @endforeach
            <button type="button"
                    onclick="limpiarTodosFiltros()"
                    class="text-xs text-red-500 hover:text-red-700 font-medium">
                Limpiar todos
            </button>
        </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- USUARIO SELECCIONADO - HISTORIAL --}}
    {{-- ============================================ --}}
    @if(request('user_id') && isset($selectedUser))
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-mso-blue to-slate-700 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                    {{ $selectedUser->initials }}
                </div>
                <div>
                    <h3 class="font-bold text-lg">{{ $selectedUser->full_name }}</h3>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-blue-200">
                        <span>{{ $selectedUser->email }}</span>
                        <span class="w-1 h-1 bg-blue-300 rounded-full"></span>
                        <span>{{ $selectedUser->main_role }}</span>
                        @if($selectedUser->phone)
                        <span class="w-1 h-1 bg-blue-300 rounded-full"></span>
                        <span><i class="ph ph-phone"></i> {{ $selectedUser->phone }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-medium">
                    {{ $userHistory->total() ?? 0 }} acciones
                </span>
                <a href="{{ route('audit-logs.user-logs') }}" class="text-white/80 hover:text-white text-sm flex items-center gap-1 transition-colors">
                    <i class="ph ph-x"></i> Limpiar
                </a>
            </div>
        </div>

        <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
            @forelse($userHistory ?? [] as $log)
            <div class="px-6 py-3 flex items-start gap-4 hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 flex-shrink-0 mt-0.5">
                    <i class="ph
                        @if(str_contains($log->action ?? $log->event, 'create')) ph-plus-circle text-green-500
                        @elseif(str_contains($log->action ?? $log->event, 'update')) ph-pencil-simple text-blue-500
                        @elseif(str_contains($log->action ?? $log->event, 'delete')) ph-trash text-red-500
                        @elseif(str_contains($log->action ?? $log->event, 'login')) ph-sign-in text-purple-500
                        @elseif(str_contains($log->action ?? $log->event, 'logout')) ph-sign-out text-gray-500
                        @else ph-dots-three text-slate-400 @endif
                    "></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-slate-700 description-cell">
                        <span class="font-medium">{{ $log->user ? $log->user->full_name : 'Sistema' }}</span>
                        <span class="text-slate-600">{{ $log->description ?? 'Realizó una acción' }}</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400 mt-0.5">
                        <span><i class="ph ph-clock"></i> {{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i:s') : '' }}</span>
                        @if($log->subject_type)
                            <span>· {{ class_basename($log->subject_type) }}</span>
                        @endif
                        @if($log->subject_id)
                            <span>· ID: #{{ $log->subject_id }}</span>
                        @endif
                        @if($log->ip_address)
                            <span>· IP: {{ $log->ip_address }}</span>
                        @endif
                    </div>
                </div>
                <span class="px-2 py-1 rounded text-xs font-medium whitespace-nowrap flex-shrink-0
                    @if(str_contains($log->action ?? $log->event, 'create')) bg-green-100 text-green-700
                    @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-100 text-blue-700
                    @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-100 text-red-700
                    @elseif(str_contains($log->action ?? $log->event, 'login')) bg-purple-100 text-purple-700
                    @elseif(str_contains($log->action ?? $log->event, 'logout')) bg-gray-100 text-gray-700
                    @else bg-slate-100 text-slate-700 @endif">
                    {{ ucfirst(str_replace('_', ' ', $log->action ?? $log->event ?? 'Desconocido')) }}
                </span>
            </div>
            @empty
            <div class="px-6 py-12 text-center text-slate-400">
                <i class="ph ph-inbox text-4xl block mb-3"></i>
                <p class="font-medium">No hay historial para este usuario</p>
                <p class="text-sm">El usuario no ha realizado ninguna acción registrada</p>
            </div>
            @endforelse
        </div>

        @if(isset($userHistory) && $userHistory->hasPages())
        <div class="px-6 py-3 border-t border-slate-200 bg-slate-50">
            {{ $userHistory->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- TABLA DE LOGS GENERAL --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h3 class="font-serif text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-list-checks text-mso-gold"></i>
                    Registro de Actividad
                </h3>
                <p class="text-xs text-slate-400">{{ $logs->total() }} registros encontrados</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('audit-logs.export') }}?{{ http_build_query(request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-xs font-medium hover:bg-green-100 transition-colors border border-green-200">
                    <i class="ph ph-download-simple"></i> Exportar
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="auditTable">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Usuario</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Acción</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Entidad</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Descripción</th>
                        <th class="px-6 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="audit-row hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800 text-sm">{{ $log->user ? $log->user->full_name : 'Sistema' }}</p>
                                    @if($log->user)
                                    <p class="text-[10px] text-slate-400">{{ $log->user->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[10px] font-bold uppercase
                                @if(str_contains($log->action ?? $log->event, 'create')) bg-green-100 text-green-700
                                @elseif(str_contains($log->action ?? $log->event, 'update')) bg-blue-100 text-blue-700
                                @elseif(str_contains($log->action ?? $log->event, 'delete')) bg-red-100 text-red-700
                                @elseif(str_contains($log->action ?? $log->event, 'login')) bg-purple-100 text-purple-700
                                @elseif(str_contains($log->action ?? $log->event, 'logout')) bg-gray-100 text-gray-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $log->action ?? $log->event ?? 'Desconocido')) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-xs text-slate-500">
                            @if($log->subject_type)
                                <span class="inline-flex items-center gap-1">
                                    @php
                                        $icon = match(class_basename($log->subject_type)) {
                                            'Property' => 'ph-buildings',
                                            'Appointment' => 'ph-calendar-check',
                                            'User' => 'ph-user',
                                            'Lead' => 'ph-target',
                                            'SiteConfiguration' => 'ph-gear',
                                            'Role' => 'ph-shield-check',
                                            'Permission' => 'ph-lock-key',
                                            default => 'ph-cube'
                                        };
                                    @endphp
                                    <i class="ph {{ $icon }} text-mso-gold"></i>
                                    {{ class_basename($log->subject_type) }}
                                </span>
                                @if($log->subject_id)
                                    <span class="text-slate-400">#{{ $log->subject_id }}</span>
                                @endif
                            @else
                                <span class="text-slate-400">Sistema</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-sm text-slate-600 description-cell max-w-xs" title="{{ $log->description ?? '—' }}">
                            {{ $log->description ?? '—' }}
                        </td>
                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                            <span class="text-xs font-medium text-slate-700 block">
                                {{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y') : '' }}
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('H:i:s') : '' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-slate-400">
                            <i class="ph ph-inbox text-5xl block mb-4 text-slate-300"></i>
                            <p class="font-medium text-slate-600">No hay registros de actividad</p>
                            <p class="text-sm">Los logs de usuarios aparecerán aquí cuando se realicen acciones</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $logs->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

@push('js')
<script>
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    // ============================================
    // BUSCADOR DE USUARIOS EN TIEMPO REAL
    // ============================================
    const searchInput = document.getElementById('userSearchInput');
    const resultsContainer = document.getElementById('userSearchResults');
    const selectedUserId = document.getElementById('selectedUserId');

    let searchTimeout;
    let currentAbortController = null;

    // URL correcta para buscar usuarios
    const searchUrl = '{{ route("audit-logs.search-users") }}';

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            // Cancelar petición anterior
            if (currentAbortController) {
                currentAbortController.abort();
                currentAbortController = null;
            }

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                resultsContainer.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                // Crear nuevo AbortController
                currentAbortController = new AbortController();

                fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                    signal: currentAbortController.signal
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(users => {
                    currentAbortController = null;

                    if (users.length === 0) {
                        resultsContainer.innerHTML = `
                            <div class="px-4 py-3 text-sm text-slate-500 text-center">
                                No se encontraron usuarios
                            </div>
                        `;
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    let html = '';
                    users.forEach(user => {
                        const isSelected = selectedUserId.value == user.id;
                        html += `
                            <div class="user-search-result px-4 py-2.5 cursor-pointer flex items-center gap-3 ${isSelected ? 'active' : ''} hover:bg-slate-50 transition-colors"
                                 data-user-id="${user.id}"
                                 data-user-name="${escapeHtml(user.name)}"
                                 data-user-email="${escapeHtml(user.email)}"
                                 onclick="selectUser(this)">
                                <div class="w-8 h-8 rounded-full bg-mso-blue text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    ${escapeHtml(user.initials || user.name.charAt(0).toUpperCase())}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">${escapeHtml(user.name)}</p>
                                    <p class="text-xs text-slate-500 truncate">${escapeHtml(user.email)}</p>
                                </div>
                                <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">${user.audits_count || 0} acciones</span>
                                ${isSelected ? '<i class="ph ph-check-circle text-mso-gold ml-2 text-lg"></i>' : ''}
                            </div>
                        `;
                    });

                    resultsContainer.innerHTML = html;
                    resultsContainer.classList.remove('hidden');
                })
                .catch(error => {
                    // Ignorar errores de abort
                    if (error.name === 'AbortError') {
                        return;
                    }
                    console.error('Error:', error);
                    resultsContainer.innerHTML = `
                        <div class="px-4 py-3 text-sm text-red-500 text-center">
                            <i class="ph ph-warning-circle"></i> Error al buscar usuarios
                        </div>
                    `;
                    resultsContainer.classList.remove('hidden');
                });
            }, 400);
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });

        // Tecla Escape para cerrar resultados
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                resultsContainer.classList.add('hidden');
                searchInput.blur();
            }
        });

        // Enter para seleccionar el primer resultado
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const firstResult = resultsContainer.querySelector('.user-search-result');
                if (firstResult) {
                    e.preventDefault();
                    selectUser(firstResult);
                }
            }
        });
    }

    function selectUser(element) {
        const userId = element.dataset.userId;
        const userName = element.dataset.userName;
        const userEmail = element.dataset.userEmail;

        selectedUserId.value = userId;
        searchInput.value = `${userName} (${userEmail})`;
        resultsContainer.classList.add('hidden');

        // Enviar el formulario automáticamente
        const form = searchInput.closest('form');
        if (form) {
            form.submit();
        }
    }

    function limpiarUsuarioSeleccionado() {
        selectedUserId.value = '';
        searchInput.value = '';
        resultsContainer.classList.add('hidden');
        // Recargar la página sin el filtro de usuario
        window.location.href = '{{ route("audit-logs.user-logs") }}';
    }

    // ============================================
    // ELIMINAR FILTROS
    // ============================================
    function eliminarFiltro(param) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete(param);
        urlParams.delete('page');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.location.href = newUrl;
    }

    function limpiarTodosFiltros() {
        window.location.href = '{{ route("audit-logs.user-logs") }}';
    }

    // ============================================
    // BÚSQUEDA EN TABLA (Filtro rápido)
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const tableHeader = document.querySelector('.border-b.border-slate-200 .flex-col.sm\\:flex-row');
        if (tableHeader) {
            // Verificar si ya existe el buscador
            if (!tableHeader.querySelector('#tableQuickSearch')) {
                const searchWrapper = document.createElement('div');
                searchWrapper.className = 'relative';
                searchWrapper.innerHTML = `
                    <input type="text"
                           id="tableQuickSearch"
                           placeholder="Buscar en tabla..."
                           class="pl-9 pr-4 py-1.5 border rounded-lg text-sm focus:ring-2 focus:ring-mso-gold focus:border-transparent outline-none w-48">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                `;
                tableHeader.appendChild(searchWrapper);

                document.getElementById('tableQuickSearch')?.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#auditTable tbody tr');
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }
        }
    });
</script>
@endpush
@endsection
