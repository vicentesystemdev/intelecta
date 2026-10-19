<?php

namespace App\Domains\Postulantes\Services;

use App\Domains\Academico\Services\AccesoEvaluacionService;
use App\Domains\Postulantes\Models\Postulante;
use App\Http\Middleware\EnsureStudentAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditoriaIdentidadService
{
    public function __construct(private readonly ConciliacionIdentidadService $conciliacion, private readonly AccesoEvaluacionService $acceso) {}

    /** Catálogo de auditoría, NO se ejecuta ningún seeder ni se generan cuentas. */
    public function fixture(int $index): array
    {
        $names = ['Valeria', 'Diego', 'Mariana', 'Alejandro', 'Camila', 'Santiago', 'Lucía', 'Mateo', 'Fernanda', 'Nicolás', 'Daniela', 'Joaquín', 'Andrea', 'Sebastián', 'Paola', 'Gabriel', 'Natalia', 'Martín', 'Brenda', 'Álvaro', 'Roxana', 'Miguel', 'Carolina', 'Esteban'];
        $surnames = ['Choque', 'Mamani', 'Quispe', 'Flores', 'Condori', 'Rojas', 'Huanca', 'Apaza', 'Vargas', 'Mendoza', 'Salazar', 'Torrez', 'Nina', 'Callisaya', 'Laura', 'Poma', 'Ticona', 'Cruz', 'Miranda', 'Gutiérrez', 'Alarcón', 'Fernández', 'López', 'Ramos'];
        [$name, $last, $email] = match ($index) {
            0 => ['Valeria Nina', 'Choque', 'valeria.nina@postulante.avalancha.edu.bo'],
            1 => ['Diego', 'Mamani Flores', 'diego.mamani@postulante.avalancha.edu.bo'],
            2 => ['Mariana', 'Quispe Rojas', 'mariana.quispe@postulante.avalancha.edu.bo'],
            default => [$names[$index % 24], $surnames[($index * 5) % 24].' '.$surnames[(($index * 7) + 3) % 24],
                Str::slug(Str::ascii($names[$index % 24].'.'.$surnames[($index * 5) % 24].'.'.$index), '.').'@postulante.avalancha.edu.bo'],
        };

        return ['nombres_post' => $name, 'apellidos_post' => $last, 'email_post' => $email,
            'ci_post' => (string) (9100000 + $index), 'gestion_post' => 2026,
            'celular_post' => '765'.str_pad((string) (10000 + $index), 5, '0', STR_PAD_LEFT),
            'turno_post' => ['Mañana', 'Tarde', 'Noche', 'Fin de Semana'][$index % 4],
            'estado_post' => $index % 19 === 0 && $index > 2 ? 'inactivo' : 'activo'];
    }

    public function generar(): array
    {
        $students = User::role('Estudiante')->with('roles')->orderBy('id')->get();
        $profiles = Postulante::withTrashed()->with(['user.roles', 'matriculasAcademicas.cuotas', 'inscripcionesAcademicas.programa', 'habilitacionesAcademicas'])->orderBy('id_post')->get();
        $rows = [];
        $matched = [];
        foreach ($profiles as $post) {
            $index = (int) $post->ci_post - 9100000;
            $fixture = $index >= 0 && $index < 72 ? $this->fixture($index) : null;
            $sourceMatch = $fixture && collect($fixture)->every(fn ($value, $key) => (string) $post->$key === (string) $value);
            $sourceMat = $sourceMatch && $post->matriculasAcademicas->contains('codigo_mat', 'AVA-2026-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT));
            // Solo las primeras tres cuentas están referenciadas explícitamente en el origen.
            $candidates = $sourceMatch && $sourceMat && $index < 3
                ? $students->filter(fn ($user) => $user->name === $fixture['nombres_post'].' '.$fixture['apellidos_post'] && $user->email === $fixture['email_post'])
                : collect();
            $probable = $students->filter(fn ($user) => $user->name === $post->nombres_post.' '.$post->apellidos_post || $user->email === $post->email_post);
            $user = $post->user ?? ($candidates->count() === 1 ? $candidates->first() : ($probable->count() === 1 ? $probable->first() : null));
            $class = $post->user ? 'A' : ($candidates->count() === 1 ? 'A' : ($probable->count() === 1 ? 'B' : 'D'));
            // Los homónimos solo vuelven ambigua una búsqueda de candidatos.
            // Una FK ya confirmada no debe degradarse por compartir el nombre
            // con otra cuenta legítima; sus duplicidades reales se validan abajo.
            $conflict = $post->trashed() || $candidates->count() > 1 || (! $post->user && $probable->count() > 1)
                || $profiles->where('ci_post', $post->ci_post)->count() > 1
                || ($user && ($user->getRoleNames()->diff(['Estudiante'])->isNotEmpty()
                    || $profiles->where('user_id', $user->id)->where('id_post', '!=', $post->id_post)->isNotEmpty()));
            if ($conflict) {
                $class = 'E';
            }
            if ($user) {
                $matched[] = $user->id;
            }
            $decision = $this->acceso->evaluar($post);
            $rows[] = [
                'user_id' => $user?->id, 'user_id_actual' => $post->user_id, 'id_post' => $post->id_post,
                'nombre' => $post->nombres_post.' '.$post->apellidos_post, 'correo' => $user?->email ?? $post->email_post,
                'documento' => $post->ci_post, 'estado_usuario' => $user?->estado_cuenta?->value,
                'estado_postulante' => $post->estado_post,
                'matriculas' => $post->matriculasAcademicas->map->only(['id_mat', 'codigo_mat', 'id_insc', 'id_prog', 'estado_matricula_mat'])->all(),
                'inscripciones' => $post->inscripcionesAcademicas->map(fn ($insc) => ['id_insc' => $insc->id_insc, 'id_prog' => $insc->id_prog, 'programa' => $insc->programa?->codigo_prog, 'estado' => $insc->estado_inscripcion])->all(),
                'habilitaciones' => $post->habilitacionesAcademicas->map->only(['id_hab', 'estado_hab', 'habilitado_evaluaciones_hab', 'habilitado_simulacros_hab'])->all(),
                'cuotas_por_estado' => $post->matriculasAcademicas->flatMap->cuotas->countBy('estado_cuota')->all(),
                'evaluacion_administrativa' => $decision['permitido'], 'motivo_restriccion' => $decision['motivo'],
                'coincide_fixture' => (bool) ($sourceMatch && $sourceMat), 'clasificacion' => $class,
                'evidencia' => $post->user ? 'Vínculo FK ya existente; no inferido por contacto.' : ($class === 'A'
                    ? 'RolesAndUsersSeeder studentUsers['.$index.'] → BaseLimpiaAvalanchaSeeder seedApplicants['.$index.']; CI, nombre, contacto, gestión, estado y código de matrícula coincidentes.'
                    : ($class === 'B' ? 'Coincidencia de nombre o correo; NO acredita identidad, requiere documentos.' : ($class === 'E' ? 'Conflicto, archivo o duplicidad; revisión manual obligatoria.' : 'Sin cuenta referenciada; no inferir una pareja.'))),
                'accion_propuesta' => $post->user ? 'conservar' : match ($class) {
                    'A' => 'vincular_solo_con_autorizacion', 'B', 'E' => 'revision_manual', default => 'evaluar_alta_de_cuenta_autorizada'
                },
                'confianza' => $class === 'A' ? 'alta_para_origen_documentado_no_prueba_civil' : ($class === 'D' ? 'sin_pareja' : 'insuficiente'),
                'revision' => $post->user ? 'vinculo_existente_no_revalidado_documentalmente' : 'pendiente',
                'huella_usuario' => $user ? $this->conciliacion->huella($user) : null,
                'huella_expediente' => $this->conciliacion->huella($post),
            ];
        }
        foreach ($students->whereNotIn('id', $matched) as $user) {
            $rows[] = ['user_id' => $user->id, 'id_post' => null, 'nombre' => $user->name, 'correo' => $user->email,
                'estado_usuario' => $user->estado_cuenta->value, 'clasificacion' => 'C', 'evidencia' => 'Sin expediente candidato; solicitar documentación.',
                'accion_propuesta' => 'revision_manual', 'confianza' => 'sin_pareja', 'revision' => 'pendiente'];
        }
        $repeated = collect($rows)->whereNotNull('user_id')->countBy('user_id')->filter(fn ($count) => $count > 1)->keys();
        foreach ($rows as &$row) {
            if ($repeated->contains($row['user_id'])) {
                $row['clasificacion'] = 'E';
                $row['evidencia'] = 'Una cuenta aparece en varios expedientes candidatos; no conciliar automáticamente.';
                $row['accion_propuesta'] = 'revision_manual';
                $row['confianza'] = 'insuficiente';
            }
        }
        unset($row);
        $access = $students->map(function (User $user): array {
            $request = Request::create('/estudiante/evaluaciones', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
            $request->setUserResolver(fn () => $user);
            $response = app(EnsureStudentAccess::class)->handle($request, fn () => response()->json(['identidad' => 'OK']));
            $post = $user->postulante()->first();

            return ['user_id' => $user->id, 'correo' => $user->email, 'cuenta_activa_verificada' => $user->cuentaActiva(),
                'id_post' => $post?->id_post, 'middleware_identidad_http' => $response->getStatusCode(),
                'evaluacion_permitida' => $user->cuentaActiva() && $this->acceso->evaluar($post)['permitido'],
                'mensaje_middleware' => json_decode($response->getContent(), true)];
        })->all();
        $sources = [];
        foreach (['database/seeders/RolesAndUsersSeeder.php', 'database/seeders/BaseLimpiaAvalanchaSeeder.php'] as $file) {
            $sources[$file] = hash_file('sha256', base_path($file));
        }

        return ['generado' => now()->toIso8601String(), 'destino' => $this->conciliacion->destino(), 'fuentes_sha256' => $sources,
            'limite_origen' => 'Coincidencia reproducible con fixtures DEMO; no existe aquí un acta de importación que pruebe exclusividad histórica. No acredita identidad civil.',
            'resumen' => ['cuentas_estudiante' => $students->count(), 'expedientes' => $profiles->count(),
                'vinculos_actuales' => $profiles->whereNotNull('user_id')->count(),
                'cuentas_sin_vinculo' => $students->filter(fn ($user) => ! $profiles->contains('user_id', $user->id))->count(),
                'expedientes_sin_cuenta_asignada' => $profiles->whereNull('user_id')->count(),
                'expedientes_sin_cuenta_candidata' => collect($rows)->where('clasificacion', 'D')->count(),
                'coinciden_fixture' => collect($rows)->where('coincide_fixture', true)->count(),
                'evaluacion_administrativa_permitida' => collect($rows)->where('evaluacion_administrativa', true)->count(),
                'clasificaciones' => collect($rows)->countBy('clasificacion')->all()],
            'cuentas' => $access, 'filas' => $rows];
    }
}
