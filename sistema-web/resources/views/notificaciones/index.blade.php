@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header" style="color: #7A5C58; font-weight: bold; font-size: 1.3rem;">Historial de Notificaciones de Insumos</div>
                <div class="card-body">
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-4">
                            <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}" placeholder="Fecha inicio">
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}" placeholder="Fecha final">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        @forelse($notificaciones as $notification)
                            @php
                                $insumo = $notification->data['insumo_id'] ? \App\Models\Insumo::find($notification->data['insumo_id']) : null;
                                if ($insumo instanceof \Illuminate\Database\Eloquent\Collection) {
                                    $insumo = $insumo->first();
                                }
                                $esAlerta = isset($notification->data['message']) && str_contains($notification->data['message'], '⚠️');
                            @endphp
                            <li class="list-group-item d-flex align-items-center {{ $esAlerta ? 'bg-danger bg-opacity-10 border-danger' : (is_null($notification->read_at) ? 'bg-light fw-bold' : 'text-muted') }}" style="border-left: 5px solid #e53935;">
                                @if($esAlerta)
                                    <span style="width: 22px; height: 22px; background: #e53935; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                        <i class="fas fa-exclamation-triangle" style="color: #fff; font-size: 14px;"></i>
                                    </span>
                                @elseif(str_contains($notification->data['message'] ?? '', '✅'))
                                    <span style="width: 16px; height: 16px; background: #4caf50; border-radius: 50%; display: inline-block; margin-right: 10px;"></span>
                                @endif
                                <div style="flex:1;">
                                    @if($esAlerta)
                                        <div><b>Insumo:</b> {{ $notification->data['nombre_insumo'] ?? ($insumo?->nombre ?? '-') }}</div>
                                        <div><b>Categoría:</b> {{ $notification->data['categoria'] ?? ($insumo?->categoria?->nombre ?? '-') }}</div>
                                        <div><b>Stock mínimo:</b> {{ $notification->data['stock_minimo'] ?? ($insumo?->stock_minimo ?? '-') }}</div>
                                        <div><b>Stock actual:</b> {{ $notification->data['stock_actual'] ?? ($insumo?->getCantidadTotal() ?? '-') }}</div>
                                        <div><b>Salida:</b> {{ $notification->data['cantidad_salida'] ?? '-' }}</div>
                                        <div><b>Fecha:</b> {{ $notification->data['fecha'] ?? $notification->created_at->format('d/m/Y') }}</div>
                                        <div><b>Hora:</b> {{ $notification->data['hora'] ?? $notification->created_at->format('H:i') }}</div>
                                        <div class="mt-1">{{ $notification->data['message'] ?? 'Sin mensaje' }}</div>
                                    @else
                                        <div><b>Insumo:</b> {{ $insumo?->nombre ?? '-' }}</div>
                                        <div><b>Categoría:</b> {{ $insumo?->categoria?->nombre ?? '-' }}</div>
                                        <div><b>Stock mínimo:</b> {{ $insumo?->stock_minimo ?? '-' }}</div>
                                        <div><b>Stock actual:</b> {{ $insumo?->getCantidadTotal() ?? '-' }}</div>
                                        <div><b>Fecha:</b> {{ $notification->data['fecha'] ?? $notification->created_at->format('d/m/Y H:i') }}</div>
                                        <div class="mt-1">{{ str_replace(" el día " . ($notification->data['fecha'] ?? ''), '', $notification->data['message'] ?? 'Sin mensaje') }}</div>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">No tienes notificaciones de insumos</li>
                        @endforelse
                    </ul>
                    <div class="mt-3">
                        {{ $notificaciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
