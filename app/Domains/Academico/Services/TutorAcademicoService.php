<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\TutorAcademicoData;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Repositories\TutorAcademicoRepository;
use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Institucional\Services\OrganizacionService;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TutorAcademicoService
{
    public function __construct(private readonly TutorAcademicoRepository $repository) {}

    public function index(array $filters, User $actor): array
    {
        return [
            'tutores' => $this->repository->paginate($filters, 12, $actor->canManageOrganization('personal.ver')),
            'especialidades' => $this->repository->especialidades(),
            'personas' => $actor->can('tutores.crear') ? $this->repository->personalOptions() : [],
            'capacidades' => [
                'crear' => $actor->can('tutores.crear'), 'editar' => $actor->can('tutores.editar'),
                'editarPersonal' => $actor->canManageOrganization('personal.editar'),
                'verPersonal' => $actor->canManageOrganization('personal.ver'),
            ],
        ];
    }

    public function show(TutorAcademico $tutor, User $actor): TutorAcademico
    {
        return $this->repository->findForDetail($tutor, $actor->canManageOrganization('personal.ver'));
    }

    public function save(TutorAcademicoData $data, User $actor, ?TutorAcademico $tutor = null): TutorAcademico
    {
        $actor = $actor->fresh();
        if (! $actor?->cuentaActiva() || ! $actor->can($tutor ? 'tutores.editar' : 'tutores.crear')) {
            throw new AuthorizationException;
        }
        if ($data->personal !== null && ! $actor->canManageOrganization('personal.editar')) {
            throw new AuthorizationException('Editar identidad requiere personal.editar.');
        }
        try {
            return DB::transaction(function () use ($data, $actor, $tutor): TutorAcademico {
                $personal = PersonalInstitucional::lockForUpdate()->findOrFail($data->personalId);
                $record = $tutor ? TutorAcademico::without('personal')->lockForUpdate()->findOrFail($tutor->id_tutor) : null;
                if ($record && (int) $record->personal_id !== $data->personalId) {
                    throw ValidationException::withMessages(['personal_id' => 'La identidad del tutor no puede reasignarse desde este formulario.']);
                }
                if (! $record && $personal->estado === EstadoPersonal::INACTIVO) {
                    throw ValidationException::withMessages(['personal_id' => 'Selecciona Personal activo o pendiente.']);
                }
                if (TutorAcademico::withTrashed()->where('personal_id', $personal->id_personal)
                    ->when($record, fn ($query) => $query->where('id_tutor', '<>', $record->id_tutor))->exists()) {
                    throw ValidationException::withMessages(['personal_id' => 'Personal ya tiene un tutor, incluso si está archivado.']);
                }
                if ($data->personal !== null) {
                    app(OrganizacionService::class)->savePersonal($data->personal, $actor, $personal);
                }
                $fields = $record ? array_keys(array_filter($data->toArray(), fn ($value, $key) => $record->{$key} !== $value, ARRAY_FILTER_USE_BOTH)) : array_keys($data->toArray());
                $saved = $record ? $this->repository->update($record, $data) : $this->repository->create($data);
                app(BitacoraService::class)->registrar([
                    'user_id' => $actor->id, 'nombre_usuario' => $actor->name, 'correo_usuario' => $actor->email,
                    'rol_usuario' => $actor->getRoleNames()->first(), 'modulo' => 'Tutores académicos',
                    'accion' => $record ? 'editar_tutor' : 'crear_tutor', 'entidad' => 'tutores_academicos',
                    'entidad_id' => $saved->id_tutor, 'valores_nuevos' => ['personal_id' => $personal->id_personal, 'campos_modificados' => $fields],
                ]);

                return $saved;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['personal_id' => 'Otra operación reservó esa identidad. Actualiza el listado.']);
        }
    }
}
