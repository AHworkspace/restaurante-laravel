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

    public function unidadCompraNombre(): string
    {
        return $this->formatoEmpaque?->nombre
            ?: ($this->unidadMedida?->abreviatura ?: $this->insumo?->unidad_medida?->abreviatura ?: 'unidad');
    }

    public function unidadInventarioNombre(): string
    {
        return $this->unidadInventario?->abreviatura
            ?: $this->presentacion?->unidadStock()?->abreviatura
            ?: $this->insumo?->unidad_medida?->abreviatura
            ?: 'unidad';
    }

    public function cantidadCompraTexto(?float $cantidad = null): string
    {
        $cantidad ??= (float) $this->cantidad_pedida;
        $texto = number_format($cantidad, 2).' '.$this->unidadCompraNombre();
        $base = $cantidad * (float) ($this->factor_compra_base ?: 1);

        if ($this->formatoEmpaque || abs($base - $cantidad) > 0.0001) {
            $texto .= ' = '.number_format($base, 2).' '.$this->unidadInventarioNombre();
        }

        if ((float) $this->cantidad_suelta > 0) {
            $texto .= ' + '.number_format((float) $this->cantidad_suelta, 2).' '.$this->unidadInventarioNombre().' sueltas';
        }

        return $texto;
    }

    public function entradaInventarioTexto(?float $cantidadBase = null): string
    {
        $cantidadBase ??= (float) $this->cantidad_pedida_base;
        return number_format($cantidadBase, 2).' '.$this->unidadInventarioNombre();
    }

    public function faltanteTexto(): string
    {
        $datos = $this->faltante_desglosado;
        if (! $datos['es_empaque']) {
            return number_format((float) $datos['empaques'], 2).' '.$this->unidadCompraNombre();
        }

        $texto = number_format((float) $datos['empaques'], 2).' '.$this->unidadCompraNombre();
        if ((float) $datos['sueltas'] > 0) {
            $texto .= ' y '.number_format((float) $datos['sueltas'], 2).' '.$this->unidadInventarioNombre();
        }

        $texto .= ' = '.number_format((float) $this->cantidad_faltante_base, 2).' '.$this->unidadInventarioNombre();

        return $texto;
    }
}
