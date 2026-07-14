<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - {{ $reporte->nombre }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { color: #7A5C58; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; font-size: 11px; }
        th { background: #f5f5f5; }
        .summary { margin: 12px 0; }
        .summary p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>Reporte de {{ ucfirst($reporte->sector) }}</h1>
    <p><strong>Nombre:</strong> {{ $reporte->nombre }}</p>
    <p><strong>Tipo:</strong> {{ $reporte->tipo_etiqueta }}</p>
    <p><strong>Periodo:</strong> {{ optional($reporte->fecha_desde)->format('d/m/Y') ?: '-' }} - {{ optional($reporte->fecha_hasta)->format('d/m/Y') ?: '-' }}</p>

    <div class="summary">
        <p><strong>Total registros:</strong> {{ $reporte->total_registros }}</p>
        <p><strong>Total:</strong> Bs. {{ number_format((float) $reporte->total_monto, 2) }}</p>
        <p><strong>Generado:</strong> {{ $reporte->created_at->format('d/m/Y H:i') }}</p>
    </div>

    @php($datos = collect($reporte->datos ?? []))
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Detalle</th>
                <th>Cliente/Proveedor</th>
                <th>Estado/Tipo</th>
                <th>Cantidad</th>
                <th>Total (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['fecha'] ?? '-' }}</td>
                    <td>{{ $item['detalle'] ?? '-' }}</td>
                    <td>{{ $item['persona'] ?? '-' }}</td>
                    <td>{{ $item['estado'] ?? $item['tipo'] ?? '-' }}</td>
                    <td>{{ $item['cantidad'] ?? '-' }}</td>
                    <td>{{ number_format((float) ($item['total'] ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
