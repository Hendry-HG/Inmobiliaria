<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Reporte de Auditoría' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #c5a059; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; color: #0f172a; margin-bottom: 5px; }
        .header .subtitle { font-size: 12px; color: #64748b; }
        .header .date { font-size: 10px; color: #94a3b8; margin-top: 5px; }
        .filters { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 15px; margin-bottom: 15px; font-size: 9px; }
        .filters span { font-weight: bold; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #0f172a; color: white; padding: 8px 6px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; font-size: 9px; vertical-align: top; }
        tr:nth-child(even) { background: #f8fafc; }
        tr:hover { background: #f1f5f9; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .badge-created { background: #d1fae5; color: #065f46; }
        .badge-updated { background: #fef3c7; color: #92400e; }
        .badge-deleted { background: #fee2e2; color: #991b1b; }
        .badge-login { background: #dbeafe; color: #1e40af; }
        .badge-logout { background: #e2e8f0; color: #475569; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .stats { display: flex; gap: 20px; margin-bottom: 15px; }
        .stat-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; text-align: center; }
        .stat-box .number { font-size: 20px; font-weight: bold; color: #c5a059; }
        .stat-box .label { font-size: 8px; color: #64748b; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Reporte de Auditoría' }}</h1>
        <div class="subtitle">MSO Grupo Inmobiliario</div>
        <div class="date">Generado: {{ now()->setTimezone('America/Caracas')->format('d/m/Y h:i A') }}</div>
    </div>

    @if(isset($filters) && count($filters) > 0)
    <div class="filters">
        @foreach($filters as $key => $value)
            @if($value)
                <span>{{ $key }}:</span> {{ $value }} &nbsp;&nbsp;|&nbsp;&nbsp;
            @endif
        @endforeach
    </div>
    @endif

    <div class="stats">
        <div class="stat-box">
            <div class="number">{{ $logs->count() }}</div>
            <div class="label">Total Registros</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $logs->where('action', 'created')->count() }}</div>
            <div class="label">Creaciones</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $logs->where('action', 'updated')->count() }}</div>
            <div class="label">Actualizaciones</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $logs->where('action', 'deleted')->count() }}</div>
            <div class="label">Eliminaciones</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="12%">Fecha/Hora</th>
                <th width="13%">Usuario</th>
                <th width="10%">Acción</th>
                <th width="10%">Entidad</th>
                <th width="35%">Descripción</th>
                <th width="10%">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->created_at ? $log->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i') : 'N/A' }}</td>
                <td>{{ $log->user ? $log->user->full_name : 'Sistema' }}</td>
                <td>
                    @php
                        $action = $log->action ?? $log->event ?? '';
                        $badgeClass = match($action) {
                            'created' => 'badge-created',
                            'updated' => 'badge-updated',
                            'deleted' => 'badge-deleted',
                            'login' => 'badge-login',
                            'logout' => 'badge-logout',
                            default => 'badge-updated',
                        };
                        $actionLabel = match($action) {
                            'created' => 'Creación',
                            'updated' => 'Actualización',
                            'deleted' => 'Eliminación',
                            'login' => 'Inicio Sesión',
                            'logout' => 'Cierre Sesión',
                            default => ucfirst($action),
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $actionLabel }}</span>
                </td>
                <td>{{ $log->subject_type ? class_basename($log->subject_type) : 'Sistema' }}</td>
                <td>{{ $log->description ?? 'N/A' }}</td>
                <td>{{ $log->ip_address ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">No hay registros de auditoría</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>MSO Grupo Inmobiliario — Sistema de Gestión Inmobiliaria</p>
        <p>Reporte generado automáticamente el {{ now()->setTimezone('America/Caracas')->format('d/m/Y \\a\\s \\l\\a\\s h:i A') }}</p>
    </div>
</body>
</html>
