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

    public function grupos(array $filters): array
    {
        return [
            'grupos' => $this->repository->paginateGrupos($filters),
            'programas' => $this->repository->programasOptions(),
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

    public function ficha(Postulante $postulante): array
    {
        return $this->repository->ficha($postulante);
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
        return DB::transaction(fn () => $grupo
            ? $this->repository->updateGrupo($grupo, $data)
            : $this->repository->createGrupo($data));
    }

    public function saveInscripcion(InscripcionAcademicaData $data, ?InscripcionAcademica $inscripcion = null): InscripcionAcademica
    {
        return DB::transaction(fn () => $inscripcion
            ? $this->repository->updateInscripcion($inscripcion, $data)
            : $this->repository->createInscripcion($data));
    }

    public function saveSimulacro(SimulacroProgramadoData $data, ?SimulacroProgramado $simulacro = null): SimulacroProgramado
    {
        return DB::transaction(fn () => $simulacro
            ? $this->repository->updateSimulacro($simulacro, $data)
            : $this->repository->createSimulacro($data));
    }
}
