<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE consumos DROP CONSTRAINT IF EXISTS consumos_estado_pago_check');
            DB::statement("ALTER TABLE consumos ADD CONSTRAINT consumos_estado_pago_check CHECK (estado_pago IN ('pendiente', 'parcial', 'pagado', 'cancelado'))");
        }
    }

    public function down(): void
    {
        // Partial payment records must remain valid.
    }
};
