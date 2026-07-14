<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_guardados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('sector', 40);
            $table->string('subtipo', 80)->nullable();
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->unsignedInteger('total_registros')->default(0);
            $table->decimal('total_monto', 15, 2)->default(0);
            $table->json('filtros')->nullable();
            $table->json('datos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_guardados');
    }
};
