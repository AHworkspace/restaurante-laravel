<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('UPDATE insumo_presentaciones p SET imagen = i.imagen FROM insumos i WHERE p.insumo_id = i.id AND p.predeterminada = true AND p.imagen IS NULL AND i.imagen IS NOT NULL');
        DB::table('insumos')->update(['stock_minimo'=>0,'costo_estandar'=>null,'imagen'=>null]);
    }
    public function down(): void{}
};
