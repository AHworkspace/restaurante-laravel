<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraProveedorPago extends Model
{
    protected $fillable=['compra_id','monto'];
    protected $casts=['monto'=>'decimal:2'];
    public function compra(){return $this->belongsTo(Compra::class);}
}
