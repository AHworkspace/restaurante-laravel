<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UnidadMedida;


class UnidadMedidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        UnidadMedida::create([
            'nombre' => 'Kilogramo',
            'abreviatura' => 'Kg',
            'descripcion' => 'Unidad de medida de peso',
        ]);

        UnidadMedida::create([
            'nombre' => 'Litro',
            'abreviatura' => 'L',
            'descripcion' => 'Unidad de medida de volumen',
        ]);

        UnidadMedida::create([
            'nombre' => 'Metro',
            'abreviatura' => 'm',
            'descripcion' => 'Unidad de medida de longitud',
        ]);

        UnidadMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'Ud',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Mililitro',
            'abreviatura' => 'ml',
            'descripcion' => 'Unidad de medida de volumen',
        ]);

        UnidadMedida::create([
            'nombre' => 'Miligramo',
            'abreviatura' => 'mg',
            'descripcion' => 'Unidad de medida de peso',
        ]);

        UnidadMedida::create([
            'nombre' => 'Centimetro',
            'abreviatura' => 'cm',
            'descripcion' => 'Unidad de medida de longitud',
        ]);

        UnidadMedida::create([
            'nombre' => 'Metro cuadrado',
            'abreviatura' => 'm2',
            'descripcion' => 'Unidad de medida de superficie',
        ]);

        UnidadMedida::create([
            'nombre' => 'Cuartilla',
            'abreviatura' => 'Cta',
            'descripcion' => 'Unidad de medida de volumen',
        ]);

        UnidadMedida::create([
            'nombre' => 'Arroba',
            'abreviatura' => 'Arr',
            'descripcion' => 'Unidad de medida de peso',
        ]);

        UnidadMedida::create([
            'nombre' => 'Libra',
            'abreviatura' => 'Lb',
            'descripcion' => 'Unidad de medida de peso',
        ]);

        UnidadMedida::create([
            'nombre' => 'Onza',
            'abreviatura' => 'Oz',
            'descripcion' => 'Unidad de medida de peso',
        ]);

        UnidadMedida::create([
            'nombre' => 'Quintal',
            'abreviatura' => 'Q',
            'descripcion' => 'Unidad de medida de peso',
        ]);

        UnidadMedida::create([
            'nombre' => 'Tonelada',
            'abreviatura' => 'Tn',
            'descripcion' => 'Unidad de medida de peso',
        ]);

        UnidadMedida::create([
            'nombre' => 'Barril',
            'abreviatura' => 'Brl',
            'descripcion' => 'Unidad de medida de volumen',
        ]);

        UnidadMedida::create([
            'nombre' => 'Caja',
            'abreviatura' => 'Cj',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Docena',
            'abreviatura' => 'Dz',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Bolsa',
            'abreviatura' => 'Bls',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Paquete',
            'abreviatura' => 'Pq',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Saco',
            'abreviatura' => 'Sc',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Bulto',
            'abreviatura' => 'Bt',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Frasco',
            'abreviatura' => 'Fco',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Lata',
            'abreviatura' => 'Lt',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);

        UnidadMedida::create([
            'nombre' => 'Botella',
            'abreviatura' => 'Bot',
            'descripcion' => 'Unidad de medida de cantidad',
        ]);
    }
}
