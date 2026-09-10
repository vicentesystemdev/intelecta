# Matriz objetivo RBAC — Bloque 5

Construida antes de modificar asignaciones, el 10/09/2026, sobre HEAD `20cafdd`.

Preflight PostgreSQL 17.2: 4 roles, 80 permisos, 179 role_has_permissions, 9 model_has_roles, 0 model_has_permissions. SA=80; Administrador=80; Docente=19; Estudiante=0. Usuarios: SA 1; Administrador 1; Docente 4; Estudiante 3. Admin id 2 sin Personal (acreditación pendiente); docentes 3–6 con Personal pendiente y Tutor activo; estudiantes 7–9 sin vínculo, no se modificarán.

Evidencia de consumidores: `routes/web.php` usa permission/organization por módulo; Requests y controladores repiten las mismas capacidades. No hay rutas con middleware `role:`. Contextos literales deliberados: Gate SA, operaciones de identidad SA activo, organización SA/Administrador, portal Estudiante. El middleware administrativo por exclusión y el dashboard global se sustituyen por contexto positivo.

| Permiso | SA | Administrador | Docente | Estudiante | Motivo |
|---|---|---|---|---|---|
| areas.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| areas.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| areas.ver | Sí | Sí | Sí | — | Contenido curricular/pedagógico sin expedientes ni resultados de personas. |
| asignaciones-tutores.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| asignaciones-tutores.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| asignaciones-tutores.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| asistencia.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| asistencia.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| asistencia.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| bitacora.exportar | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |
| bitacora.ver | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |
| cargos.cambiar_estado | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| cargos.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| cargos.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| cargos.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| configuracion.ver | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |
| dashboard.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| docentes.crear | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| docentes.editar | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| docentes.eliminar | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| docentes.ver | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| evaluaciones.cerrar | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| evaluaciones.crear | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| evaluaciones.editar | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| evaluaciones.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| ficha-academica.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| grupos.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| grupos.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| grupos.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| habilitacion-academica.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| habilitacion-academica.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| indicadores.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| inscripciones.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| inscripciones.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| inscripciones.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| learning_analytics.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| materias.ver | Sí | Sí | Sí | — | Contenido curricular/pedagógico sin expedientes ni resultados de personas. |
| matriculas-cuotas.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| matriculas-cuotas.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| matriculas-cuotas.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| personal.cambiar_estado | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| personal.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| personal.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| personal.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| plantillas.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| plantillas.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| plantillas.eliminar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| plantillas.ver | Sí | Sí | Sí | — | Contenido curricular/pedagógico sin expedientes ni resultados de personas. |
| postulantes.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| postulantes.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| postulantes.eliminar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| postulantes.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| preguntas.crear | Sí | Sí | Sí | — | Contenido curricular/pedagógico sin expedientes ni resultados de personas. |
| preguntas.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| preguntas.eliminar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| preguntas.ver | Sí | Sí | Sí | — | Contenido curricular/pedagógico sin expedientes ni resultados de personas. |
| programas.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| programas.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| programas.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| ranking.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| reportes.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| resultados.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| roles-permisos.editar | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |
| roles-permisos.ver | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |
| roles.crear | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| roles.editar | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| roles.ver | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| simulacros.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| simulacros.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| simulacros.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| temas.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| temas.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| temas.ver | Sí | Sí | Sí | — | Contenido curricular/pedagógico sin expedientes ni resultados de personas. |
| tutores.crear | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| tutores.editar | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| tutores.ver | Sí | Sí | — | — | Operación o consulta institucional global; solo administración académica y SA. |
| usuarios.asignar_roles | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |
| usuarios.crear | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |
| usuarios.editar | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |
| usuarios.eliminar | — | — | — | — | RETIRABLE AHORA: sin consumidor backend ni dependencia funcional; conservar fila legacy sin asignaciones. |
| usuarios.ver | Sí | — | — | — | Seguridad exclusiva de SA activo; asignar_roles es el nuevo endpoint independiente. |

Resultado: SA 70, Administrador 61, Docente 6, Estudiante 0; 137 asignaciones. El bypass Gate SA se conserva, incluso para nombres legacy, pero las 11 capacidades sin consumidor salen de la matriz activa. No se crean endpoints para justificarlas. RESERVADO CON JUSTIFICACIÓN: ninguno. LEGACY: esas 11 filas del catálogo quedan para trazabilidad, no seleccionables.

Docente pierde temporalmente 13 capacidades: dashboard.ver, programas.ver, grupos.ver, postulantes.ver, ficha-academica.ver, ranking.ver, asistencia.ver, simulacros.ver, resultados.ver, reportes.ver, indicadores.ver, learning_analytics.ver, evaluaciones.ver. Los módulos consultan datos institucionales globales o son centros de navegación hacia ellos. No se restituyen hasta contar con ámbitos de Bloque 6. Plantillas solo consulta preguntas, alternativas, temas y materias (PlantillaEvaluacionRepository), no aplicaciones/resultados de alumnos.

`postulantes.eliminar`, `preguntas.eliminar` y `plantillas.eliminar` representan cambio de estado, no borrado: permanecen sin rename.

Reglas: permisos aditivos; Administrador+Docente permitido. Estudiante y SA exclusivos para nuevas asignaciones. Nuevos Estudiante requieren Postulante no archivado; nuevos Docente requieren Personal+Tutor no archivado, sin exigir Personal activo; nuevos Administrador requieren Personal. Roles ya existentes se conservan sin reacreditar perfiles legacy. Crear cuenta no acepta roles; edición no acepta role/roles. Vinculación explícita de Postulante admite cuenta sin roles para evitar un ciclo entre acreditación y asignación; nunca autovincula.

Prioridad de entrada: SA/Administrador → dashboard; Docente → preguntas si disponible, otro catálogo pedagógico permitido o inicio público; Estudiante → portal; sin roles → inicio público. No existe rol principal. La edición de matriz tiene techos por rol iguales a esta matriz; reducir capacidades es posible, excederlas no.
