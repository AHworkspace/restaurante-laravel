<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consumidores')) Schema::create('consumidores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 200);
            $table->string('ci', 30)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->foreignId('fuerza_id')->nullable()->constrained('fuerzas')->nullOnDelete();
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->nullOnDelete();
            $table->foreignId('grado_id')->nullable()->constrained('grados')->nullOnDelete();
            $table->string('codigo_unico', 50)->nullable()->unique();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index('nombre_completo');
        });

    }

    public function down(): void
    {
        // The recovered consumidores table predates this compatibility migration.
    }
};
