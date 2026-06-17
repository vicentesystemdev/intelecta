# Auditoría de Base de Datos y Normalización - INTELECTA

## 1. Alcance

Esta auditoría revisa la estructura lógica actual de INTELECTA desde migraciones, modelos, relaciones Eloquent, repositorios y controladores principales. El análisis se concentra en normalización, trazabilidad académica, relaciones transaccionales, uso de consultas compuestas y recomendaciones de endurecimiento institucional.

El objetivo no es modificar la base de datos, sino documentar si el diseño actual se encuentra orientado a Tercera Forma Normal (3FN), qué datos son transaccionales, qué datos son derivados o consolidados, y qué mecanismos técnicos se recomiendan para auditoría, logs y respaldo incremental.

## 2. Entidades principales

### Seguridad

- `users`: cuentas de acceso al sistema.
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`: estructura de Spatie Permission.
- `password_reset_tokens`, `sessions`: soporte de autenticación Laravel/Breeze.

### Institucional

- `universidades`: universidades objetivo.
- `carreras`: carreras postuladas, asociadas a universidades.
- `colegios`: colegios de procedencia.

### Postulantes

- `postulantes`: datos personales y académicos base del postulante.

### Evaluaciones

- `materias`: materias curriculares.
- `areas_conocimiento`: áreas vinculadas a materias.
- `temas`: temas vinculados a áreas.
- `preguntas`: banco de preguntas vinculado a temas.
- `alternativas`: alternativas por pregunta.
- `plantillas_evaluacion`: instrumentos evaluativos.
- `plantilla_preguntas`: tabla intermedia entre plantillas y preguntas con orden y puntaje.

### Resultados

- `evaluaciones_aplicadas`: evaluación rendida por un postulante.
- `respuestas_evaluacion`: respuestas registradas por pregunta dentro de una evaluación aplicada.
- `rendimientos_postulante`: consolidación académica por postulante, programa y grupo.

### Reportes

No se observa una tabla transaccional propia de reportes. Los reportes se generan desde consultas a postulantes, evaluaciones aplicadas, asistencia, rendimiento, matrícula, cuotas y habilitación. La exportación PDF/Excel usa servicios de aplicación y no almacena copias permanentes del reporte.

### Learning Analytics conceptual

La capa de Learning Analytics se mantiene como visualización conceptual y lectura institucional. No se observan tablas para modelos predictivos entrenados, parámetros estadísticos, clustering operativo ni inferencia real. La base actual sí contiene datos que podrían alimentar una fase posterior: respuestas, puntajes, tiempos, materias, áreas, temas, asistencia y rendimiento.

### Administración académica

- `programas_academicos`: programas o ciclos de nivelación.
- `grupos_academicos`: grupos/paralelos vinculados a programas.
- `inscripciones_academicas`: vínculo entre postulante, programa y grupo.
- `tutores_academicos`: perfil académico del tutor, opcionalmente vinculado a `users`.
- `asignaciones_tutores`: asignación de tutor a programa o grupo.
- `simulacros_programados`: calendario institucional de simulacros.
- `matriculas_academicas`: registro administrativo de la inscripción.
- `cuotas_academicas`: cuotas asociadas a matrícula.
- `habilitaciones_academicas`: estado de acceso académico-administrativo.
- `asistencias_academicas`: asistencia por postulante, programa, grupo y tutor.

## 3. Evaluación de 1FN

La estructura revisada cumple el criterio general de Primera Forma Normal:

- Las tablas propias usan claves primarias identificables (`id_post`, `id_col`, `id_car`, `id_mat`, `id_area`, `id_tem`, `id_preg`, `id_plan`, `id_eval_apl`, entre otras).
- Los atributos principales son atómicos: nombres, apellidos, correo, celular, estado, fechas, puntajes, porcentajes y observaciones se registran como columnas individuales.
- No se observan listas repetidas en una misma columna para entidades críticas.
- Los detalles repetibles se separan en tablas hijas:
  - alternativas de preguntas en `alternativas`.
  - preguntas de una plantilla en `plantilla_preguntas`.
  - respuestas de una evaluación en `respuestas_evaluacion`.
  - cuotas de una matrícula en `cuotas_academicas`.
  - asistencias por sesión en `asistencias_academicas`.
- La jerarquía curricular se registra como entidades independientes: materia, área, tema, pregunta.

## 4. Evaluación de 2FN

La estructura está orientada a Segunda Forma Normal:

- Las tablas con clave primaria simple no presentan dependencias parciales sobre claves compuestas.
- Las relaciones muchos-a-muchos o uno-a-muchos con atributos propios se modelan como tablas separadas:
  - `plantilla_preguntas` guarda relación, orden y puntaje entre plantilla y pregunta.
  - `inscripciones_academicas` separa la relación postulante-programa-grupo.
  - `asignaciones_tutores` separa tutor, programa, grupo, rol y fechas.
  - `cuotas_academicas` separa cuotas de la matrícula.
  - `respuestas_evaluacion` separa cada respuesta de la evaluación aplicada.
- La información de programa, grupo, tutor, matrícula, cuota, asistencia y habilitación no se repite dentro de `postulantes`; se vincula por claves foráneas.

## 5. Evaluación de 3FN

El núcleo transaccional se encuentra orientado a Tercera Forma Normal:

- Los catálogos principales están separados:
  - `universidades` y `carreras`.
  - `colegios`.
  - `materias`, `areas_conocimiento` y `temas`.
- `postulantes` no almacena nombre de colegio, carrera o universidad; almacena claves hacia `colegios` y `carreras`, y la universidad se resuelve mediante la carrera.
- `tutores_academicos` se separa de `users`: `users` administra acceso, roles y credenciales; `tutores_academicos` administra perfil académico.
- `inscripciones_academicas`, `matriculas_academicas`, `cuotas_academicas` y `habilitaciones_academicas` representan hechos administrativos diferentes y no se mezclan en una sola tabla.
- `evaluaciones_aplicadas` representa la cabecera de una evaluación rendida; `respuestas_evaluacion` representa el detalle trazable pregunta por pregunta.
- `preguntas` no almacena el nombre del área o materia; lo obtiene por `tema -> area -> materia`.
- Las plantillas no duplican preguntas; usan `plantilla_preguntas`.

Se observan algunos campos de snapshot o consolidación aceptables para operación institucional, por ejemplo `id_prog` e `id_grupo` en matrícula o rendimiento. Estos campos facilitan filtros e históricos operativos y no reemplazan los catálogos ni relaciones base.

## 6. Tablas consolidadas o derivadas

Existen datos calculados o consolidados que no deben interpretarse como ruptura automática de 3FN si mantienen trazabilidad al dato base:

- `puntaje_total_eval_apl`, `puntaje_maximo_eval_apl` y `porcentaje_eval_apl` en `evaluaciones_aplicadas` son resultados calculados desde `respuestas_evaluacion`.
- `puntaje_obtenido_resp`, `puntaje_maximo_resp` y `es_correcta_resp` en `respuestas_evaluacion` permiten conservar el resultado por ítem al momento de finalizar la evaluación.
- `rendimientos_postulante` consolida promedios por materia, promedio general, asistencia y nivel de seguimiento. Es una tabla de lectura académica derivada, útil para ranking, ficha y reportes.
- La habilitación académica consolida estado administrativo y acceso a evaluaciones, simulacros y reportes. Su origen puede trazarse a matrícula, cuotas e intervención administrativa.
- Los reportes PDF/Excel no almacenan copias en base de datos; se generan desde servicios y consultas actuales.

Recomendación: mantener una política clara de recalculo y trazabilidad. Todo dato derivado debe poder explicar su origen: evaluación aplicada, respuestas, asistencia, matrícula, cuota o habilitación.

## 7. Relaciones y claves foráneas

Relaciones importantes observadas:

- `postulantes.id_col -> colegios.id_col`.
- `postulantes.id_car -> carreras.id_car`.
- `carreras.id_uni -> universidades.id_uni`.
- `grupos_academicos.id_prog -> programas_academicos.id_prog`.
- `inscripciones_academicas.id_post -> postulantes.id_post`.
- `inscripciones_academicas.id_prog -> programas_academicos.id_prog`.
- `inscripciones_academicas.id_grupo -> grupos_academicos.id_grupo`.
- `tutores_academicos.user_id -> users.id`.
- `asignaciones_tutores.id_tutor -> tutores_academicos.id_tutor`.
- `asignaciones_tutores.id_prog -> programas_academicos.id_prog`.
- `asignaciones_tutores.id_grupo -> grupos_academicos.id_grupo`.
- `areas_conocimiento.id_mat -> materias.id_mat`.
- `temas.id_area -> areas_conocimiento.id_area`.
- `preguntas.id_tem -> temas.id_tem`.
- `alternativas.id_preg -> preguntas.id_preg`.
- `plantilla_preguntas.id_plan -> plantillas_evaluacion.id_plan`.
- `plantilla_preguntas.id_preg -> preguntas.id_preg`.
- `evaluaciones_aplicadas.id_post -> postulantes.id_post`.
- `evaluaciones_aplicadas.id_plantilla -> plantillas_evaluacion.id_plan`.
- `evaluaciones_aplicadas.id_sim -> simulacros_programados.id_sim`.
- `respuestas_evaluacion.id_eval_apl -> evaluaciones_aplicadas.id_eval_apl`.
- `respuestas_evaluacion.id_preg -> preguntas.id_preg`.
- `respuestas_evaluacion.id_alt -> alternativas.id_alt`.
- `matriculas_academicas.id_insc -> inscripciones_academicas.id_insc`.
- `matriculas_academicas.id_post -> postulantes.id_post`.
- `matriculas_academicas.id_prog -> programas_academicos.id_prog`.
- `matriculas_academicas.id_grupo -> grupos_academicos.id_grupo`.
- `cuotas_academicas.id_mat -> matriculas_academicas.id_mat`.
- `habilitaciones_academicas.id_post -> postulantes.id_post`.
- `habilitaciones_academicas.id_insc -> inscripciones_academicas.id_insc`.
- `habilitaciones_academicas.id_mat -> matriculas_academicas.id_mat`.
- `asistencias_academicas.id_post -> postulantes.id_post`.
- `asistencias_academicas.id_prog -> programas_academicos.id_prog`.
- `asistencias_academicas.id_grupo -> grupos_academicos.id_grupo`.
- `asistencias_academicas.id_tutor -> tutores_academicos.id_tutor`.

## 8. Uso de joins y consultas compuestas

El sistema usa principalmente relaciones Eloquent, eager loading y repositorios. También existen joins directos en reportes y dashboard cuando se requiere agrupar información para métricas.

Patrones observados:

- `with()` para evitar consultas N+1 en rankings, fichas, inscripciones, grupos y reportes.
- `withCount()` para contar grupos, inscripciones, simulacros, preguntas y evaluaciones finalizadas.
- `whereHas()` para filtrar inscripciones por datos del postulante.
- `join()` directo en reportes para postulantes por universidad, carrera, colegio y preguntas por área.
- Servicios especializados para consolidar datos:
  - `AcademicoRepository` integra programas, grupos, inscripciones, simulacros, ranking y opciones de catálogos.
  - `ResultadoAcademicoRepository` recalcula rendimiento desde evaluaciones aplicadas y respuestas.
  - `ReporteExportacionService` integra rendimiento, evaluaciones aplicadas, asistencia, matrícula, cuotas y habilitación para PDF/Excel.
  - `CoberturaCurricularService` calcula cobertura por materia, dificultad y plantillas.
- La ficha académica usa `AcademicoService` para presentar datos personales, inscripción, grupo, tutor, rendimiento, asistencia, matrícula, habilitación y evaluaciones aplicadas.

Ejemplos de integración:

- Ficha académica: postulante + colegio + carrera + universidad + inscripción + programa + grupo + tutor + asistencia + matrícula + habilitación + evaluaciones aplicadas.
- Ranking académico: rendimiento + postulante + colegio + carrera + universidad + programa + grupo.
- Reportes: rendimiento, evaluaciones aplicadas, asistencia y habilitación desde modelos relacionados.
- Resultados académicos: evaluación aplicada + respuestas + pregunta + alternativa + plantilla.

## 9. Triggers

En la revisión estática de migraciones y código no se encontraron triggers SQL explícitos (`CREATE TRIGGER` o definiciones equivalentes).

Conclusión:

- No se encontraron triggers SQL explícitos.
- La trazabilidad se gestiona desde la capa de aplicación mediante controladores, FormRequests, DTOs, Actions, Services, Repositories, modelos Eloquent y relaciones.
- Los cálculos principales, como rendimiento académico, se ejecutan desde servicios/repositorios de aplicación.
- A futuro podrían incorporarse triggers para auditoría de cambios críticos, pero no son necesarios para la operación funcional actual si la capa de aplicación conserva disciplina transaccional.

## 10. Auditoría y logs

Como siguiente fase de endurecimiento institucional se recomienda crear una tabla `bitacora_sistema` o equivalente.

Campos sugeridos:

- `id_bitacora`.
- `user_id`.
- `accion`.
- `entidad`.
- `entidad_id`.
- `valores_anteriores` en JSON.
- `valores_nuevos` en JSON.
- `ip`.
- `user_agent`.
- `fecha_hora`.

Acciones críticas recomendadas:

- Creación, edición, inactivación o eliminación lógica de postulantes.
- Cambios de matrícula, cuotas y estados administrativos.
- Cambios de habilitación académica.
- Inicio, finalización o anulación de evaluaciones aplicadas.
- Registro o modificación de respuestas de evaluación.
- Modificación de preguntas, alternativas y plantillas.
- Exportación de reportes PDF/Excel.
- Cambios de roles, permisos y usuarios.
- Cambios en programas, grupos, tutores e inscripciones.

Diseño recomendado:

- Registrar auditoría desde eventos de aplicación o observers Eloquent.
- Evitar guardar contraseñas, tokens o secretos.
- Guardar JSON con diferencias, no copias innecesarias de toda la base.
- Proteger lectura de bitácora con permisos administrativos específicos.
- Definir política de retención.

## 11. Backup incremental

Propuesta técnica para PostgreSQL y archivos de aplicación:

- Backup completo semanal de la base de datos.
- Backup incremental o archivado WAL diario en PostgreSQL.
- Respaldo de `storage/app` si el sistema almacena archivos generados o cargados.
- Respaldo de documentación operativa y `.env.example`; no respaldar `.env` real en repositorios ni ubicaciones inseguras.
- Rotación de copias con ventanas mínimas: diaria, semanal y mensual.
- Prueba de restauración programada para validar que el backup realmente sirve.
- Almacenamiento externo seguro con cifrado y control de acceso.
- Registro de cada ejecución de backup y alerta si falla.

No se recomienda implementar backups dentro del flujo web normal. Deben gestionarse por tareas programadas del servidor, herramientas PostgreSQL (`pg_dump`, WAL archiving, replicas) o infraestructura administrada.

## 12. Conclusión

El núcleo transaccional de INTELECTA está orientado a Tercera Forma Normal. La separación entre seguridad, postulantes, institución, currícula, evaluaciones, resultados, administración académica y reportes permite reducir duplicidad y conservar relaciones claras.

Existen datos derivados controlados, como puntajes, porcentajes, rendimiento, ranking e habilitación. Estos son aceptables en una plataforma académica siempre que se mantenga trazabilidad hacia evaluaciones aplicadas, respuestas, asistencias, matrículas y cuotas.

No se detectaron triggers SQL explícitos. La integridad funcional y trazabilidad actual se administran desde la capa Laravel mediante servicios, repositorios y relaciones Eloquent.

El sistema ya utiliza relaciones, eager loading, `whereHas()` y joins directos para consultas institucionales como ficha académica, ranking, reportes, resultados, asistencia y habilitación.

La siguiente fase de endurecimiento institucional debería incorporar bitácora de auditoría, logs de acciones críticas, política de backup incremental y pruebas periódicas de restauración.
