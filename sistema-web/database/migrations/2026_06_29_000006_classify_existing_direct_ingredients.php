<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('insumos')->whereRaw('LOWER(nombre) = ?', ['coca-cola'])->update([
            'tipo_uso' => 'directo',
            'cantidad_base_por_venta' => 1,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // La clasificación elegida por el negocio se conserva.
    }
};
