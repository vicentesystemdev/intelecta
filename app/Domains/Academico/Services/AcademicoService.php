<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\GrupoAcademicoData;
use App\Domains\Academico\DTOs\InscripcionAcademicaData;
use App\Domains\Academico\DTOs\ProgramaAcademicoData;
use App\Domains\Academico\DTOs\SimulacroProgramadoData;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Repositories\AcademicoRepository;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcademicoService
{
    public function __construct(private readonly AcademicoRepository $repository) {}

    public function programas(array $filters): array
    {
        return [
            'programas' => $this->repository->paginateProgramas($filters),
            'universidades' => $this->repository->universidadesPrograma(),
            'modalidades' => $this->repository->modalidadesPrograma(),
        ];
    }

    public function grupos(array $filters, User $user): array
    {
        return [
            'grupos' => $this->repository->paginateGrupos($filters, $user),
            'programas' => $this->repository->programasOptionsPara($user, 'grupos.ver'),
        ];
    }

    public function inscripciones(array $filters): array
    {
        return [
            'inscripciones' => $this->repository->paginateInscripciones($filters),
            'programas' => $this->repository->programasOptions(true),
            'grupos' => $this->repository->gruposOptions(null, true),
            'postulantes' => $this->repository->postulantesOptions(),
        ];
    }

    public function simulacros(array $filters): array
    {
        return [
            'simulacros' => $this->repository->paginateSimulacros($filters),
            'programas' => $this->repository->programasOptions(),
            'grupos' => $this->repository->gruposOptions(),
            'plantillas' => $this->repository->plantillasOptions(),
        ];
    }

    public function ranking(array $filters): array
    {
        return [
            ...$this->repository->ranking($filters),
            'programas' => $this->repository->programasOptions(),
            'grupos' => $this->repository->gruposOptions(),
        ];
    }

    public function rankingPortal(Postulante $postulante): array
    {
        return $this->repository->rankingPortal($postulante);
    }

    public function ficha(Postulante $postulante, ?User $user = null): array
    {
        return $this->repository->ficha($postulante, $user);
    }

    public function fichas(array $filters, User $user): array
    {
        return [
            'fichas' => $this->repository->paginateFichas($filters, $user),
            'programas' => $this->repository->programasOptionsPara($user, 'ficha-academica.ver'),
            'grupos' => $this->repository->gruposOptionsPara($user, 'ficha-academica.ver'),
        ];
    }

    public function dashboardMetrics(): array
    {
        return $this->repository->dashboardMetrics();
    }

    public function savePrograma(ProgramaAcademicoData $data, ?ProgramaAcademico $programa = null): ProgramaAcademico
    {
        return DB::transaction(fn () => $programa
            ? $this->repository->updatePrograma($programa, $data)
            : $this->repository->createPrograma($data));
    }

    public function saveGrupo(GrupoAcademicoData $data, ?GrupoAcademico $grupo = null): GrupoAcademico
    {
        return DB::transaction(function () use ($data, $grupo): GrupoAcademico {
            if (! $grupo) {
                return $this->repository->createGrupo($data);
            }

            $locked = GrupoAcademico::query()->lockForUpdate()->findOrFail($grupo->getKey());

            return $this->repository->updateGrupo($locked, $data);
        }, 3);
    }

    public function saveInscripcion(InscripcionAcademicaData $data, ?InscripcionAcademica $inscripcion = null): InscripcionAcademica
    {
        return DB::transaction(function () use ($data, $inscripcion): InscripcionAcademica {
            if (! $inscripcion) {
                return $this->repository->createInscripcion($data);
            }

            $locked = InscripcionAcademica::query()->lockForUpdate()->findOrFail($inscripcion->getKey());

            return $this->repository->updateInscripcion($locked, $data);
        }, 3);
    }

    public function saveSimulacro(SimulacroProgramadoData $data, ?SimulacroProgramado $simulacro = null): SimulacroProgramado
    {
        return DB::transaction(fn () => $simulacro
            ? $this->repository->updateSimulacro($simulacro, $data)
            : $this->repository->createSimulacro($data));
    }
}
