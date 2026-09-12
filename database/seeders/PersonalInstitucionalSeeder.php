<?php

namespace Database\Seeders;

use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Domains\Institucional\Models\PersonalInstitucional;
use Illuminate\Database\Seeder;
use LogicException;

class PersonalInstitucionalSeeder extends Seeder
{
    public array $personal = [];

    public function run(array $users = [], array $cargos = []): void
    {
        if (! isset($users['coordinacion'], $cargos['coordinacion'], $cargos['docencia'])) {
            throw new LogicException('Este fixture requiere referencias explícitas del orquestador BaseLimpiaAvalanchaSeeder; no resuelve cuentas por correo, nombre ni CI.');
        }
        foreach ([
            ['coordinacion', 'Marco Antonio', 'Torrez Quispe', 'coordinacion', null, null, null],
            ['docente_1', 'Rodrigo', 'Salazar Condori', 'docencia', '7300101', '72010001', 'rodrigo.salazar@avalancha.edu.bo'],
            ['docente_2', 'Carla', 'Mendoza Rojas', 'docencia', '7300102', '72010002', 'carla.mendoza@avalancha.edu.bo'],
            ['docente_3', 'Luis Fernando', 'Arce Huanca', 'docencia', '7300103', '72010003', 'luis.arce@avalancha.edu.bo'],
            ['docente_4', 'Patricia', 'Vargas Choque', 'docencia', '7300104', '72010004', 'patricia.vargas@avalancha.edu.bo'],
        ] as [$key, $nombres, $apellidos, $cargoKey, $ci, $celular, $correo]) {
            // Idempotence by the already-explicit account ID, never by identifying/contact text.
            $this->personal[$key] = PersonalInstitucional::unguarded(fn () => PersonalInstitucional::firstOrCreate(['user_id' => $users[$key]->id], [
                'nombres' => $nombres, 'apellidos' => $apellidos, 'cargo_id' => $cargos[$cargoKey]->id_cargo,
                'estado' => EstadoPersonal::ACTIVO, 'ci' => $ci, 'celular' => $celular, 'correo_contacto' => $correo,
            ]));
        }

        foreach ([
            'demo_personal_sin_tutor' => ['Elena', 'Mamani Flores', '7900001', '71000001', 'elena.mamani@avalancha.edu.bo'],
            'demo_tutor_sin_asignacion' => ['Javier', 'Quispe Condori', '7900002', '71000002', 'javier.quispe@avalancha.edu.bo'],
        ] as $key => [$nombres, $apellidos, $ci, $celular, $correo]) {
            $this->personal[$key] = PersonalInstitucional::unguarded(fn () => PersonalInstitucional::firstOrCreate(
                ['ci' => $ci],
                [
                    'user_id' => null,
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'cargo_id' => $cargos['docencia']->id_cargo,
                    'estado' => EstadoPersonal::ACTIVO,
                    'celular' => $celular,
                    'correo_contacto' => $correo,
                ],
            ));
        }
    }
}
