<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus_dia_insumos', function (Blueprint $table) {
            $table->decimal('precio_venta', 12, 2)->nullable()->after('insumo_id');
        });
        DB::table('menus_dia_insumos')->orderBy('id')->each(function ($linea) {
            $precio = DB::table('insumos')->where('id', $linea->insumo_id)->value('precio_venta');
            if ($precio !== null) DB::table('menus_dia_insumos')->where('id', $linea->id)->update(['precio_venta' => $precio]);
        });
    }

    public function down(): void
    {
        // El precio histórico del menú se conserva.
    }
};
