<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraLinea extends Model
{
    protected $fillable = ['compra_id','insumo_id','presentacion_id','formato_empaque_id','estructura_empaque','marca_id','unidad_medida_id','unidad_precio_id','cantidad_pedida','cantidad_pedida_base','cantidad_recibida','cantidad_recibida_base','precio_unitario','factor_compra_base','cantidad_contenido','cantidad_suelta','unidad_contenido_id','unidad_inventario_id','factor_precio_base','costo_linea','etiqueta_manual','linea_pagada','suma_al_total'];
    protected $casts = ['estructura_empaque'=>'array','cantidad_pedida'=>'decimal:4','cantidad_pedida_base'=>'decimal:4','cantidad_recibida'=>'decimal:4','cantidad_recibida_base'=>'decimal:4','precio_unitario'=>'decimal:4','factor_compra_base'=>'decimal:6','cantidad_contenido'=>'decimal:6','cantidad_suelta'=>'decimal:6','factor_precio_base'=>'decimal:6','costo_linea'=>'decimal:2','linea_pagada'=>'boolean','suma_al_total'=>'boolean'];
    public function compra(){return $this->belongsTo(Compra::class);}
    public function insumo(){return $this->belongsTo(Insumo::class);}
    public function marca(){return $this->belongsTo(Marca::class);}
    public function presentacion(){return $this->belongsTo(InsumoPresentacion::class,'presentacion_id');}
    public function formatoEmpaque(){return $this->belongsTo(FormatoEmpaque::class,'formato_empaque_id');}
    public function unidadMedida(){return $this->belongsTo(UnidadMedida::class,'unidad_medida_id');}
    public function unidadPrecio(){return $this->belongsTo(UnidadMedida::class,'unidad_precio_id');}
    public function unidadContenido(){return $this->belongsTo(UnidadMedida::class,'unidad_contenido_id');}
    public function unidadInventario(){return $this->belongsTo(UnidadMedida::class,'unidad_inventario_id');}
    public function movimientos(){return $this->hasMany(MovimientoInventario::class,'compra_linea_id');}
    public function getCantidadFaltanteAttribute(): float{return max(0,(float)$this->cantidad_pedida-(float)$this->cantidad_recibida);}
    public function getCantidadFaltanteBaseAttribute(): float{return max(0,(float)$this->cantidad_pedida_base-(float)$this->cantidad_recibida_base);}
    public function getFaltanteDesglosadoAttribute(): array
    {
        $factor=max(1,(float)$this->factor_compra_base);$base=$this->cantidad_faltante_base;
        $esEmpaque=(float)$this->cantidad_contenido>0&&$this->unidad_contenido_id&&$this->unidad_medida_id!==$this->unidad_contenido_id;
        if(!$esEmpaque)return ['empaques'=>round($base/$factor,4),'sueltas'=>0,'es_empaque'=>false];
        $empaques=(int)floor(($base+0.000001)/$factor);
        return ['empaques'=>$empaques,'sueltas'=>round($base-($empaques*$factor),4),'es_empaque'=>true];
    }
}
