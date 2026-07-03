<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('consumidor_etiqueta');
        Schema::dropIfExists('etiquetas_consumidores');
    }

    public function down(): void
    {
        // Labels were not part of the recovered historical system.
    }
};
