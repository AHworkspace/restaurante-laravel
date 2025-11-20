<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Proveedor::create([
            'nombre' => 'Pepe Gonzaes',
            'direccion' => 'Direccion 1',
            'telefono' => '1234567890',
            'email' => 'pepe@gmail.com',
            'descripcion' => 'Proveedor de frutas',
        ]);

        Proveedor::create([
            'nombre' => 'Juan Perez',
            'direccion' => 'Direccion 2',
            'telefono' => '0987654321',
            'email' => 'juan@gmail.com',
            'descripcion' => 'Proveedor de verduras',
        ]);

        Proveedor::create([
            'nombre' => 'Maria Lopez',
            'direccion' => 'Direccion 3',
            'telefono' => '0987654321',
            'email' => 'maria@gmail.com',
            'descripcion' => 'Proveedor de carnes',
        ]);

        Proveedor::create([
            'nombre' => 'Jose Martinez',
            'direccion' => 'Direccion 4',
            'telefono' => '0987654321',
            'email' => 'jose@gmail.com',
            'descripcion' => 'Proveedor de lacteos',
        ]);

        Proveedor::create([
            'nombre' => 'Ana Rodriguez',
            'direccion' => 'Direccion 5',
            'telefono' => '0987654321',
            'email' => 'ana@gmail.com',
            'descripcion' => 'Proveedor de pan',
        ]);

        Proveedor::create([
            'nombre' => 'Pedro Perez',
            'direccion' => 'Direccion 6',
            'telefono' => '0987654321',
            'email' => 'pedo@gmail.com',
            'descripcion' => 'Proveedor de frutas',
        ]);

        Proveedor::create([
            'nombre' => 'Juanita Perez',
            'direccion' => 'Direccion 7',
            'telefono' => '0987654321',
            'email' => 'juanita@gmail.com',
            'descripcion' => 'Proveedor de verduras',
        ]);
    }
}
