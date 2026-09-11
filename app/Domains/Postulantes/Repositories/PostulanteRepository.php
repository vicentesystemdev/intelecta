<?php

namespace App\Domains\Postulantes\Repositories;

use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Institucional\Models\Universidad;
use App\Domains\Postulantes\DTOs\PostulanteData;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PostulanteRepository
{
    public function __construct(private readonly AmbitoDocenteService $ambito) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, User $user, int $perPage = 10): LengthAwarePaginator
    {
        $teacher = $this->ambito->esDocenteRestringido($user, 'postulantes.ver');
        $query = $this->ambito->postulantes(Postulante::query(), $user);

        if ($teacher) {
            $query->select([
                'id_post',
                'nombres_post',
                'apellidos_post',
                'estado_post',
                'gestion_post',
                'turno_post',
                'id_col',
                'id_car',
            ]);
        }

        return $query
            ->with([
                'colegio:id_col,nombre_col',
                'carrera:id_car,id_uni,nombre_car,nivel_exigencia_matematica_car',
                'carrera.universidad:id_uni,nombre_uni,sigla_uni,tipo_uni,nivel_exigencia_matematica_uni',
            ])
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) use ($teacher) {
                $pattern = '%'.mb_strtolower($search).'%';

                $query->where(function (Builder $query) use ($pattern, $teacher) {
                    $query
                        ->whereRaw('LOWER(nombres_post) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(apellidos_post) LIKE ?', [$pattern]);
                    if (! $teacher) {
                        $query->orWhereRaw("LOWER(COALESCE(ci_post, '')) LIKE ?", [$pattern])
                            ->orWhereRaw("LOWER(COALESCE(email_post, '')) LIKE ?", [$pattern]);
                    }
                });
            })
            ->when(
                $filters['id_col'] ?? null,
                fn (Builder $query, int|string $idCol) => $query->where('id_col', $idCol)
            )
            ->when(
                $filters['id_uni'] ?? null,
                fn (Builder $query, int|string $idUni) => $query->whereHas(
                    'carrera',
                    fn (Builder $query) => $query->where('id_uni', $idUni)
                )
            )
            ->when(
                $filters['id_car'] ?? null,
                fn (Builder $query, int|string $idCar) => $query->where('id_car', $idCar)
            )
            ->when(
                $filters['estado_post'] ?? null,
                fn (Builder $query, string $estado) => $query->where('estado_post', $estado)
            )
            ->orderBy('apellidos_post')
            ->orderBy('nombres_post')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(PostulanteData $data): Postulante
    {
        return Postulante::create($data->toArray());
    }

    public function update(Postulante $postulante, PostulanteData $data): Postulante
    {
        $postulante->update($data->toArray());

        return $postulante->refresh();
    }

    public function changeStatus(Postulante $postulante, string $estado): Postulante
    {
        $postulante->update(['estado_post' => $estado]);

        return $postulante->refresh();
    }

    public function find(int $id, User $user): Postulante
    {
        $query = $this->ambito->postulantes(Postulante::query(), $user)
            ->with(['colegio', 'carrera.universidad']);

        if ($this->ambito->esDocenteRestringido($user, 'postulantes.ver')) {
            $query->select([
                'id_post',
                'nombres_post',
                'apellidos_post',
                'estado_post',
                'gestion_post',
                'turno_post',
                'id_col',
                'id_car',
            ]);
        }

        return $query->findOrFail($id);
    }

    /**
     * @return array{
     *     colegios: Collection<int, Colegio>,
     *     universidades: Collection<int, Universidad>,
     *     carreras: Collection<int, Carrera>
     * }
     */
    public function formOptions(?User $user = null): array
    {
        $teacher = $user && $this->ambito->esDocenteRestringido($user, 'postulantes.ver');

        return [
            'colegios' => Colegio::query()
                ->when($teacher, fn (Builder $query) => $query->whereIn(
                    'id_col',
                    $this->ambito->postulantes(Postulante::query(), $user)
                        ->whereNotNull('id_col')
                        ->select('id_col'),
                ))
                ->where('estado_col', 'activo')
                ->orderBy('nombre_col')
                ->get(['id_col', 'nombre_col']),
            'universidades' => Universidad::query()
                ->when($teacher, fn (Builder $query) => $query->whereIn(
                    'id_uni',
                    Carrera::query()
                        ->whereIn(
                            'id_car',
                            $this->ambito->postulantes(Postulante::query(), $user)
                                ->whereNotNull('id_car')
                                ->select('id_car'),
                        )
                        ->select('id_uni'),
                ))
                ->where('estado_uni', 'activo')
                ->orderBy('nombre_uni')
                ->get([
                    'id_uni',
                    'nombre_uni',
                    'sigla_uni',
                    'tipo_uni',
                    'nivel_exigencia_matematica_uni',
                ]),
            'carreras' => Carrera::query()
                ->with('universidad:id_uni,nombre_uni,sigla_uni')
                ->where('estado_car', 'activo')
                ->whereNotNull('id_uni')
                ->when($teacher, fn (Builder $query) => $query->whereIn(
                    'id_car',
                    $this->ambito->postulantes(Postulante::query(), $user)
                        ->whereNotNull('id_car')
                        ->select('id_car'),
                ))
                ->orderBy('nombre_car')
                ->get([
                    'id_car',
                    'id_uni',
                    'nombre_car',
                    'area_car',
                    'nivel_exigencia_matematica_car',
                ]),
        ];
    }
}
