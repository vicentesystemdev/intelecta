<?php

namespace Database\Factories;

use App\Domains\Academico\Models\TutorAcademico;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorAcademicoFactory extends Factory
{
    protected $model = TutorAcademico::class;

    public function definition(): array
    {
        return ['personal_id' => null, 'especialidad_tutor' => 'Matemática', 'formacion_tutor' => null,
            'experiencia_tutor' => null, 'estado_tutor' => 'activo', 'observacion_tutor' => null];
    }

    public function withPersonal(): static
    {
        return $this->state(['personal_id' => PersonalInstitucionalFactory::new()]);
    }
}
