<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsumoPresentacion extends Model
{
    protected static function booted():void
    {
        static::creating(function(self $presentacion){$insumo=Insumo::find($presentacion->insumo_id);if(!$insumo)return;$presentacion->categoria_id??=$insumo->categoria_id;$presentacion->tipo_uso??=$insumo->tipo_uso;$presentacion->unidad_stock_id??=$insumo->unidad_medida_id;});
    }
    protected $table='insumo_presentaciones';
    protected $fillable=['insumo_id','nombre','descripcion','categoria_id','tipo_uso','contenido','unidad_contenido_id','unidad_stock_id','stock_minimo','costo_estandar','unidad_empaque_id','unidades_por_empaque','tipo_envase','formato_empaque_id','retornable','codigo_barras','imagen','predeterminada','activa'];
    protected $casts=['contenido'=>'decimal:4','stock_minimo'=>'decimal:4','costo_estandar'=>'decimal:4','unidades_por_empaque'=>'decimal:4','retornable'=>'boolean','predeterminada'=>'boolean','activa'=>'boolean'];
    public function stockBajo():bool{return $this->stockDisponible()<=(float)$this->stock_minimo;}
    public function insumo(){return $this->belongsTo(Insumo::class);}
    public function categoria(){return $this->belongsTo(Categoria::class);}
    public function unidadContenido(){return $this->belongsTo(UnidadMedida::class,'unidad_contenido_id');}
    public function unidadStockRelacion(){return $this->belongsTo(UnidadMedida::class,'unidad_stock_id');}
    public function unidadEmpaque(){return $this->belongsTo(UnidadMedida::class,'unidad_empaque_id');}
    public function formatoEmpaque(){return $this->belongsTo(FormatoEmpaque::class,'formato_empaque_id');}
    public function movimientos(){return $this->hasMany(MovimientoInventario::class,'presentacion_id');}
    public function menusDia(){return $this->belongsToMany(MenuDia::class,'menus_dia_presentaciones','presentacion_id','menu_dia_id')->withPivot(['precio_venta','cantidad_inicial','cantidad','tipo_produccion_id'])->withTimestamps();}
    public function getNombreCompletoAttribute():string{return $this->insumo->nombre.' · '.$this->nombre;}
    public function stockDisponible():float{return $this->movimientos->sum(fn($m)=>($m->tipo==='entrada'?1:-1)*(float)($m->cantidad_convertida??$m->cantidad));}
    public function unidadStock():?UnidadMedida{return $this->unidadStockRelacion ?? $this->movimientos->first(fn($m)=>$m->unidadInventario)?->unidadInventario ?? $this->unidadContenido ?? $this->insumo->unidad_medida;}
}
