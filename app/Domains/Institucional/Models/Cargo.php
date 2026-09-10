<?php

namespace App\Domains\Institucional\Models;

use App\Domains\Institucional\Enums\EstadoCargo;
use Database\Factories\CargoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre_cargo', 'descripcion'])]
#[UseFactory(CargoFactory::class)]
class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargos';

    protected $primaryKey = 'id_cargo';

    protected function casts(): array
    {
        return ['estado' => EstadoCargo::class];
    }

    public function personal(): HasMany
    {
        return $this->hasMany(PersonalInstitucional::class, 'cargo_id', 'id_cargo');
    }
}
