<?php

namespace App\Domains\Seguridad\Support;

/** Block 5 reviewed ceilings, shared by deployment, seeders and the security UI. */
final class MatrizRbac
{
    public const ROLES = ['Super Administrador', 'Administrador', 'Docente', 'Estudiante'];

    public const CATALOG = [
        'areas.crear',
        'areas.editar',
        'areas.ver',
        'asignaciones-tutores.crear',
        'asignaciones-tutores.editar',
        'asignaciones-tutores.ver',
        'asistencia.crear',
        'asistencia.editar',
        'asistencia.ver',
        'bitacora.exportar',
        'bitacora.ver',
        'cargos.cambiar_estado',
        'cargos.crear',
        'cargos.editar',
        'cargos.ver',
        'configuracion.ver',
        'dashboard.ver',
        'docentes.crear',
        'docentes.editar',
        'docentes.eliminar',
        'docentes.ver',
        'evaluaciones.cerrar',
        'evaluaciones.crear',
        'evaluaciones.editar',
        'evaluaciones.ver',
        'ficha-academica.ver',
        'grupos.crear',
        'grupos.editar',
        'grupos.ver',
        'habilitacion-academica.editar',
        'habilitacion-academica.ver',
        'indicadores.ver',
        'inscripciones.crear',
        'inscripciones.editar',
        'inscripciones.ver',
        'learning_analytics.ver',
        'materias.ver',
        'matriculas-cuotas.crear',
        'matriculas-cuotas.editar',
        'matriculas-cuotas.ver',
        'personal.cambiar_estado',
        'personal.crear',
        'personal.editar',
        'personal.ver',
        'plantillas.crear',
        'plantillas.editar',
        'plantillas.eliminar',
        'plantillas.ver',
        'postulantes.crear',
        'postulantes.editar',
        'postulantes.eliminar',
        'postulantes.ver',
        'preguntas.crear',
        'preguntas.editar',
        'preguntas.eliminar',
        'preguntas.ver',
        'programas.crear',
        'programas.editar',
        'programas.ver',
        'ranking.ver',
        'reportes.ver',
        'resultados.ver',
        'roles-permisos.editar',
        'roles-permisos.ver',
        'roles.crear',
        'roles.editar',
        'roles.ver',
        'simulacros.crear',
        'simulacros.editar',
        'simulacros.ver',
        'temas.crear',
        'temas.editar',
        'temas.ver',
        'tutores.crear',
        'tutores.editar',
        'tutores.ver',
        'usuarios.asignar_roles',
        'usuarios.crear',
        'usuarios.editar',
        'usuarios.eliminar',
        'usuarios.ver',
    ];

    public const RETIRED = [
        'usuarios.eliminar',
        'roles.ver',
        'roles.crear',
        'roles.editar',
        'docentes.ver',
        'docentes.crear',
        'docentes.editar',
        'docentes.eliminar',
        'evaluaciones.crear',
        'evaluaciones.editar',
        'evaluaciones.cerrar',
    ];

    public const SECURITY = [
        'usuarios.ver',
        'usuarios.crear',
        'usuarios.editar',
        'usuarios.asignar_roles',
        'roles-permisos.ver',
        'roles-permisos.editar',
        'bitacora.ver',
        'bitacora.exportar',
        'configuracion.ver',
    ];

    public const TEACHER = [
        'materias.ver',
        'areas.ver',
        'temas.ver',
        'preguntas.ver',
        'preguntas.crear',
        'plantillas.ver',
    ];

    public static function active(): array
    {
        return array_values(array_diff(self::CATALOG, self::RETIRED));
    }

    public static function forRole(string $role): array
    {
        return match ($role) {
            'Super Administrador' => self::active(),
            'Administrador' => array_values(array_diff(self::active(), self::SECURITY)),
            'Docente' => self::TEACHER,
            default => [],
        };
    }
}
