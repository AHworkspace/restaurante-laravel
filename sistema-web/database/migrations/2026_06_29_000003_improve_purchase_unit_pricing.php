<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_lineas', function (Blueprint $table) {
            $table->foreignId('unidad_precio_id')->nullable()->after('unidad_medida_id')->constrained('unidad_medidas')->nullOnDelete();
            $table->decimal('precio_unitario', 14, 4)->nullable()->after('cantidad_recibida');
            $table->decimal('factor_compra_base', 14, 6)->nullable()->after('precio_unitario');
            $table->decimal('factor_precio_base', 14, 6)->nullable()->after('factor_compra_base');
        });

        DB::table('compra_lineas')->orderBy('id')->each(function ($linea) {
            $cantidad = max((float) $linea->cantidad_pedida, 0.000001);
            $factor = (float) $linea->cantidad_pedida_base / $cantidad;
            DB::table('compra_lineas')->where('id', $linea->id)->update([
                'unidad_precio_id' => $linea->unidad_medida_id,
                'precio_unitario' => (float) $linea->costo_linea / $cantidad,
                'factor_compra_base' => $factor > 0 ? $factor : 1,
                'factor_precio_base' => $factor > 0 ? $factor : 1,
            ]);
        });

        $cuartilla = DB::table('unidad_medidas')->where('nombre', 'Cuartilla')->first();
        $arroba = DB::table('unidad_medidas')->where('nombre', 'Arroba')->first();
        $kilogramo = DB::table('unidad_medidas')->where('nombre', 'Kilogramo')->first();
        $litro = DB::table('unidad_medidas')->where('nombre', 'Litro')->first();

        if ($cuartilla) {
            DB::table('unidad_medidas')->where('id', $cuartilla->id)->update([
                'descripcion' => 'Unidad comercial de peso equivalente a un cuarto de arroba',
            ]);
            if ($litro) {
                DB::table('conversiones_unidades')->where(function ($query) use ($cuartilla, $litro) {
                    $query->where('unidad_origen_id', $cuartilla->id)->where('unidad_destino_id', $litro->id);
                })->orWhere(function ($query) use ($cuartilla, $litro) {
                    $query->where('unidad_origen_id', $litro->id)->where('unidad_destino_id', $cuartilla->id);
                })->delete();
            }
            if ($arroba) {
                DB::table('conversiones_unidades')->updateOrInsert(
                    ['unidad_origen_id' => $cuartilla->id, 'unidad_destino_id' => $arroba->id],
                    ['factor_conversion' => 0.25, 'created_at' => now(), 'updated_at' => now()]
                );
            }
            if ($kilogramo) {
                DB::table('conversiones_unidades')->updateOrInsert(
                    ['unidad_origen_id' => $cuartilla->id, 'unidad_destino_id' => $kilogramo->id],
                    ['factor_conversion' => 2.83495, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::table('compra_lineas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_precio_id');
            $table->dropColumn(['precio_unitario', 'factor_compra_base', 'factor_precio_base']);
        });
    }
};
