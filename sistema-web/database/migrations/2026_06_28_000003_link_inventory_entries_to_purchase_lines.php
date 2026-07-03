<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimiento_inventarios', function (Blueprint $table) {
            if (! Schema::hasColumn('movimiento_inventarios', 'compra_linea_id')) {
                $table->foreignId('compra_linea_id')->nullable()->constrained('compra_lineas')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Reception traceability is intentionally preserved.
    }
};
