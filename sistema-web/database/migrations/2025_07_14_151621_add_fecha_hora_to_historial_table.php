<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historial', function (Blueprint $table) {
            if (!Schema::hasColumn('historial', 'fecha')) {
                $table->date('fecha')->nullable()->after('detalles');
            }

            if (!Schema::hasColumn('historial', 'hora')) {
                $table->time('hora')->nullable()->after('fecha');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('historial', 'fecha')) {
                $columnsToDrop[] = 'fecha';
            }

            if (Schema::hasColumn('historial', 'hora')) {
                $columnsToDrop[] = 'hora';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
