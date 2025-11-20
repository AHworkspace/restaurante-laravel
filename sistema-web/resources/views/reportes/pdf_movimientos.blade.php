<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3, h4 { color: #7A5C58; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; font-size: 12px; }
        th { background: #f5f5f5; }
        .summary { margin-top: 15px; }
        .summary p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>Reporte de Movimientos</h1>
    <p><strong>Nombre:</strong> {{ $reporte->nombre ?? 'Sin nombre' }}</p>
    <p><strong>Período:</strong> {{ optional($reporte->fecha_desde)->format('d/m/Y') }} - {{ optional($reporte->fecha_hasta)->format('d/m/Y') }}</p>
    <div class="summary">
        <p><strong>Total de movimientos:</strong> {{ $reporte->total_movimientos }}</p>
        <p><strong>Total invertido:</strong> Bs. {{ number_format($reporte->total_costo, 2) }}</p>
        <p><strong>Fecha de generación:</strong> {{ $reporte->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Insumo</th>
                <th>Cantidad</th>
                <th>Tipo</th>
                <th>Motivo</th>
                <th>Proveedor</th>
                <th>Costo (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}</td>
                    <td>{{ $item['insumo'] }}</td>
                    <td>{{ number_format($item['cantidad'], 2) }} {{ $item['unidad'] }}</td>
                    <td>{{ ucfirst($item['tipo']) }}</td>
                    <td>{{ $item['motivo'] }}</td>
                    <td>{{ $item['proveedor'] ?: '-' }}</td>
                    <td>{{ number_format($item['costo'], 2) }}</td>
                </tr>
                @if(!empty($item['detalle']))
                    <tr>
                        <td colspan="8"><strong>Detalle:</strong> {{ $item['detalle'] }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>

