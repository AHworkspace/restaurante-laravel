<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consumos')) Schema::create('consumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumidor_id')->constrained('consumidores')->cascadeOnDelete();
            $table->foreignId('receta_id')->constrained('recetas')->restrictOnDelete();
            $table->foreignId('tipo_comida_id')->nullable()->constrained('tipos_comida')->nullOnDelete();
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('total', 10, 2);
            $table->date('fecha_consumo');
            $table->time('hora_consumo');
            $table->enum('estado_pago', ['pendiente', 'parcial', 'pagado', 'cancelado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_registro_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->index(['fecha_consumo', 'consumidor_id']);
        });

        if (! Schema::hasTable('pagos')) Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumidor_id')->constrained('consumidores')->cascadeOnDelete();
            $table->decimal('monto', 10, 2);
            $table->enum('tipo_pago', ['consumo_especifico', 'adelanto', 'cuenta_periodo']);
            $table->enum('metodo_pago', ['efectivo', 'qr', 'transferencia'])->default('efectivo');
            $table->string('periodo_pagado', 50)->nullable();
            $table->date('fecha_pago');
            $table->time('hora_pago');
            $table->string('referencia', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_registro_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->index(['consumidor_id', 'fecha_pago']);
        });

        if (! Schema::hasTable('pagos_consumos')) Schema::create('pagos_consumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
            $table->foreignId('consumo_id')->constrained('consumos')->cascadeOnDelete();
            $table->decimal('monto_aplicado', 10, 2);
            $table->timestamps();
            $table->unique(['pago_id', 'consumo_id']);
        });
    }

    public function down(): void
    {
        // Recovered transactional tables are intentionally preserved on rollback.
    }
};
