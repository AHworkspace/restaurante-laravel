<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - {{ $reporte->nombre }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #7A5C58; padding-bottom: 15px; }
        .header h1 { color: #7A5C58; margin: 5px 0; font-size: 22px; }
        .header h2 { color: #666; margin: 5px 0; font-size: 16px; }
        .info-box { background: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #7A5C58; }
        .info-box h3 { margin: 0 0 15px 0; color: #7A5C58; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th { background: #6F4E37; color: white; padding: 10px; text-align: left; font-size: 11px; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
        table tr:nth-child(even) { background: #f9f9f9; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 REPORTE DE VENTAS</h1>
        <h2>{{ $reporte->nombre }}</h2>
        <p><strong>Período:</strong> {{ $reporte->fecha_desde }} a {{ $reporte->fecha_hasta }}</p>
        <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- RESUMEN GENERAL CON TARJETAS --}}
    <div class="info-box">
        <h3>📈 RESUMEN GENERAL</h3>
        <table style="border: none;">
            <tr style="border: none;">
                <td style="width: 50%; background: #6F4E37; color: white; padding: 20px; text-align: center; border: none;">
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 8px;">📊 TOTAL VENTAS</div>
                    <div style="font-size: 36px; font-weight: bold;">{{ $totalVentas }}</div>
                    <div style="font-size: 11px; margin-top: 8px;">Ventas realizadas en el período</div>
                </td>
                <td style="width: 50%; background: #FF9800; color: white; padding: 20px; text-align: center; border: none;">
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 8px;">💰 INGRESOS TOTALES</div>
                    <div style="font-size: 36px; font-weight: bold;">Bs. {{ number_format($ingresosTotales, 2) }}</div>
                    <div style="font-size: 11px; margin-top: 8px;">Total de ingresos del período</div>
                </td>
            </tr>
            <tr style="border: none;">
                <td style="background: #4CAF50; color: white; padding: 20px; text-align: center; border: none;">
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 8px;">📉 PROMEDIO POR VENTA</div>
                    <div style="font-size: 36px; font-weight: bold;">Bs. {{ number_format($promedioVenta, 2) }}</div>
                    <div style="font-size: 11px; margin-top: 8px;">Valor promedio por venta</div>
                </td>
                <td style="background: #2196F3; color: white; padding: 20px; text-align: center; border: none;">
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 8px;">🍽️ PLATOS VENDIDOS</div>
                    <div style="font-size: 36px; font-weight: bold;">{{ $platosVendidos }}</div>
                    <div style="font-size: 11px; margin-top: 8px;">Cantidad total de platos</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- TOP 5 CON BARRAS HORIZONTALES --}}
    <div class="info-box">
        <h3>🏆 TOP 5 PRODUCTOS MÁS VENDIDOS</h3>
        @forelse($top5 as $index => $item)
            @php
                $maxIngresos = $top5->max('ingresos');
                $ancho = $maxIngresos > 0 ? ($item->ingresos / $maxIngresos) * 100 : 0;
            @endphp
            <div style="margin: 12px 0; page-break-inside: avoid;">
                <div style="margin-bottom: 3px;">
                    <strong style="color: #333;">{{ $index + 1 }}. {{ $item->receta->nombre }}</strong>
                    <span style="float: right; color: #7A5C58; font-weight: bold;">Bs. {{ number_format($item->ingresos, 2) }}</span>
                </div>
                <div style="background: #e0e0e0; height: 30px; border-radius: 4px; overflow: hidden; margin: 3px 0;">
                    <div style="background: #6F4E37; height: 100%; width: {{ $ancho }}%; color: white; font-size: 11px; font-weight: bold; display: flex; align-items: center; padding-left: 10px;">
                        {{ $item->total_vendido }} unidades vendidas
                    </div>
                </div>
                <div style="font-size: 10px; color: #666; margin-top: 3px;">
                    Número de ventas: {{ $item->num_ventas }} | Precio promedio: Bs. {{ number_format($item->precio_promedio, 2) }}
                </div>
            </div>
        @empty
            <p>No hay ventas en este período.</p>
        @endforelse
    </div>

    {{-- GRÁFICO DE BARRAS POR DÍA --}}
    <div class="info-box" style="page-break-inside: avoid;">
        <h3>📊 VENTAS POR DÍA</h3>
        @php
            $ventasPorDia = $ventas->groupBy(function($venta) {
                return $venta->created_at->format('Y-m-d');
            })->map(function($ventasDia) {
                return [
                    'fecha' => $ventasDia->first()->created_at->format('d/m/Y'),
                    'total' => $ventasDia->sum('total'),
                    'cantidad' => $ventasDia->sum('cantidad'),
                ];
            })->take(15);
            $maxTotalDia = $ventasPorDia->max('total') ?: 1;
        @endphp

        @foreach($ventasPorDia as $dia)
            @php
                $ancho = ($dia['total'] / $maxTotalDia) * 100;
            @endphp
            <div style="margin: 8px 0;">
                <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">{{ $dia['fecha'] }}</div>
                <div style="background: #e0e0e0; height: 25px; border-radius: 4px; overflow: hidden;">
                    <div style="background: linear-gradient(90deg, #6F4E37, #C9B6A9); height: 100%; width: {{ $ancho }}%; color: white; font-size: 10px; font-weight: bold; display: flex; align-items: center; padding-left: 8px;">
                        Bs. {{ number_format($dia['total'], 2) }} • {{ $dia['cantidad'] }} platos
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- TABLA DE DETALLE --}}
    <div class="info-box" style="page-break-before: always;">
        <h3>📋 DETALLE DE VENTAS</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventas as $venta)
                    <tr>
                        <td>{{ $venta->created_at->format('d/m/Y') }}</td>
                        <td>{{ $venta->receta->nombre }}</td>
                        <td>{{ $venta->cantidad }}</td>
                        <td>Bs. {{ number_format($venta->precio, 2) }}</td>
                        <td><strong>Bs. {{ number_format($venta->total, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999;">No hay ventas en este período</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>Sistema de Gestión de Restaurante - Reporte generado automáticamente</strong></p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
