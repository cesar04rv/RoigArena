<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener sectores (deben existir: SECTOR A, B, C, D)
        $sectores = DB::table('sectores')->get()->keyBy('nombre');

        if ($sectores->isEmpty()) {
            $this->command->error('⚠ No hay sectores. Ejecuta la migración de sectores primero.');
            return;
        }

        // TUS 6 EVENTOS PERSONALIZADOS
        $eventos = [
            [
                'nombre' => 'Concierto Coldplay',
                'descripcion_corta' => 'La banda británica más icónica regresa',
                'descripcion_larga' => 'La banda británica más icónica regresa con su gira mundial Music of the Spheres',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/ColdplayWembley120925_%28cropped%29.jpg/250px-ColdplayWembley120925_%28cropped%29.jpg',
                'poster_ancho_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cc/ColdplayWembley120925_%28cropped%29.jpg/250px-ColdplayWembley120925_%28cropped%29.jpg',
                'fecha' => '2028-05-12',
                'hora' => '21:00:00',
                'precios' => ['SECTOR A' => 50, 'SECTOR B' => 60, 'SECTOR C' => 75, 'SECTOR D' => 150],
            ],
            [
                'nombre' => 'Final de Ping Pong Europea',
                'descripcion_corta' => 'El campeonato europeo de tenis de mesa',
                'descripcion_larga' => 'El campeonato europeo de tenis de mesa llega a su emocionante final',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/2012_Summer_Olympics_Men%27s_Team_Table_Tennis_Final_1.jpg/330px-2012_Summer_Olympics_Men%27s_Team_Table_Tennis_Final_1.jpg',
                'poster_ancho_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/2012_Summer_Olympics_Men%27s_Team_Table_Tennis_Final_1.jpg/330px-2012_Summer_Olympics_Men%27s_Team_Table_Tennis_Final_1.jpg',
                'fecha' => '2027-02-13',
                'hora' => '18:00:00',
                'precios' => ['SECTOR A' => 20, 'SECTOR B' => 10, 'SECTOR C' => 15, 'SECTOR D' => 45],
            ],
            [
                'nombre' => 'Evento de catas de vino',
                'descripcion_corta' => 'Descubre los mejores caldos de la región',
                'descripcion_larga' => 'Descubre los mejores caldos de la región con expertos sommeliers',
                'poster_url' => 'https://www.plateamadrid.com/wp-content/uploads/2024/02/CATA-DE-VINOS-EN-MADRID-PARA-EVENTOS-DE-EMPRESA-UNA-EXPERIENCIA-INOLVIDABLE-1.jpg',
                'poster_ancho_url' => 'https://www.plateamadrid.com/wp-content/uploads/2024/02/CATA-DE-VINOS-EN-MADRID-PARA-EVENTOS-DE-EMPRESA-UNA-EXPERIENCIA-INOLVIDABLE-1.jpg',
                'fecha' => '2026-05-31',
                'hora' => '19:00:00',
                'precios' => ['SECTOR A' => 5, 'SECTOR B' => 5, 'SECTOR C' => 5, 'SECTOR D' => 12],
            ],
            [
                'nombre' => 'Concierto conmemorativo QUEEN',
                'descripcion_corta' => 'Homenaje a la legendaria banda británica',
                'descripcion_larga' => 'Homenaje a la legendaria banda británica con sus mayores éxitos en vivo',
                'poster_url' => 'https://www.udiscovermusic.com/wp-content/uploads/2019/03/Queen-II-album-cover-820.jpg',
                'poster_ancho_url' => 'https://www.udiscovermusic.com/wp-content/uploads/2019/03/Queen-II-album-cover-820.jpg',
                'fecha' => '2026-06-30',
                'hora' => '21:30:00',
                'precios' => ['SECTOR A' => 35, 'SECTOR B' => 45, 'SECTOR C' => 50, 'SECTOR D' => 87],
            ],
            [
                'nombre' => 'Monólogo Juan Dávila',
                'descripcion_corta' => 'Una noche de humor sin filtros',
                'descripcion_larga' => 'Una noche de humor sin filtros con el cómico revelación del momento',
                'poster_url' => 'https://www.kursaal.eus/wp-content/uploads/2024/02/thumbnail_juan-davila_1080X180.jpg',
                'poster_ancho_url' => 'https://www.kursaal.eus/wp-content/uploads/2024/02/thumbnail_juan-davila_1080X180.jpg',
                'fecha' => '2026-09-11',
                'hora' => '20:00:00',
                'precios' => ['SECTOR A' => 90, 'SECTOR B' => 90, 'SECTOR C' => 95, 'SECTOR D' => 150],
            ],
            [
                'nombre' => 'Festival de Motocross',
                'descripcion_corta' => 'Acrobacias extremas y velocidad pura',
                'descripcion_larga' => 'Acrobacias extremas y velocidad pura en el campeonato nacional de freestyle',
                'poster_url' => 'https://img.redbull.com/images/c_fill,g_auto,w_1000,h_960/q_auto,f_auto/redbullcom/2020/9/15/y2umijbh6rqy5chmlawf/motocross-world-championship',
                'poster_ancho_url' => 'https://img.redbull.com/images/c_fill,g_auto,w_1000,h_960/q_auto,f_auto/redbullcom/2020/9/15/y2umijbh6rqy5chmlawf/motocross-world-championship',
                'fecha' => '2026-09-01',
                'hora' => '17:00:00',
                'precios' => ['SECTOR A' => 26, 'SECTOR B' => 10, 'SECTOR C' => 5, 'SECTOR D' => 15],
            ],
        ];

        foreach ($eventos as $eventoData) {
            // Crear evento
            $eventoId = DB::table('eventos')->insertGetId([
                'nombre' => $eventoData['nombre'],
                'descripcion_corta' => $eventoData['descripcion_corta'],
                'descripcion_larga' => $eventoData['descripcion_larga'],
                'poster_url' => $eventoData['poster_url'],
                'poster_ancho_url' => $eventoData['poster_ancho_url'],
                'fecha' => $eventoData['fecha'],
                'hora' => $eventoData['hora'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear precios para cada sector
            foreach ($sectores as $nombreSector => $sector) {
                $precio = $eventoData['precios'][$nombreSector] ?? 50;

                DB::table('precios')->insert([
                    'evento_id' => $eventoId,
                    'sector_id' => $sector->id,
                    'precio' => $precio,
                    'disponible' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info("  ✓ {$eventoData['nombre']}");
        }

        $this->command->info('✅ 6 eventos creados con éxito');
    }
}