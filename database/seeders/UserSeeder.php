<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        DB::table('users')->insert([
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),
            'is_admin' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuarios regulares
        $usuarios = [
            ['nombre' => 'Juan', 'apellido' => 'Pérez', 'email' => 'juan@example.com'],
            ['nombre' => 'María', 'apellido' => 'García', 'email' => 'maria@example.com'],
            ['nombre' => 'Carlos', 'apellido' => 'López', 'email' => 'carlos@example.com'],
        ];

        foreach ($usuarios as $usuario) {
            DB::table('users')->insert([
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'email' => $usuario['email'],
                'password' => Hash::make('password'),
                'is_admin' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Usuarios creados: 4 (1 admin, 3 regulares)');
    }
}