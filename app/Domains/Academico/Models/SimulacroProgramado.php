<?php

namespace App\Domains\Academico\Models;

use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id_prog',
    'id_grupo',
    'id_plantilla',
    'titulo_sim',
    'fecha_sim',
    'hora_inicio_sim',
    'hora_fin_sim',
    'modalidad_sim',
    'estado_sim',
    'observacion_sim',
])]
class SimulacroProgramado extends Model
{
    use SoftDeletes;

    protected $table = 'simulacros_programados';

    protected $primaryKey = 'id_sim';

    protected function casts(): array
    {
        return ['fecha_sim' => 'date'];
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaAcademico::class, 'id_prog', 'id_prog');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoAcademico::class, 'id_grupo', 'id_grupo');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaEvaluacion::class, 'id_plantilla', 'id_plan');
    }
}
