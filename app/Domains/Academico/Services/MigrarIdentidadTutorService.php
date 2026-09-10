<?php

namespace App\Domains\Academico\Services;

use App\Support\Validation\InputRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/** One-time, structural migration. Query builder deliberately includes archived tutors. */
class MigrarIdentidadTutorService
{
    public const FIELDS = ['nombres_tutor' => 'nombres', 'apellidos_tutor' => 'apellidos', 'ci_tutor' => 'ci', 'celular_tutor' => 'celular', 'correo_tutor' => 'correo_contacto'];

    public function execute(bool $dryRun = true): array
    {
        if (! Schema::hasColumn('tutores_academicos', 'personal_id')) {
            throw new RuntimeException('Aplica primero la migración aditiva de personal_id.');
        }

        return DB::transaction(function () use ($dryRun): array {
            // Lock all writers, including legacy CRUD and independent Personal account linkage.
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('LOCK TABLE users, personal_institucional, tutores_academicos IN SHARE ROW EXCLUSIVE MODE');
            }
            if (! Schema::hasColumn('tutores_academicos', 'user_id')) {
                return ['fase' => 'finalizada', 'conflictos' => 0, 'registros' => DB::table('tutores_academicos as t')->join('personal_institucional as p', 'p.id_personal', '=', 't.personal_id')->select('t.id_tutor', 't.personal_id', 'p.user_id')->orderBy('t.id_tutor')->get()->toArray()];
            }
            $people = DB::table('personal_institucional')->get()->keyBy('id_personal');
            $tutors = DB::table('tutores_academicos')->orderBy('id_tutor')->get();
            $users = DB::table('users')->pluck('id')->all();
            $plans = [];
            $seenUsers = [];
            $seenCi = [];
            $reserved = $tutors->whereNotNull('personal_id')->pluck('id_tutor', 'personal_id')->all();
            foreach ($tutors as $tutor) {
                $errors = [];
                $person = $tutor->personal_id !== null ? $people->get($tutor->personal_id)
                    : ($tutor->user_id !== null ? $people->firstWhere('user_id', $tutor->user_id) : null);
                if ($tutor->personal_id !== null && ! $person) {
                    $errors[] = 'personal_id inexistente';
                }
                if ($tutor->user_id !== null) {
                    if (! in_array($tutor->user_id, $users, true)) {
                        $errors[] = 'User explícito inexistente';
                    }
                    if (isset($seenUsers[$tutor->user_id])) {
                        $errors[] = 'User repetido entre tutores';
                    }
                    $seenUsers[$tutor->user_id] = true;
                }
                if ($person && $person->user_id !== $tutor->user_id) {
                    $errors[] = 'Vínculo User incompatible';
                }
                if ($person && isset($reserved[$person->id_personal]) && $reserved[$person->id_personal] !== $tutor->id_tutor) {
                    $errors[] = 'Personal reservado por otro Tutor, incluido archivado';
                }
                $values = [];
                $updates = [];
                foreach (self::FIELDS as $legacy => $field) {
                    $source = $tutor->{$legacy};
                    $target = $person?->{$field};
                    if ($source !== null && $target !== null && $source !== $target) {
                        $errors[] = 'Valores distintos: '.$field;
                    }
                    $values[$field] = $target ?? $source;
                    if ($person && $target === null && $source !== null) {
                        $updates[$field] = $source;
                    }
                }
                // Validate without trimming, normalizing, truncating or changing existing information.
                $validator = Validator::make($values, [
                    'nombres' => ['required', ...InputRules::person()], 'apellidos' => ['required', ...InputRules::person()],
                    'ci' => ['nullable', ...InputRules::document()], 'celular' => ['nullable', ...InputRules::phone()],
                    'correo_contacto' => ['nullable', ...InputRules::email()],
                ]);
                foreach ($validator->errors()->keys() as $field) {
                    $errors[] = 'Valor inválido o ausente: '.$field;
                }
                if ($values['ci'] !== null) {
                    $owner = $people->firstWhere('ci', $values['ci']);
                    if (($owner && $owner->id_personal !== $person?->id_personal) || isset($seenCi[$values['ci']])) {
                        $errors[] = 'CI reservado por otra identidad';
                    }
                    $seenCi[$values['ci']] = true;
                }
                if ($person) {
                    $reserved[$person->id_personal] = $tutor->id_tutor;
                }
                $plans[] = ['id_tutor' => $tutor->id_tutor, 'personal_id' => $person?->id_personal, 'user_id' => $tutor->user_id,
                    'clasificacion' => $errors ? 'CONFLICTO' : (! $person || $updates ? 'INCOMPLETO' : 'COMPATIBLE'),
                    'errores' => $errors, 'crear_personal' => ! $person, 'campos_completar' => array_keys($updates),
                    'vincular' => $tutor->personal_id === null, '_values' => $values, '_updates' => $updates];
            }
            $conflicts = count(array_filter($plans, fn ($plan) => $plan['errores'] !== []));
            if (! $dryRun && ! $conflicts) {
                foreach ($plans as &$plan) {
                    if ($plan['crear_personal']) {
                        $plan['personal_id'] = DB::table('personal_institucional')->insertGetId([
                            ...$plan['_values'], 'user_id' => $plan['user_id'], 'cargo_id' => null, 'estado' => 'pendiente',
                            'created_at' => now(), 'updated_at' => now(),
                        ], 'id_personal');
                    } elseif ($plan['_updates']) {
                        DB::table('personal_institucional')->where('id_personal', $plan['personal_id'])->update([...$plan['_updates'], 'updated_at' => now()]);
                    }
                    if ($plan['vincular']) {
                        // Preserve original tutor timestamps and all academic/professional columns.
                        DB::table('tutores_academicos')->where('id_tutor', $plan['id_tutor'])->update(['personal_id' => $plan['personal_id']]);
                    }
                }
                unset($plan);
            }

            return ['fase' => $dryRun ? 'dry-run' : ($conflicts ? 'detenida_sin_escrituras' : 'aplicada'), 'conflictos' => $conflicts,
                'registros' => array_map(function ($plan) {
                    unset($plan['_values'], $plan['_updates']);

                    return $plan;
                }, $plans)];
        });
    }

    public function assertReadyToFinalize(): void
    {
        $report = $this->execute();
        foreach ($report['registros'] as $record) {
            if ($record['errores'] || $record['vincular'] || $record['crear_personal'] || $record['campos_completar']) {
                throw new RuntimeException('Cobertura/consistencia incompleta. Ejecuta tutores:migrar-identidad --dry-run y resuelve el informe antes de retirar columnas.');
            }
        }
    }
}
