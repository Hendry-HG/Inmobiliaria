{{-- Formulario de edición de permisos del sidebar --}}
@extends('layouts.dashboard')

@section('title', 'Editar Permisos del Sidebar')
@section('header', 'Editar Visibilidad del Sidebar')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-gray-800 mb-4">Configurando: <span class="text-mso-gold">{{ $role->name }}</span></h3>

        {{--  ADVERTENCIA PARA SUPER ADMIN --}}
        @if($role->name === 'Super Admin')
        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-start gap-2">
                <i class="ph ph-warning text-amber-600 text-xl mt-0.5"></i>
                <div>
                    <p class="text-xs text-amber-700">Estos cambios afectarán tu propia experiencia en el sidebar. Si desmarcas alguna opción, desaparecerá de tu menú.</p>
                </div>
            </div>
        </div>
        @endif

    

        <form action="{{ route('super-admin.sidebar-permissions.update', $role) }}" method="POST" id="sidebarForm">
            @csrf
            @method('PUT')

            <input type="hidden" name="permissions_json" id="permissions_json" value="">

            <div class="mb-6">
                <p class="text-sm text-gray-500 mb-4">
                    Selecciona qué secciones del sidebar serán visibles para los usuarios con el rol <strong>{{ $role->name }}</strong>.
                </p>

                <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-xs text-blue-700 font-medium"> Permisos actuales del sidebar para este rol:</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @php
                            $currentSidebarPerms = $role->permissions->filter(function($p) {
                                return str_starts_with($p->name, 'sidebar.');
                            });
                        @endphp
                        @forelse($currentSidebarPerms as $perm)
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">{{ str_replace('sidebar.', '', $perm->name) }}</span>
                        @empty
                            <span class="text-xs text-blue-400">Ningún permiso de sidebar configurado</span>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($sidebarPermissions as $permission)
                        @php
                            $label = ucfirst(str_replace('sidebar.', '', str_replace('_', ' ', $permission->name)));
                            $isChecked = $role->hasPermissionTo($permission->name);
                        @endphp
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 hover:border-mso-gold transition-colors">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->name }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       class="sidebar-checkbox rounded text-mso-gold focus:ring-mso-gold h-4 w-4"
                                       data-permission="{{ $permission->name }}">
                                <div class="flex items-center gap-2">
                                    <i class="ph
                                        @if(str_contains($permission->name, 'dashboard')) ph-squares-four text-blue-500
                                        @elseif(str_contains($permission->name, 'catalogo')) ph-buildings text-green-500
                                        @elseif(str_contains($permission->name, 'users')) ph-users text-purple-500
                                        @elseif(str_contains($permission->name, 'roles')) ph-lock-key text-red-500
                                        @elseif(str_contains($permission->name, 'properties')) ph-building text-amber-500
                                        @elseif(str_contains($permission->name, 'leads')) ph-target text-orange-500
                                        @elseif(str_contains($permission->name, 'appointments')) ph-calendar-check text-teal-500
                                        @elseif(str_contains($permission->name, 'chat')) ph-chat-circle-text text-pink-500
                                        @elseif(str_contains($permission->name, 'audit')) ph-scroll text-indigo-500
                                        @elseif(str_contains($permission->name, 'reports')) ph-chart-bar text-cyan-500
                                        @elseif(str_contains($permission->name, 'config')) ph-gear text-slate-500
                                        @elseif(str_contains($permission->name, 'favorites')) ph-heart text-red-500
                                        @elseif(str_contains($permission->name, 'servicios')) ph-handshake text-yellow-500
                                        @elseif(str_contains($permission->name, 'categorias')) ph-folder text-violet-500
                                        @elseif(str_contains($permission->name, 'ubicaciones')) ph-globe-hemisphere-west text-emerald-500
                                        @elseif(str_contains($permission->name, 'telefonos')) ph-phone text-blue-400
                                        @elseif(str_contains($permission->name, 'profile')) ph-user-circle text-slate-400
                                        @else ph-dots-three text-gray-400 @endif
                                        text-lg"></i>
                                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-4 p-3 bg-slate-50 rounded-lg border border-slate-200">
                <p class="text-sm text-slate-700">
                    <i class="ph ph-check-square"></i>
                    Permisos del sidebar seleccionados: <span id="selectedCount" class="font-bold text-mso-gold">0</span>
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <div class="flex gap-2">
                    <button type="button" onclick="selectAllSidebar()" class="text-xs text-mso-blue hover:underline px-2 py-1">Seleccionar todos</button>
                    <button type="button" onclick="deselectAllSidebar()" class="text-xs text-red-500 hover:underline px-2 py-1">Deseleccionar todos</button>
                </div>
                <a href="{{ route('super-admin.sidebar-permissions.index') }}" class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition-colors">
                    <i class="ph ph-x mr-1"></i> Cancelar
                </a>
                <button type="submit" id="submitBtn" class="px-8 py-2.5 bg-mso-blue text-white rounded-lg hover:bg-slate-800 font-bold shadow-lg transition-all hover:-translate-y-0.5">
                    <i class="ph ph-check mr-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.sidebar-checkbox:checked');
        document.getElementById('selectedCount').textContent = checkboxes.length;
    }

    function selectAllSidebar() {
        document.querySelectorAll('.sidebar-checkbox').forEach(cb => cb.checked = true);
        updateSelectedCount();
    }

    function deselectAllSidebar() {
        document.querySelectorAll('.sidebar-checkbox').forEach(cb => cb.checked = false);
        updateSelectedCount();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.sidebar-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
        updateSelectedCount();

        const form = document.getElementById('sidebarForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin mr-1"></i> Guardando...';

                const checkedPermissions = [];
                document.querySelectorAll('.sidebar-checkbox:checked').forEach(cb => {
                    checkedPermissions.push(cb.value);
                });

                const hiddenInput = document.getElementById('permissions_json');
                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(checkedPermissions);
                }

                return true;
            });
        }
    });
</script>
@endpush
@endsection