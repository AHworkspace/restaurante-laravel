<?php

namespace Database\Seeders;

use App\Models\ConversionesUnidades;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;

class ConversionesUnidadesSeeder extends Seeder
{
    public function run():void
    {
        $unidades=UnidadMedida::all()->keyBy(fn($unidad)=>mb_strtolower($unidad->nombre));
        $conversiones=[
            ['Gramo','Kilogramo',0.001],
            ['Miligramo','Kilogramo',0.000001],
            ['Libra','Kilogramo',0.48],
            ['Onza','Kilogramo',0.03],
            ['Cuartilla','Kilogramo',3],
            ['Arroba','Kilogramo',12],
            ['Quintal','Kilogramo',48],
            ['Tonelada','Kilogramo',1000],
            ['Mililitro','Litro',0.001],
            ['Centimetro','Metro',0.01],
            ['Docena','Unidad',12],
        ];

        ConversionesUnidades::query()->delete();
        foreach($conversiones as [$origen,$destino,$factor]){
            $unidadOrigen=$unidades->get(mb_strtolower($origen));
            $unidadDestino=$unidades->get(mb_strtolower($destino));
            if($unidadOrigen&&$unidadDestino)ConversionesUnidades::create(['unidad_origen_id'=>$unidadOrigen->id,'unidad_destino_id'=>$unidadDestino->id,'factor_conversion'=>$factor]);
        }
    }
}
