<?php

namespace Tests\Feature;

use App\Models\Consumo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PagoParcialTest extends TestCase
{
    use DatabaseTransactions;

    public function test_period_detail_returns_pending_consumptions_and_balance(): void
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();
        $consumo = Consumo::whereIn('estado_pago', ['pendiente', 'parcial'])->firstOrFail();
        $fecha = $consumo->fecha_consumo->toDateString();

        $response = $this->actingAs($user)->getJson(route('pagos.consumos-periodo', [
            'consumidor_id' => $consumo->consumidor_id,
            'tipo' => 'dia',
            'desde' => $fecha,
            'hasta' => $fecha,
        ]));

        $response->assertOk()
            ->assertJsonPath('periodo', "dia:{$fecha}:{$fecha}")
            ->assertJsonStructure(['total_pendiente', 'consumos' => [['id', 'fecha', 'hora', 'plato', 'cantidad', 'total', 'pagado', 'saldo', 'estado']]]);
        $this->assertContains($consumo->id, collect($response->json('consumos'))->pluck('id'));
    }

    public function test_multiple_selected_days_are_accepted(): void
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();
        $consumo = Consumo::whereIn('estado_pago', ['pendiente', 'parcial'])->firstOrFail();
        $fecha = $consumo->fecha_consumo->toDateString();
        $otraFecha = $consumo->fecha_consumo->copy()->addDays(2)->toDateString();

        $response = $this->actingAs($user)->getJson(route('pagos.consumos-periodo', [
            'consumidor_id' => $consumo->consumidor_id,
            'tipo' => 'dia',
            'valores' => $fecha.','.$otraFecha,
        ]));

        $response->assertOk()->assertJsonPath('periodo', "seleccion:dia:{$fecha},{$otraFecha}");
        $this->assertContains($consumo->id, collect($response->json('consumos'))->pluck('id'));
    }

    public function test_selected_iso_week_returns_its_pending_consumptions(): void
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();
        $consumo = Consumo::whereIn('estado_pago', ['pendiente', 'parcial'])->firstOrFail();
        $semana = $consumo->fecha_consumo->format('o-\\WW');

        $response = $this->actingAs($user)->getJson(route('pagos.consumos-periodo', [
            'consumidor_id' => $consumo->consumidor_id,
            'tipo' => 'semana',
            'valores' => $semana,
        ]));

        $response->assertOk()->assertJsonPath('periodo', "seleccion:semana:{$semana}");
        $this->assertContains($consumo->id, collect($response->json('consumos'))->pluck('id'));
    }

    public function test_selected_date_range_returns_consumptions_without_duplicates(): void
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();
        $consumo = Consumo::whereIn('estado_pago', ['pendiente', 'parcial'])->firstOrFail();
        $inicio = $consumo->fecha_consumo->copy()->subDay()->toDateString();
        $fin = $consumo->fecha_consumo->copy()->addDay()->toDateString();
        $rango = $inicio.'~'.$fin;

        $response = $this->actingAs($user)->getJson(route('pagos.consumos-periodo', [
            'consumidor_id' => $consumo->consumidor_id,
            'tipo' => 'rango',
            'valores' => $rango.','.$rango,
        ]));

        $response->assertOk()->assertJsonPath('periodo', "seleccion:rango:{$rango}");
        $ids = collect($response->json('consumos'))->pluck('id');
        $this->assertContains($consumo->id, $ids);
        $this->assertSame($ids->unique()->count(), $ids->count());
    }

    public function test_specific_partial_payment_records_application_and_remaining_balance(): void
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();
        $consumo = Consumo::whereIn('estado_pago', ['pendiente', 'parcial'])->firstOrFail();
        $pagadoAntes = (float) $consumo->pagos()->sum('pagos_consumos.monto_aplicado');
        $saldoAntes = (float) $consumo->total - $pagadoAntes;
        $monto = min(0.01, $saldoAntes);

        $response = $this->actingAs($user)->post(route('pagos.store'), [
            'consumidor_id' => $consumo->consumidor_id,
            'monto' => $monto,
            'tipo_pago' => 'consumo_especifico',
            'metodo_pago' => 'efectivo',
            'fecha_pago' => now()->toDateString(),
            'hora_pago' => now()->format('H:i'),
            'consumo_ids' => [$consumo->id],
        ]);

        $pagoId = DB::table('pagos')->latest('id')->value('id');
        $response->assertRedirect(route('pagos.index'));
        $this->assertDatabaseHas('pagos_consumos', [
            'pago_id' => $pagoId,
            'consumo_id' => $consumo->id,
            'monto_aplicado' => $monto,
        ]);
        $consumo = $consumo->fresh();
        $this->assertEqualsWithDelta($saldoAntes - $monto, $consumo->saldoPendiente(), 0.001);
        $this->assertSame($consumo->saldoPendiente() > 0 ? 'parcial' : 'pagado', $consumo->estado_pago);
    }
}
