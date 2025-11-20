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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            // cantidad
            $table->float('cantidad');
            //precio
            $table->float('precio'); // precio unitario
            // total
            $table->float('total'); // cantidad * precio
            // relacion con una receta
            $table->foreignId('receta_id')->constrained('recetas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
