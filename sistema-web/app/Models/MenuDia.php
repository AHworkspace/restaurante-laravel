<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuDia extends Model
{
    use HasFactory;

    protected $table = 'menus_dia';

    protected $fillable = [
        'titulo', 'tipo_comida_id', 'fecha', 'hora_inicio', 'hora_fin',
        'visible_para_clientes', 'visible_en_horario', 'activo',
        'descripcion', 'usuario_creador_id', 'historial',
    ];

    protected $casts = [
        'fecha' => 'date', 'visible_para_clientes' => 'boolean',
        'visible_en_horario' => 'boolean', 'activo' => 'boolean', 'historial' => 'array',
    ];

    public function tipoComida() { return $this->belongsTo(TipoComida::class); }
    public function usuarioCreador() { return $this->belongsTo(User::class, 'usuario_creador_id'); }
    public function recetas()
    {
        return $this->belongsToMany(Receta::class, 'menus_dia_recetas')
            ->withPivot(['cantidad', 'cantidad_inicial', 'adiciones', 'precio_venta','tipo_produccion_id'])->withTimestamps();
    }
    public function insumosDirectos()
    {
        return $this->belongsToMany(Insumo::class, 'menus_dia_insumos')
            ->withPivot(['cantidad', 'cantidad_inicial', 'precio_venta'])->withTimestamps();
    }
    public function presentacionesDirectas()
    {
        return $this->belongsToMany(InsumoPresentacion::class,'menus_dia_presentaciones','menu_dia_id','presentacion_id')
            ->withPivot(['precio_venta','cantidad','cantidad_inicial','adiciones','tipo_produccion_id'])->withTimestamps();
    }

    public static function normalizarHoraDia($valor): ?string
    {
        if ($valor === null || $valor === '') return null;
        if ($valor instanceof \DateTimeInterface) return $valor->format('H:i:s');
        if (preg_match('/(\d{1,2}):(\d{2})(?::(\d{2}))?/', (string) $valor, $m)) {
            return sprintf('%02d:%02d:%02d', $m[1], $m[2], $m[3] ?? 0);
        }
        return null;
    }

    public function pasaFiltroHorarioVisualizacion(string|\DateTimeInterface $fecha): bool
    {
        $zona = config('app.timezone');
        $ahora = now()->timezone($zona);
        $consulta = $fecha instanceof \DateTimeInterface
            ? Carbon::instance($fecha)->timezone($zona)->toDateString()
            : Carbon::parse($fecha, $zona)->toDateString();
        if ($consulta !== $ahora->toDateString()) return true;
        $inicio = self::normalizarHoraDia($this->getRawOriginal('hora_inicio') ?? $this->hora_inicio);
        $fin = self::normalizarHoraDia($this->getRawOriginal('hora_fin') ?? $this->hora_fin);
        if ($inicio === null && $fin === null) return true;
        $actual = $ahora->format('H:i:s');
        if ($inicio !== null && $fin === null) return $actual >= $inicio;
        if ($inicio === null) return $actual <= $fin;
        return $inicio <= $fin ? $actual >= $inicio && $actual <= $fin : $actual >= $inicio || $actual <= $fin;
    }

    public function visibleEnDashboard(string $fecha): bool
    {
        return $this->activo && $this->visible_para_clientes
            && $this->fecha?->toDateString() === $fecha
            && $this->pasaFiltroHorarioVisualizacion($fecha)
            && ($this->recetas->contains(fn ($receta) => (int) ($receta->pivot->cantidad ?? 0) > 0)
                || $this->presentacionesDirectas->contains(fn ($presentacion) => (int) ($presentacion->pivot->cantidad ?? 0) > 0));
    }

    public function tieneVentanaHorariaConfigurada(): bool
    {
        return self::normalizarHoraDia($this->hora_inicio) !== null
            || self::normalizarHoraDia($this->hora_fin) !== null;
    }

    public function visibleParaClientesEfectivoAhora(): bool
    {
        $hoy = now()->timezone(config('app.timezone'))->toDateString();
        return $this->activo && $this->visible_para_clientes
            && $this->fecha?->toDateString() === $hoy
            && $this->pasaFiltroHorarioVisualizacion($hoy);
    }

    public function estadoPublicacion(): array
    {
        if (! $this->activo) return ['Inactivo', 'bg-dark'];
        if (! $this->visible_para_clientes) return ['Oculto manualmente', 'bg-secondary'];
        $hoy = now()->timezone(config('app.timezone'))->toDateString();
        if ($this->fecha?->toDateString() < $hoy) return ['Finalizado', 'bg-secondary'];
        if ($this->fecha?->toDateString() > $hoy) return ['Programado', 'bg-info'];
        if (! $this->pasaFiltroHorarioVisualizacion($hoy)) return ['Oculto (fuera de horario)', 'bg-warning text-dark'];
        return ['Publicado ahora', 'bg-success'];
    }
}
