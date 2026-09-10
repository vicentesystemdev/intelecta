<?php

namespace App\Domains\Institucional\Services;

use App\Domains\Institucional\Enums\EstadoCargo;
use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizacionService
{
    public function __construct(private readonly BitacoraService $bitacora) {}

    public function saveCargo(array $data, User $actor, ?Cargo $cargo = null): Cargo
    {
        $this->authorize($actor, $cargo ? 'cargos.editar' : 'cargos.crear');
        try {
            return DB::transaction(function () use ($data, $actor, $cargo): Cargo {
                $record = $cargo ? Cargo::lockForUpdate()->findOrFail($cargo->id_cargo) : new Cargo;
                $before = $record->only(['nombre_cargo', 'descripcion']);
                $record->fill(Arr::only($data, ['nombre_cargo', 'descripcion']));
                if (! $record->exists) {
                    $record->estado = EstadoCargo::ACTIVO;
                }
                $record->save();
                $this->audit($actor, $record, $cargo ? 'editar_cargo' : 'crear_cargo', $before, $record->only(['nombre_cargo', 'descripcion', 'estado']));

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['nombre_cargo' => 'Otra operación registró esa denominación. Actualiza el catálogo.']);
        }
    }

    public function savePersonal(array $data, User $actor, ?PersonalInstitucional $personal = null): PersonalInstitucional
    {
        $this->authorize($actor, $personal ? 'personal.editar' : 'personal.crear');
        try {
            return DB::transaction(function () use ($data, $actor, $personal): PersonalInstitucional {
                $record = $personal ? PersonalInstitucional::lockForUpdate()->findOrFail($personal->id_personal) : new PersonalInstitucional;
                $previousCargo = $record->cargo_id;
                $cargoId = array_key_exists('cargo_id', $data) ? ($data['cargo_id'] === null ? null : (int) $data['cargo_id']) : $previousCargo;
                // Preserve an existing inactive assignment; serialize NEW assignments with inactivation.
                if ($cargoId !== null && $cargoId !== $previousCargo) {
                    $cargo = Cargo::lockForUpdate()->find($cargoId);
                    if (! $cargo || $cargo->estado !== EstadoCargo::ACTIVO) {
                        throw ValidationException::withMessages(['cargo_id' => 'Selecciona un cargo activo para una nueva asignación.']);
                    }
                }
                $record->fill(Arr::only($data, ['nombres', 'apellidos', 'ci', 'celular', 'correo_contacto']));
                $record->cargo_id = $cargoId;
                if (! $record->exists) {
                    $record->estado = EstadoPersonal::PENDIENTE;
                }
                // Audit field names, not a second copy of CI/contact/person data.
                $changedFields = array_keys($record->getDirty());
                $record->save();
                $this->audit($actor, $record, $personal ? 'editar_personal' : 'crear_personal', null, ['campos_modificados' => $changedFields]);
                if ($previousCargo !== $cargoId) {
                    $this->audit($actor, $record, 'cambiar_cargo_personal', ['cargo_id' => $previousCargo], ['cargo_id' => $cargoId]);
                }

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['ci' => 'El C.I. ya está registrado para otra persona.']);
        }
    }

    public function changeState(Cargo|PersonalInstitucional $record, EstadoCargo|EstadoPersonal $state, User $actor): void
    {
        $isCargo = $record instanceof Cargo;
        $this->authorize($actor, $isCargo ? 'cargos.cambiar_estado' : 'personal.cambiar_estado');
        if (($isCargo && ! $state instanceof EstadoCargo) || (! $isCargo && ! $state instanceof EstadoPersonal)) {
            throw ValidationException::withMessages(['estado' => 'Selecciona un estado válido para esta entidad.']);
        }
        DB::transaction(function () use ($record, $state, $actor, $isCargo): void {
            $locked = $record->newQuery()->lockForUpdate()->findOrFail($record->getKey());
            $before = $locked->estado->value;
            $locked->estado = $state;
            $locked->save();
            $this->audit($actor, $locked, $isCargo ? 'cambiar_estado_cargo' : 'cambiar_estado_personal', ['estado' => $before], ['estado' => $state->value]);
        }, 3);
    }

    private function authorize(User $actor, string $permission): void
    {
        if (! $actor->fresh()?->canManageOrganization($permission)) {
            throw new AuthorizationException('No tienes autorización para esta operación institucional.');
        }
    }

    private function audit(User $actor, Model $record, string $action, ?array $before, array $after): void
    {
        $this->bitacora->registrar([
            'user_id' => $actor->id, 'nombre_usuario' => $actor->name, 'correo_usuario' => $actor->email,
            'rol_usuario' => $actor->getRoleNames()->first(), 'modulo' => 'Organización institucional',
            'accion' => $action, 'entidad' => $record->getTable(), 'entidad_id' => $record->getKey(),
            'valores_anteriores' => $before, 'valores_nuevos' => $after,
        ]);
    }
}
