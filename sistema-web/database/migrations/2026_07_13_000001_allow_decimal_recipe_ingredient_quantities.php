<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE recetas_insumos ALTER COLUMN cantidad TYPE DECIMAL(14,4) USING cantidad::DECIMAL(14,4)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE recetas_insumos ALTER COLUMN cantidad TYPE INTEGER USING ROUND(cantidad)::INTEGER');
    }
};
