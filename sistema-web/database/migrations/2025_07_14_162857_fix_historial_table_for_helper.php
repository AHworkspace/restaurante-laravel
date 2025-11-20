<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial', function (Blueprint $table) {
            if (Schema::hasColumn('historial', 'seccion')) {
                $table->dropColumn('seccion');
            }
            if (Schema::hasColumn('historial', 'fecha_hora')) {
                $table->dropColumn('fecha_hora');
            }
            if (!Schema::hasColumn('historial', 'fecha')) {
                $table->date('fecha')->nullable()->after('detalles');
            }
            if (!Schema::hasColumn('historial', 'hora')) {
                $table->time('hora')->nullable()->after('fecha');
            }
        });
    }

    public function down(): void
    {
        Schema::table('historial', function (Blueprint $table) {
            if (!Schema::hasColumn('historial', 'seccion')) {
                $table->string('seccion')->nullable();
            }
            if (!Schema::hasColumn('historial', 'fecha_hora')) {
                $table->timestamp('fecha_hora')->nullable();
            }
            if (Schema::hasColumn('historial', 'fecha')) {
                $table->dropColumn('fecha');
            }
            if (Schema::hasColumn('historial', 'hora')) {
                $table->dropColumn('hora');
            }
        });
    }
};
