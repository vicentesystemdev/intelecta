<?php

namespace App\Http\Requests\Institucional;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AsistenciaGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sesion_asist' => trim((string) $this->input('sesion_asist')) ?: 'General',
        ]);
    }

    public function rules(): array
    {
        return [
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['required', 'integer', 'exists:grupos_academicos,id_grupo'],
            'id_tutor' => ['nullable', 'integer', 'exists:tutores_academicos,id_tutor'],
            'fecha_asist' => ['required', 'date'],
            'sesion_asist' => ['required', 'string', 'max:120'],
            'registros' => ['required', 'array', 'min:1'],
            'registros.*.id_post' => ['required', 'integer', 'distinct', 'exists:postulantes,id_post'],
            'registros.*.estado_asist' => ['required', Rule::in(['presente', 'ausente', 'retraso', 'justificado'])],
            'registros.*.observacion_asist' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $grupo = GrupoAcademico::find($this->integer('id_grupo'));
                if (! $grupo) {
                    return;
                }

                if ($this->filled('id_prog') && $grupo->id_prog !== $this->integer('id_prog')) {
                    $validator->errors()->add('id_prog', 'El grupo seleccionado no pertenece al programa indicado.');
                }

                $postulantes = collect($this->input('registros', []))
                    ->pluck('id_post')
                    ->map(fn ($id) => (int) $id)
                    ->unique();
                $inscritos = InscripcionAcademica::query()
                    ->where('id_grupo', $grupo->id_grupo)
                    ->where('estado_inscripcion', 'activo')
                    ->whereIn('id_post', $postulantes)
                    ->pluck('id_post');

                if ($inscritos->count() !== $postulantes->count()) {
                    $validator->errors()->add('registros', 'Todos los postulantes deben tener una inscripción activa en el grupo.');
                }
            },
        ];
    }
}
