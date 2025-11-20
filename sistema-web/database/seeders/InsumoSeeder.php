<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Insumo;
use App\Models\Categoria;
class InsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        /*
        nombre',
        'descripcion',
        'stock_minimo',
        'imagen',
        'restaurante_id',
        'unidad_medida_id',
        */
        $categoria = Categoria::where('nombre', 'Otros')->first();

        Insumo::create([
            'nombre' => 'Arroz',
            'descripcion' => 'Arroz blanco',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Azucar',
            'descripcion' => 'Azucar blanca',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Aceite',
            'descripcion' => 'Aceite de cocina',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Sal',
            'descripcion' => 'Sal de mesa',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Pimienta',
            'descripcion' => 'Pimienta negra',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Cebolla',
            'descripcion' => 'Cebolla blanca',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Ajo',
            'descripcion' => 'Ajo fresco',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Papa',
            'descripcion' => 'Papa blanca',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Tomate',
            'descripcion' => 'Tomate rojo',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);

        Insumo::create([
            'nombre' => 'Lechuga',
            'descripcion' => 'Lechuga verde',
            'stock_minimo' => 10,
            'unidad_medida_id' => 1,
            'categoria_id' => $categoria->id
        ]);
    }
}
