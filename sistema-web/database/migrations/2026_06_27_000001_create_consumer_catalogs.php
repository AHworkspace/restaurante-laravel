<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fuerzas')) Schema::create('fuerzas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('codigo', 20)->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        if (! Schema::hasTable('instituciones')) Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuerza_id')->nullable()->constrained('fuerzas')->nullOnDelete();
            $table->string('nombre', 120);
            $table->string('codigo', 20)->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['fuerza_id', 'nombre']);
        });

        if (! Schema::hasTable('grados')) Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->nullOnDelete();
            $table->string('nombre', 100);
            $table->string('codigo', 20)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['institucion_id', 'nombre']);
        });

        if (! Schema::hasTable('tipos_comida')) Schema::create('tipos_comida', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('codigo', 20)->nullable()->unique();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        // Existing recovered catalogs are intentionally preserved on rollback.
    }
};
