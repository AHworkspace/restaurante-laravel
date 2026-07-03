<?php

namespace Tests\Feature;

use App\Models\MenuDia;
use Carbon\Carbon;
use Tests\TestCase;

class MenuDashboardVisibilityTest extends TestCase
{
    public function test_menu_for_today_respects_its_time_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-28 01:45:00', 'America/La_Paz'));
        $menu = new MenuDia(['fecha' => '2026-06-28', 'hora_inicio' => '01:30', 'hora_fin' => '02:00']);
        $this->assertTrue($menu->pasaFiltroHorarioVisualizacion('2026-06-28'));

        Carbon::setTestNow(Carbon::parse('2026-06-28 03:00:00', 'America/La_Paz'));
        $this->assertFalse($menu->pasaFiltroHorarioVisualizacion('2026-06-28'));
        Carbon::setTestNow();
    }
}
