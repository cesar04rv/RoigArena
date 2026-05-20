<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            EventoSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Base de datos poblada correctamente');
        $this->command->info('   - 4 usuarios (1 admin, 3 regulares)');
        $this->command->info('   - 6 eventos personalizados');
        $this->command->info('   - 4 sectores (A, B, C, D)');
        $this->command->info('   - 240 asientos (60 por sector)');
    }
}