<?php

namespace App\Domains\Institucional\Models;

use App\Domains\Institucional\Enums\EstadoPersonal;
use App\Models\User;
use Database\Factories\PersonalInstitucionalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cargo_id', 'nombres', 'apellidos', 'ci', 'celular', 'correo_contacto'])]
#[UseFactory(PersonalInstitucionalFactory::class)]
class PersonalInstitucional extends Model
{
    use HasFactory;

    protected $table = 'personal_institucional';

    protected $primaryKey = 'id_personal';

    protected function casts(): array
    {
        return ['estado' => EstadoPersonal::class, 'user_id' => 'integer', 'cargo_id' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id', 'id_cargo');
    }
}
