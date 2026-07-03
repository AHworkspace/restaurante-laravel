<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    //

    protected $fillable = [
        'nombre',
        'descripcion',
        'stock_minimo',
        'imagen',
        'costo_estandar',
        'categoria_id',
        'restaurante_id',
        'unidad_medida_id',
        'tipo_uso',
        'vendible_menu',
        'precio_venta',
        'cantidad_base_por_venta',
    ];

    protected $casts = ['vendible_menu' => 'boolean', 'precio_venta' => 'decimal:2', 'cantidad_base_por_venta' => 'decimal:4'];

    protected static function booted():void
    {
        static::created(function(Insumo $insumo){if(!$insumo->presentaciones()->exists())$insumo->presentaciones()->create(['nombre'=>$insumo->tipo_uso==='indirecto'?'A granel':'Presentación general','unidad_contenido_id'=>$insumo->unidad_medida_id,'predeterminada'=>true,'activa'=>true]);});
    }

    public function unidad_medida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function recetas()
    {
        return $this->belongsToMany(Receta::class, 'recetas_insumos')
            ->withPivot('cantidad', 'desperdicio') // Incluye los campos extra
            ->withTimestamps();    // Incluye marcas de tiempo
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function movimiento_inventarios()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function lineasCompra(){return $this->hasMany(CompraLinea::class);}
    public function presentaciones(){return $this->hasMany(InsumoPresentacion::class);}
    public function presentacionPredeterminada(){return $this->hasOne(InsumoPresentacion::class)->where('predeterminada',true);}

    public function menusDia()
    {
        return $this->belongsToMany(MenuDia::class, 'menus_dia_insumos')
            ->withPivot(['cantidad', 'cantidad_inicial', 'precio_venta'])->withTimestamps();
    }

    // Función para obtener la cantidad total del insumo en el restaurante, sumando las cantidades de los movimientos de inventario de tipo entrada y restando las de tipo salida
    // Usa cantidad_convertida si existe (sistema nuevo), sino usa cantidad (compatibilidad con movimientos antiguos)
    public function getCantidadTotal()
    {
        // Cargar movimientos y trabajar con Collection
        $movimientos = $this->movimiento_inventarios;

        $entradas = $movimientos->where('tipo', 'entrada')
            ->sum(function($mov) {
                return $mov->cantidad_convertida ?? $mov->cantidad ?? 0;
            });

        $salidas = $movimientos->where('tipo', 'salida')
            ->sum(function($mov) {
                return $mov->cantidad_convertida ?? $mov->cantidad ?? 0;
            });

        return $entradas - $salidas;
    }
}
