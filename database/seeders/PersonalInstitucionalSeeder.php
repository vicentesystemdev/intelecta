<?php

namespace Database\Seeders;

use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Domains\Institucional\Models\PersonalInstitucional;
use Illuminate\Database\Seeder;
use LogicException;

class PersonalInstitucionalSeeder extends Seeder
{
    public function run(array $users = [], array $cargos = []): void
    {
        if (! isset($users['coordinacion'], $cargos['coordinacion'], $cargos['docencia'])) {
            throw new LogicException('Este fixture requiere referencias explícitas del orquestador BaseLimpiaAvalanchaSeeder; no resuelve cuentas por correo, nombre ni CI.');
        }
        foreach ([
            ['coordinacion', 'Marco Antonio', 'Torrez Quispe', 'coordinacion'],
            ['docente_1', 'Rodrigo', 'Salazar Condori', 'docencia'],
            ['docente_2', 'Carla', 'Mendoza Rojas', 'docencia'],
            ['docente_3', 'Luis Fernando', 'Arce Huanca', 'docencia'],
            ['docente_4', 'Patricia', 'Vargas Choque', 'docencia'],
        ] as [$key, $nombres, $apellidos, $cargoKey]) {
            // Idempotence by the already-explicit account ID, never by identifying/contact text.
            PersonalInstitucional::unguarded(fn () => PersonalInstitucional::firstOrCreate(['user_id' => $users[$key]->id], [
                'nombres' => $nombres, 'apellidos' => $apellidos, 'cargo_id' => $cargos[$cargoKey]->id_cargo,
                'estado' => EstadoPersonal::ACTIVO, 'ci' => null, 'celular' => null, 'correo_contacto' => null,
            ]));
        }
    }
}
