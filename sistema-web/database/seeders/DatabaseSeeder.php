<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        /*
        // Crear roles
        $adminRoleId = \DB::table('roles')->insertGetId([
            'nombre' => 'Administrador',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cajeroRoleId = \DB::table('roles')->insertGetId([
            'nombre' => 'Cajero',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear usuario administrador
        $user = \App\Models\User::create([
            'nombre' => 'Admin',
            'apellido_paterno' => 'Principal',
            'apellido_materno' => 'Sistema',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'rol_id' => $adminRoleId
        ]);

        // Crear usuario cajero
        $user2 = \App\Models\User::create([
            'nombre' => 'Cajero',
            'apellido_paterno' => 'Caja',
            'apellido_materno' => 'Secundaria',
            'email' => 'cajero@gmail.com',
            'password' => bcrypt('cajero123'),
            'rol_id' => $cajeroRoleId
        ]);
*/
        // Crear Roles con Spatie, Administrador, Cocinero, Cajero, Mesero

        $directorRole = Role::create(['name' => 'director']);
        $adminRole = Role::create(['name' => 'admin']);
        $cocineroRole = Role::create(['name' => 'cocinero']);
        $ayudanteCocinaRole = Role::create(['name' => 'ayudante_cocina']);
        $cajeroRole = Role::create(['name' => 'cajero']);
        $meseroRole = Role::create(['name' => 'mesero']);

        $director = User::create([
            'nombre' => 'Pepito',
            'apellido_paterno' => 'Navas',
            'apellido_materno' => 'Director',
            'email' => 'director@gmail.com',
            'password' => bcrypt('director123'),
        ]);

        $director->assignRole($directorRole);

        $user = User::create([
            'nombre' => 'Admin',
            'apellido_paterno' => 'Principal',
            'apellido_materno' => 'Sistema',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
        ]);
        $user->assignRole($adminRole);

        // Crear usuario cocinero
        $cocinero = User::create([
            'nombre' => 'Pepe',
            'apellido_paterno' => 'Gomez',
            'apellido_materno' => 'Bolanios',
            'email' => 'cocinero@gmail.com',
            'password' => bcrypt('cocinero123'),
        ]);
        $cocinero->assignRole($cocineroRole);

        // Crear usuario ayudante de cocina
        $ayudante = User::create([
            'nombre' => 'Rodrigo',
            'apellido_paterno' => 'Ayudante',
            'apellido_materno' => 'Cocina',
            'email' => 'ayudante@gmail.com',
            'password' => bcrypt('ayudante123'),
        ]);

        $ayudante->assignRole($ayudanteCocinaRole);

        // Crear usuario cajero
        $cajero = User::create([
            'nombre' => 'Juan',
            'apellido_paterno' => 'Mamani',
            'apellido_materno' => 'Bolanios',
            'email' => 'cajero@gmail.com',
            'password' => bcrypt('cajero123'),
        ]);
        $cajero->assignRole($cajeroRole);

        // Crear usuario mesero
        $mesero = User::create([
            'nombre' => 'Maria',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Bolanios',
            'email' => 'mesero@gmail.com',
            'password' => bcrypt('mesero123'),
        ]);
        $mesero->assignRole($meseroRole);

        $this->call([
            UnidadMedidaSeeder::class,
            CategoriaSeeder::class,
            InsumoSeeder::class,
            ProveedorSeeder::class,
            RecetaSeeder::class,
            VentaSeeder::class,
            MovimientoSeeder::class,
        ]);
    }
}
