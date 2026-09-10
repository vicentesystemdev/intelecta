<?php

namespace App\Domains\Institucional\Support;

final class PermisosOrganizacion
{
    public const ALL = [
        'cargos.ver', 'cargos.crear', 'cargos.editar', 'cargos.cambiar_estado',
        'personal.ver', 'personal.crear', 'personal.editar', 'personal.cambiar_estado',
    ];
}
