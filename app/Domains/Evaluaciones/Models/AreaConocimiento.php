<?php

namespace App\Domains\Evaluaciones\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre_area', 'descripcion_area', 'estado_area'])]
class AreaConocimiento extends Model
{
    protected $table = 'areas_conocimiento';

    protected $primaryKey = 'id_area';

    public function temas(): HasMany
    {
        return $this->hasMany(Tema::class, 'id_area', 'id_area');
    }
}
