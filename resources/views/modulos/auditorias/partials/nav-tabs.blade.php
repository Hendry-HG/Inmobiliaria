{{-- Navegación por pestañas para auditoría --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <ul class="nav nav-pills flex flex-wrap gap-2">
        <li class="nav-item">
            <a href="{{ route('audit-logs.index') }}" class="nav-link {{ $active == 'dashboard' ? 'active' : '' }}">
                <i class="ph ph-speedometer"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('audit-logs.user-logs') }}" class="nav-link {{ $active == 'users' ? 'active' : '' }}">
                <i class="ph ph-users"></i> Usuarios
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('audit-logs.property-logs') }}" class="nav-link {{ $active == 'properties' ? 'active' : '' }}">
                <i class="ph ph-buildings"></i> Propiedades
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('audit-logs.appointment-logs') }}" class="nav-link {{ $active == 'appointments' ? 'active' : '' }}">
                <i class="ph ph-calendar-check"></i> Citas
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('audit-logs.lead-logs') }}" class="nav-link {{ $active == 'leads' ? 'active' : '' }}">
                <i class="ph ph-target"></i> Leads
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('audit-logs.system-logs') }}" class="nav-link {{ $active == 'system' ? 'active' : '' }}">
                <i class="ph ph-gear"></i> Sistema
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('audit-logs.reports') }}" class="nav-link {{ $active == 'reports' ? 'active' : '' }}">
                <i class="ph ph-chart-pie"></i> Reportes
            </a>
        </li>
    </ul>
</div>

@push('styles')
<style>
    .nav-pills .nav-link {
        color: #64748b;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    .nav-pills .nav-link:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .nav-pills .nav-link.active {
        background-color: #c9a84c;
        color: #fff;
    }
    .nav-pills .nav-link i {
        margin-right: 0.5rem;
    }
</style>
@endpush
