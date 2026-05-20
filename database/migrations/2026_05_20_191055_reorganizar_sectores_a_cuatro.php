<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Las migraciones ya manejan transacciones automáticamente
        
        // DESACTIVAR CHECKS DE FOREIGN KEYS
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // 1. BORRAR DATOS (tablas dependientes primero)
        DB::table('entradas')->truncate();
        DB::table('estado_asientos')->truncate();
        DB::table('precios')->truncate();
        DB::table('asientos')->truncate();
        DB::table('sectores')->truncate();
        
        // REACTIVAR CHECKS
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // 2. CREAR 4 NUEVOS SECTORES
        $sectores = [
            [
                'nombre' => 'SECTOR A',
                'descripcion' => 'Zona superior izquierda',
                'cantidad_filas' => 6,
                'cantidad_columnas' => 10,
                'color_hex' => '#dc2626',
                'activo' => true,
                'fila_inicio' => 1,
                'fila_fin' => 6,
                'columna_inicio' => 1,
                'columna_fin' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'SECTOR B',
                'descripcion' => 'Zona superior derecha',
                'cantidad_filas' => 6,
                'cantidad_columnas' => 10,
                'color_hex' => '#2563eb',
                'activo' => true,
                'fila_inicio' => 1,
                'fila_fin' => 6,
                'columna_inicio' => 11,
                'columna_fin' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'SECTOR C',
                'descripcion' => 'Zona inferior izquierda',
                'cantidad_filas' => 6,
                'cantidad_columnas' => 10,
                'color_hex' => '#16a34a',
                'activo' => true,
                'fila_inicio' => 7,
                'fila_fin' => 12,
                'columna_inicio' => 1,
                'columna_fin' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'SECTOR D',
                'descripcion' => 'Zona inferior derecha',
                'cantidad_filas' => 6,
                'cantidad_columnas' => 10,
                'color_hex' => '#9333ea',
                'activo' => true,
                'fila_inicio' => 7,
                'fila_fin' => 12,
                'columna_inicio' => 11,
                'columna_fin' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($sectores as $sector) {
            $sectorId = DB::table('sectores')->insertGetId($sector);
            
            // Crear asientos para este sector
            $asientos = [];
            for ($fila = $sector['fila_inicio']; $fila <= $sector['fila_fin']; $fila++) {
                for ($col = $sector['columna_inicio']; $col <= $sector['columna_fin']; $col++) {
                    $asientos[] = [
                        'sector_id' => $sectorId,
                        'fila' => $fila,
                        'numero' => $col,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            DB::table('asientos')->insert($asientos);
        }
        
        // 3. CREAR PRECIOS para cada evento existente
        $eventos = DB::table('eventos')->get();
        $nuevosSectores = DB::table('sectores')->get();
        
        foreach ($eventos as $evento) {
            foreach ($nuevosSectores as $sector) {
                // Precio por defecto según sector
                $precio = match($sector->nombre) {
                    'SECTOR A' => 50.00,
                    'SECTOR B' => 60.00,
                    'SECTOR C' => 70.00,
                    'SECTOR D' => 80.00,
                    default => 50.00,
                };
                
                DB::table('precios')->insert([
                    'evento_id' => $evento->id,
                    'sector_id' => $sector->id,
                    'precio' => $precio,
                    'disponible' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        // No hay vuelta atrás fácil, restaurar desde backup
    }
};