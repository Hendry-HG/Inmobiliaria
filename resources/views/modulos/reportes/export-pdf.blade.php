<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $data['titulo'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            padding: 20px;
            color: #1e293b;
            font-size: 12px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #c5a059;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #c5a059;
            margin: 0;
        }
        .header p {
            color: #64748b;
            margin: 5px 0 0;
            font-size: 12px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .metric-card {
            background: #f8fafc;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .metric-card .label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .metric-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table th {
            background: #c5a059;
            color: white;
            padding: 8px 12px;
            text-align: left;
            font-weight: 600;
        }
        table td {
            padding: 6px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        table tr:nth-child(even) {
            background: #f8fafc;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            margin-top: 20px;
            font-size: 10px;
            color: #94a3b8;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h1> {{ $data['titulo'] }}</h1>
        <p>Generado: {{ $data['fecha_generacion'] }}</p>
    </div>

    {{-- MÉTRICAS PRINCIPALES --}}
    <div class="section">
        <div class="section-title"> Métricas Principales</div>
        <div class="grid-2">
            @foreach($data['metricas'] as $key => $value)
            <div class="metric-card">
                <div class="label">{{ $key }}</div>
                <div class="value">{{ $value }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- PROPIEDADES POR CATEGORÍA --}}
    <div class="section">
        <div class="section-title"> Propiedades por Categoría</div>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th style="text-align: right;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['propiedades_por_categoria'] as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td style="text-align: right;">{{ number_format($item['value']) }}</td>
                </tr>
                @endforeach
                <tr style="background: #e2e8f0; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: right;">{{ number_format(collect($data['propiedades_por_categoria'])->sum('value')) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- CITAS POR ESTADO --}}
    <div class="section">
        <div class="section-title"> Citas por Estado</div>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th style="text-align: right;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['citas_por_estado'] as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td style="text-align: right;">{{ number_format($item['value']) }}</td>
                </tr>
                @endforeach
                <tr style="background: #e2e8f0; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: right;">{{ number_format(collect($data['citas_por_estado'])->sum('value')) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- LEADS POR ESTADO --}}
    <div class="section">
        <div class="section-title"> Leads por Estado</div>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th style="text-align: right;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['leads_por_estado'] as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td style="text-align: right;">{{ number_format($item['value']) }}</td>
                </tr>
                @endforeach
                <tr style="background: #e2e8f0; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: right;">{{ number_format(collect($data['leads_por_estado'])->sum('value')) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- TOP ASESORES --}}
    <div class="section">
        <div class="section-title"> Top Asesores</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Asesor</th>
                    <th style="text-align: right;">Leads Captados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['top_asesores'] as $index => $asesor)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $asesor['name_display'] ?? $asesor['name'] }}</td>
                    <td style="text-align: right;">{{ number_format($asesor['leads']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>MSO Inmobiliaria &copy; {{ date('Y') }} - Todos los derechos reservados</p>
        <p>Reporte generado automáticamente por el sistema de gestión inmobiliaria</p>
        <p style="font-size: 8px; color: #cbd5e1;">Documento confidencial - Solo para uso interno</p>
    </div>

</body>
</html>
