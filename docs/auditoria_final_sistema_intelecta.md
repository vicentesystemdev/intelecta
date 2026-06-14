# Auditoría Final del Sistema INTELECTA

**Sistema auditado:** INTELECTA  
**Caso institucional:** Academia Universitaria Avalancha  
**Fecha de revisión:** 14 de junio de 2026  
**Alcance técnico:** Laravel 13, React/Inertia, Tailwind, PostgreSQL, Redis y Spatie Permission  
**Estado revisado:** copia de trabajo local actual, incluyendo cambios aún no confirmados en Git.

## 1. Resumen Ejecutivo

INTELECTA presenta una base institucional amplia y coherente para administrar postulantes, programas, grupos, tutores, inscripciones, matrículas, cuotas, habilitación, asistencia, simulacros, estructura curricular, preguntas, plantillas, reportes y seguimiento académico. La identidad visual Elite Prep Institute está aplicada de forma consistente en los módulos institucionales recientes y la navegación principal carga sin enlaces técnicamente rotos.

La auditoría verificó 100 rutas sin nombres ni combinaciones método/URI duplicadas, 183 archivos PHP sin errores de sintaxis y ausencia de errores de consola durante el recorrido visual. Las pantallas principales tampoco presentaron overflow horizontal global en anchos de 1280 px y 390 px.

El sistema todavía requiere una fase de estabilización antes de considerarse cerrado. Los riesgos más importantes son:

1. Las rutas institucionales y varias rutas administrativas no aplican permisos granulares; cualquier usuario autenticado puede alcanzar operaciones institucionales.
2. El CRUD de Áreas de Conocimiento no captura `id_mat`, aunque la jerarquía curricular exige Materia → Área → Tema.
3. Resultados Académicos continúa siendo una vista referencial con datos fijos y no alimenta Ranking, Ficha ni Reportes desde evaluaciones aplicadas.
4. La sincronización administrativa puede reemplazar una habilitación ajustada manualmente y depende del estado escrito de la cuota, no de su vencimiento efectivo.

**Resultado general:** sistema funcional para exposición institucional y operación controlada, pero no listo para cierre definitivo ni despliegue con usuarios de distintos privilegios hasta resolver los hallazgos críticos.

## 2. Alcance de la Auditoría

Se revisaron:

- Dashboard institucional.
- Sidebar, grupos desplegables, enlaces y estado activo.
- Programas Académicos.
- Grupos y Paralelos.
- Tutores Académicos y detalle modal.
- Asignación de Tutores.
- Inscripciones Académicas.
- Matrículas y Cuotas.
- Habilitación Académica.
- Control de Asistencia.
- Calendario de Simulacros.
- Postulantes y Ficha Académica.
- Ranking Académico.
- Carreras, Colegios y Universidades.
- Materias, Áreas de Conocimiento y Temas Académicos.
- Banco de Preguntas y Plantillas de Evaluación.
- Resultados, Reportes e Indicadores de Desempeño.
- Learning Analytics conceptual.
- Usuarios, Roles y Permisos y Configuración.
- FormRequests, DTOs, Actions, Services, Repositories y Models relevantes.
- Migraciones propias de INTELECTA y relaciones principales.
- Terminología visible, datos de referencia y vistas heredadas.
- Adaptación visual básica en escritorio y móvil.

Comprobaciones no destructivas realizadas:

- `php artisan route:list --except-vendor`
- `php artisan route:list --json`
- `php -l` sobre 183 archivos PHP
- `git diff --check`
- búsquedas estáticas con `rg`
- recorrido visual autenticado de las rutas principales
- comprobación de overflow a 1280 px y 390 px
- revisión de errores de consola del navegador

No se ejecutaron migraciones, seeders, build, pruebas automatizadas ni diagnóstico del sistema.

## 3. Hallazgos Críticos

| Código | Módulo | Hallazgo | Impacto | Prioridad | Recomendación |
|---|---|---|---|---|---|
| C-01 | Seguridad y rutas institucionales | El grupo `/admin/institucional` usa `auth` y `verified`, pero no permisos de Spatie. Los FormRequests institucionales autorizan con `$this->user() !== null`. Además, el registro público crea usuarios sin rol y `DashboardController` solo redirige al rol `Estudiante`, por lo que un usuario sin rol puede entrar al dashboard y alcanzar operaciones institucionales. | Acceso no autorizado a información y mutaciones de programas, grupos, tutores, inscripciones, matrículas, habilitación, asistencia y simulacros. | Crítica | Definir permisos institucionales por recurso y acción, aplicarlos en rutas y `authorize()`, ocultar navegación sin permiso y establecer un destino seguro para usuarios sin rol. Revisar si el registro público debe permanecer habilitado. |
| C-02 | Áreas de Conocimiento | La tabla y el modelo admiten `id_mat`, pero `StoreAreaConocimientoRequest`, `UpdateAreaConocimientoRequest`, `AreaConocimientoData` y el formulario no capturan ni persisten la materia. | Las nuevas áreas quedan sin materia y rompen la navegación Materia → Área → Tema, la cobertura curricular y filtros del banco de preguntas. | Crítica | Incorporar selector obligatorio de materia, validar `exists:materias,id_mat`, persistirlo en DTO/repositorio y mostrar materia en listado y edición. Definir estrategia para áreas históricas sin clasificación. |
| C-03 | Resultados Académicos | `/admin/evaluaciones/resultados` renderiza `Modulos/ResultadosSeguimiento.jsx` con KPIs y postulantes hardcodeados. No existe flujo de aplicación de evaluación que persista respuestas/resultados y alimente `rendimientos_postulante`. | Resultados, Ranking, Ficha e Indicadores pueden representar universos distintos y no trazables; el flujo documental Evaluaciones → Resultados → Ranking no está cerrado. | Crítica | Implementar primero el modelo transaccional mínimo de evaluación aplicada, respuestas y resultados. Derivar rendimiento y ranking desde esa fuente o declarar Resultados como módulo referencial hasta completar la integración. |
| C-04 | Cuotas y Habilitación Académica | Cada alta/edición de cuota ejecuta `syncForMatricula()`, que reemplaza estado, motivo y banderas de acceso de la habilitación. También considera vencida solo una cuota con estado explícito `vencida`, aunque una cuota `pendiente` tenga fecha vencida. | Una decisión manual puede perderse; un postulante vencido puede continuar observado/habilitado; existe riesgo de acceso académico incoherente. | Crítica | Separar estado calculado y override manual, registrar origen/fecha del cambio y evitar sobrescribir una restricción manual. Calcular vencimiento por fecha o ejecutar una conciliación idempotente. |

## 4. Hallazgos Medios

| Código | Módulo | Hallazgo | Impacto | Prioridad | Recomendación |
|---|---|---|---|---|---|
| M-01 | Sidebar / Ficha Académica | “Ficha Académica” aparece en el sidebar, pero enlaza a `/postulantes`; solo existe la ruta de detalle `/admin/institucional/ficha-academica/{postulante}`. | Navegación semánticamente incorrecta y expectativa incumplida. | Alta | Crear `/admin/institucional/ficha-academica` con búsqueda/listado de postulantes y acción “Ver ficha”. Es la mejor opción porque el módulo está declarado en el sidebar y en el Capítulo 5. |
| M-02 | Fechas institucionales | Programas y asignaciones validan relación inicio/fin, pero no restringen una fecha inicial pasada al crear planificación nueva. Inscripción y matrícula permiten fechas futuras. La fecha de pago no impide futuro ni se relaciona con el estado `pagada`. | Registros cronológicamente inconsistentes. | Alta | Aplicar reglas condicionales para creación frente a edición histórica; añadir `min`/`max` equivalentes en frontend. |
| M-03 | Inscripciones y grupos | La inscripción valida programa, grupo y duplicidad, pero no controla capacidad disponible ni exige que postulante, programa y grupo estén activos. | Sobreasignación de cupos e inscripción sobre entidades inactivas. | Alta | Validar cupo dentro de una transacción y estados activos; conservar edición de registros históricos con reglas diferenciadas. |
| M-04 | Asignación de Tutores | No se impiden asignaciones activas solapadas para el mismo grupo/programa. Las consultas consideran `estado_asig`, pero no verifican si `fecha_fin_asig` ya venció. | Puede haber más de un “tutor responsable” activo y seleccionarse silenciosamente el último registro. | Alta | Definir cardinalidad y solapamiento permitido; validar rangos y filtrar vigencia por fechas, no solo por estado. |
| M-05 | Catálogos y Configuración | Carreras, Colegios y Materias son consultas de solo lectura; Configuración también es informativa. | Los módulos existen visualmente, pero no constituyen gestión completa. | Media | Documentarlos como catálogos de consulta o incorporar CRUD controlado en una fase posterior. No presentarlos como administración completa mientras sean solo lectura. |
| M-06 | Normalización operativa | `programas_academicos` guarda universidad objetivo y carrera/área como texto pese a existir catálogos normalizados; `grupos_academicos` conserva `tutor_responsable_grupo` además de asignaciones; matrícula replica IDs derivados de inscripción. | Posible divergencia entre catálogos, tutor real y snapshots operativos. | Alta | Reemplazar textos por FK cuando el dato represente una entidad; retirar o marcar como legado el tutor textual; documentar y sincronizar snapshots de matrícula. |
| M-07 | Mensajes de validación | La aplicación usa locale y fallback `en` y no tiene directorio `lang`. Requests como Áreas y Temas carecen de mensajes propios para varias reglas. | Parte de los errores puede mostrarse en inglés y romper la UX institucional en español. | Media | Configurar `APP_LOCALE=es`, publicar traducciones y mantener mensajes específicos para reglas de negocio. |
| M-08 | Acceso del postulante | La habilitación se resuelve asociando `users.email` con `postulantes.email_post`; no existe relación explícita usuario-postulante y el email del postulante no es único. Además, `/evaluaciones-postulante` es público y debe diferenciarse claramente del portal restringido. | Asociación ambigua y posible bypass conceptual del control de habilitación. | Alta | Crear relación explícita `user_id` o tabla de vinculación. Mantener la ruta pública solo como presentación claramente identificada y separar cualquier evaluación operativa. |
| M-09 | Integridad de estados | Matrícula acepta cualquier inscripción existente, simulacro acepta programas/plantillas inactivos y asistencia acepta cualquier tutor existente aunque no esté asignado al grupo. Habilitación permite estado `restringido` con todos los accesos activos. | Estados formalmente válidos pero incompatibles con el flujo académico. | Media | Agregar validaciones cruzadas de estados y coherencia, con excepciones explícitas para historial. |

## 5. Hallazgos Menores

| Código | Módulo | Hallazgo | Impacto | Prioridad | Recomendación |
|---|---|---|---|---|---|
| N-01 | Títulos de página | Login y Reportes incluyen “INTELECTA” dentro de `<Head>` y el layout vuelve a agregar el sufijo. | Títulos como “Reportes Académicos - INTELECTA - INTELECTA”. | Baja | Usar en `<Head>` solo el nombre de pantalla. |
| N-02 | Resultados Académicos | El buscador está visible pero deshabilitado. | Control que parece disponible sin ofrecer interacción. | Baja | Ocultarlo hasta que exista filtrado real o reemplazarlo por una nota de alcance. |
| N-03 | Terminología | Persisten `docente`, `estudiante`, `aptitud docente` y `admisión docente` en rutas heredadas, vistas o seeders. | Inconsistencia con Tutor Académico y Postulante. | Media | Sustituir en contenido visible y decidir una migración controlada para nombres de roles/rutas si el documento final exige nueva terminología. |
| N-04 | Datos de referencia | `Colegio San Antonio de Padua` sigue presente en seeders, término señalado para revisión. | Puede aparecer en exposición o documentación con una institución no deseada. | Baja | Reemplazarlo en una futura actualización de datos de referencia, sin borrar registros productivos. |
| N-05 | Vistas heredadas | Permanecen `ModuloPlanificado.jsx`, `Modulos/TutoresAcademicos.jsx` y la ruta `admin.docentes`, aunque el sidebar ya usa módulos funcionales nuevos. | Deuda de mantenimiento y riesgo de reactivar textos antiguos. | Baja | Deprecar o retirar cuando se confirme que no hay enlaces externos. |
| N-06 | Consistencia visual | Postulantes y algunas pantallas evaluativas todavía usan clases directas `slate/indigo`, mientras los módulos institucionales nuevos usan tokens `brand-*`. | Diferencia visual leve entre familias de módulos. | Baja | Homologar gradualmente componentes de formulario, estados y botones con tokens institucionales. |
| N-07 | Accesibilidad | Algunos botones de icono, por ejemplo acciones en Grupos, no tienen `aria-label` explícito. | Menor comprensión para lectores de pantalla. | Baja | Añadir nombres accesibles a todas las acciones de solo icono. |

## 6. Revisión del Sidebar

**Resultado:** cumple parcialmente.

La jerarquía visual coincide con el sidebar final esperado:

- INICIO
- GESTIÓN INSTITUCIONAL
- GESTIÓN DE POSTULANTES
- GESTIÓN EVALUATIVA
- REPORTES Y ANÁLISIS
- ADMINISTRACIÓN DEL SISTEMA

Comprobaciones:

- Todas las opciones visibles cargaron una pantalla.
- No se detectaron rutas duplicadas.
- La sección correspondiente a la ruta activa se abre automáticamente.
- Los grupos usan controles desplegables con `aria-expanded`.
- No se observó overflow horizontal global en escritorio o móvil.
- Usuarios y Roles y Permisos se ocultan según permisos compartidos.
- Las demás opciones institucionales no están ocultas por permiso, en concordancia con el hallazgo C-01.

Inconsistencias:

| Opción | Ruta actual | Estado |
|---|---|---|
| Dashboard | `/dashboard` | Correcta |
| Programas a Simulacros | `/admin/institucional/...` | Correctas |
| Postulantes | `/postulantes` | Correcta |
| Ficha Académica | `/postulantes` | Inconsistente; no es un índice de fichas |
| Ranking Académico | `/admin/institucional/ranking` | Correcta |
| Materias a Learning Analytics | rutas declaradas | Correctas |
| Usuarios, Roles y Configuración | rutas declaradas | Correctas |

**Recomendación para Ficha Académica:** aplicar la opción A. Crear un índice propio con buscador de postulantes, datos mínimos y botón “Ver ficha”. La opción B solo sería adecuada si también se retira el submódulo del sidebar y del mapa documental. Dado que el Capítulo 5 lo presenta como módulo, la opción A conserva mejor la coherencia.

Rutas heredadas no visibles en el sidebar:

- `/admin/gestion-academica/docentes`
- `/admin/evaluaciones`

Conviene decidir si se mantienen como compatibilidad temporal o se deprecan.

## 7. Matriz de Validaciones de Fechas

| Módulo | Campo | Regla esperada | Estado actual | Corrección recomendada | Mensaje sugerido |
|---|---|---|---|---|---|
| Programas Académicos | `fecha_inicio_prog` | Para un programa nuevo planificado: hoy o futuro. Edición histórica: conservar fecha original. | Backend solo `date`; frontend sin `min`. | Regla condicional en alta y `min={hoy}` solo al crear. | “La fecha de inicio de un programa nuevo no puede ser anterior a la fecha actual.” |
| Programas Académicos | `fecha_fin_prog` | `fecha_fin >= fecha_inicio`. | Cumple backend y frontend usa `min` igual al inicio. | Mantener; añadir prueba de edición histórica. | “La fecha de finalización debe ser posterior o igual a la fecha de inicio.” |
| Grupos y Paralelos | No aplica | No existen fechas en el formulario actual. | No aplica. | Sin cambio. | No aplica. |
| Tutores Académicos | No aplica | No existen fechas contractuales en el formulario actual. | No aplica. | Sin cambio. | No aplica. |
| Asignación de Tutores | `fecha_inicio_asig` | Alta activa planificada: hoy o futuro; permitir historial controlado. | Backend solo `date`; frontend sin `min`. | Validación condicional por alta/estado y `min={hoy}` en creación. | “La fecha de inicio de una asignación activa no puede ser anterior a la fecha actual.” |
| Asignación de Tutores | `fecha_fin_asig` | `fecha_fin >= fecha_inicio`. | Cumple backend y frontend. | Además, usar la fecha para determinar vigencia efectiva. | “La fecha de finalización debe ser posterior o igual a la fecha de inicio.” |
| Inscripciones | `fecha_inscripcion` | Puede ser histórica; no debería ser futura si representa una inscripción realizada. | Backend solo `date`; frontend sin `max`. | `before_or_equal:today` y `max={hoy}`, salvo modo de planificación explícito. | “La fecha de inscripción no puede ser posterior a la fecha actual.” |
| Matrículas | `fecha_matricula_mat` | Puede ser histórica; no debe ser futura si la matrícula ya fue registrada. | Backend solo `date`; frontend sin `max`; si está vacía se completa con hoy. | `before_or_equal:today` y `max={hoy}`. | “La fecha de matrícula no puede ser posterior a la fecha actual.” |
| Cuotas | `fecha_vencimiento_cuota` | Admite pasado o futuro; debe ser coherente con el periodo/matrícula. | Solo valida formato. | Mantener flexibilidad y documentar regla de periodo; opcionalmente validar contra inicio de matrícula. | “La fecha de vencimiento no corresponde al periodo de la matrícula.” |
| Cuotas | `fecha_pago_cuota` | Si estado es pagada: requerida y no futura. Puede ser anterior, igual o posterior al vencimiento. | Solo valida formato; frontend sin `max`. | `required_if:estado_cuota,pagada`, `before_or_equal:today`, limpiar fecha si no está pagada y usar `max={hoy}`. | “La fecha de pago no puede ser posterior a la fecha actual.” |
| Habilitación | `fecha_inicio_hab` | Fecha válida; puede ser histórica o futura según habilitación temporal. | Cumple formato. | Mantener y precisar semántica por estado. | “La fecha de inicio no tiene un formato válido.” |
| Habilitación | `fecha_fin_hab` | `fecha_fin >= fecha_inicio`. | Cumple backend; frontend no define `min`. | Añadir `min={fecha_inicio_hab}`. | “La fecha de finalización debe ser posterior o igual a la fecha de inicio.” |
| Asistencia individual | `fecha_asist` | No futura. | Cumple backend y frontend `max={hoy}`. | Mantener. | “La asistencia no puede registrarse en una fecha futura.” |
| Asistencia grupal | `fecha_asist` | No futura. | Cumple backend y frontend. | Mantener. | “La asistencia no puede registrarse en una fecha futura.” |
| Simulacros | `fecha_sim` | Nuevo simulacro: hoy o futuro; edición histórica sin cambiar fecha: permitida. | Cumple mediante regla personalizada y `min` condicional. | Mantener y cubrir con prueba. | “La fecha del simulacro no puede ser anterior a la fecha actual.” |
| Simulacros | `hora_fin_sim` | `hora_fin > hora_inicio`. | Cumple backend. | Opcionalmente limitar frontend al elegir hora de inicio. | “La hora de finalización debe ser posterior a la hora de inicio.” |
| Plantillas | No aplica | La plantilla no tiene fecha de aplicación. | No aplica. | La fecha corresponde al simulacro/evaluación aplicada, no a la plantilla. | No aplica. |
| Resultados | Fecha de aplicación | Un resultado real no debe registrar una aplicación futura. | El módulo funcional de resultados aplicados no existe. | Incluir la regla cuando se implemente evaluación aplicada. | “La fecha de aplicación no puede ser posterior a la fecha actual.” |

## 8. Matriz de Validaciones Generales

| Módulo | Validación esperada | Estado actual | Corrección recomendada |
|---|---|---|---|
| Programas | Nombre requerido, código único, estado válido, fechas relacionadas. | Cumple; universidad y carrera son texto libre. | Normalizar referencias y completar regla de fecha inicial. |
| Grupos | Programa existente, nombre, código único por programa, capacidad positiva. | Cumple reglas básicas. | Exigir programa activo y proteger reducción de capacidad por debajo de inscritos. |
| Tutores | Nombres/apellidos, usuario y C.I. únicos, email válido, estado. | Cumple. | Si se vincula usuario, comprobar rol o capacidad institucional esperada. |
| Asignación de Tutores | Tutor, programa/grupo, coherencia grupo-programa, fechas y estado. | Cumple reglas básicas. | Evitar solapamientos, exigir entidades activas y considerar vigencia por fecha. |
| Inscripciones | Programa, postulante, grupo perteneciente al programa, no duplicado. | Cumple duplicidad y relaciones. | Validar estados activos y capacidad disponible dentro de transacción. |
| Matrículas | Inscripción, código único, monto no negativo, estado. | Cumple. | Exigir inscripción activa y definir si la unicidad es histórica o solo para matrícula activa. |
| Cuotas | Matrícula, número único, monto no negativo, fechas y estado. | Cumple formato, monto y duplicado. | Relacionar estado con fecha de pago y calcular vencimiento efectivo. |
| Habilitación | Estado, fechas y booleanos. | Cumple estructura. | Validar consistencia estado/accesos y preservar overrides manuales. |
| Asistencia | Grupo, postulante inscrito, fecha no futura, estado y duplicidad. | Cobertura sólida en individual y grupal. | Definir documentalmente si retraso/justificado computan como asistencia plena. |
| Simulacros | Programa, grupo perteneciente, plantilla, fecha, horas y estado. | Cobertura sólida. | Exigir entidades activas al crear y permitir historial al editar. |
| Postulantes | Nombres, C.I. único, email, edad, carrera-universidad, gestión y estado. | Cumple. `id_uni` actúa como campo de selección, no se duplica en tabla. | Mantener relación mediante carrera; considerar email único solo si será identidad de acceso. |
| Carreras | Universidad, nombre único por universidad, estado y exigencia. | Existe restricción de BD; no hay CRUD/FormRequest administrativo. | Crear CRUD controlado o declarar catálogo administrado por datos maestros. |
| Colegios | Nombre único, tipo, ubicación y estado. | Existe restricción de BD; no hay CRUD/FormRequest administrativo. | Crear CRUD controlado o declarar catálogo de consulta. |
| Materias | Código único, nombre, estado. | Existe modelo/catálogo; pantalla de solo lectura. | Incorporar gestión solo si el alcance institucional lo requiere. |
| Áreas | Materia, nombre, descripción y estado. | Falta materia en request/DTO/formulario; nombre es único global. | Resolver C-02 y evaluar unicidad `id_mat + nombre_area`. |
| Temas | Área, nombre único por área, nivel y estado. | Cumple; faltan mensajes personalizados completos. | Configurar traducción española y filtrar áreas activas/clasificadas. |
| Banco de Preguntas | Tema, tipo, dificultad, puntaje, taxonomía y alternativa correcta. | Cobertura sólida; admite preguntas antiguas sin tema. | Mantener compatibilidad y mostrar claramente “Sin clasificación”. |
| Plantillas | Preguntas existentes/no repetidas, orden, puntajes positivos y total 100. | Cobertura sólida. | Validar que preguntas inactivas no se incorporen a plantillas nuevas. |
| Resultados | Evaluación aplicada, postulante, respuestas, puntaje y fecha. | No existe flujo transaccional; vista estática. | Implementar modelo y validación antes de declarar módulo completo. |

## 9. Revisión Visual y UX

Observaciones favorables:

- Dashboard y módulos institucionales recientes mantienen identidad Elite Prep Institute.
- Cards, banners, badges, modales y estados vacíos tienen padding adecuado.
- El modal de detalle de Tutores abre correctamente y presenta perfil, contacto, usuario asociado y asignaciones.
- Los formularios institucionales muestran errores junto a los campos mediante componentes reutilizables.
- Las tablas principales usan wrap, anchos controlados o variantes móviles.
- No se detectó overflow horizontal global en las pantallas revisadas a 1280 px y 390 px.
- No se detectaron errores de consola durante la navegación auditada.
- Los estados vacíos son explícitos en la mayoría de módulos.
- `brand-danger` se usa principalmente para restricciones, vencimientos y alertas reales.

Observaciones a corregir:

- Resultados Académicos parece operativo, pero su buscador está deshabilitado y sus datos son fijos.
- Ficha Académica tiene pestañas con overflow controlado; es aceptable en móvil, pero necesita índice propio.
- Carreras, Colegios, Materias y Configuración parecen módulos completos aunque solo ofrecen consulta.
- Postulantes y parte de Gestión Evaluativa todavía usan estilos directos `slate/indigo` en vez de tokens institucionales.
- Algunos botones de icono no tienen texto accesible.
- Las pantallas heredadas pueden conservar contenido o estética que ya no corresponde al sidebar actual.
- Conviene homologar “Ver”, “Editar”, “Inactivar” y sus `aria-label` en todos los módulos.

## 10. Revisión de Terminología

| Término encontrado | Archivo o módulo | Reemplazo recomendado | Prioridad |
|---|---|---|---|
| `docentes` / `Docente` | `routes/web.php`, roles, usuarios y vista heredada de tutores | `tutores` / `Tutor Académico`, salvo que el rol Docente deba mantenerse por compatibilidad | Media |
| `estudiante` / `Estudiante` | rutas, rol, portal y algunos textos | `postulante`, excepto identificadores técnicos que requieran migración planificada | Media |
| “Seguimiento de Rendimiento Estudiantil” | Resultados Académicos | “Seguimiento del Rendimiento de Postulantes” | Media |
| “Interpretación docente” | Learning Analytics | “Interpretación tutorial” o “Lectura para coordinación académica” | Baja |
| “Conclusiones automáticas simuladas” | Learning Analytics | “Conclusiones referenciales de la vista conceptual” | Media |
| “Simulación analítica institucional” | Learning Analytics | “Vista conceptual de análisis institucional” | Baja |
| “aptitud docente” | `Public/EvaluacionesPostulante.jsx` | “aptitud académica” | Media |
| “admisión docente” | `PlantillasEvaluacionSeeder.php` | “admisión universitaria” | Media |
| `Colegio San Antonio de Padua` | seeders de colegios/postulantes | Sustituir por un nombre institucional autorizado | Baja |
| `admin.docentes` | ruta heredada | Deprecar o renombrar de forma compatible | Baja |
| `placeholder` | atributos HTML y componentes internos | No requiere reemplazo: no es texto visible como estado del módulo | Informativa |
| `fake()` | factory estándar de Laravel | No requiere reemplazo: no aparece en interfaz | Informativa |
| `AccessFlag` | identificador interno React | No requiere reemplazo: no es terminología visible | Informativa |

No se encontraron referencias visibles a CTF, hacking, estenografía ni Casa Amandita en el alcance revisado.

## 11. Coherencia con Capítulo 5

| Módulo documental | Estado en sistema | Observación | Acción recomendada |
|---|---|---|---|
| Dashboard | Implementado | Métricas reales de estructura curricular e institucional, con protecciones parciales `Schema::hasTable`. | Mantener y asegurar autorización. |
| Programas Académicos | Implementado | CRUD operativo mediante modal/cards. | Normalizar universidad/carrera y completar fecha inicial. |
| Grupos y Paralelos | Implementado | CRUD, cupos y tutor visible. | Validar capacidad contra inscripciones y retirar tutor textual legado. |
| Tutores Académicos | Implementado | CRUD y detalle modal verificados. | Aplicar permisos y revisar rol de usuario vinculado. |
| Asignación de Tutores | Implementado parcialmente | CRUD operativo, sin control de solapamientos/vigencia. | Resolver M-04. |
| Inscripciones Académicas | Implementado parcialmente | CRUD y relaciones, sin bloqueo de sobrecupo/entidades inactivas. | Resolver M-03. |
| Matrículas y Cuotas | Implementado parcialmente | CRUD administrativo y métricas. | Corregir estados por fecha y reglas de pago. |
| Habilitación Académica | Implementado parcialmente | Edición y acceso al portal; riesgo de sobrescritura manual. | Resolver C-04 y fortalecer vínculo de usuario. |
| Control de Asistencia | Implementado | Registro individual/grupal, métricas y ficha. | Definir política de cómputo y permisos. |
| Calendario de Simulacros | Implementado | CRUD, fechas y horas bien validadas. | Exigir entidades activas al crear. |
| Postulantes | Implementado | CRUD, filtros, universidad/carrera y detalle. | Homologar identidad visual y vínculo con usuario. |
| Ficha Académica | Implementado parcialmente | Detalle completo, pero sin índice propio. | Crear índice por postulante. |
| Ranking Académico | Implementado con datos referenciales | Usa `rendimientos_postulante`, no resultados aplicados. | Integrar con resultados reales. |
| Materias | Implementado como catálogo | Solo lectura. | Definir si requiere CRUD. |
| Áreas de Conocimiento | Requiere corrección | CRUD no persiste materia. | Resolver C-02. |
| Temas Académicos | Implementado | CRUD y relación con área. | Mejorar mensajes y filtrar estructura vigente. |
| Banco de Preguntas | Implementado | Taxonomía, alternativas, filtros y estados. | Mantener compatibilidad y permisos. |
| Plantillas de Evaluación | Implementado | Composición, ponderación y estados. | Evitar preguntas inactivas en nuevas plantillas. |
| Resultados Académicos | Parcial / referencial | Pantalla estática sin persistencia de aplicaciones. | Resolver C-03. |
| Reportes Académicos | Implementado para estructura y catálogos | Métricas reales de postulantes y cobertura; no reporta resultados aplicados. | Ampliar después del módulo de resultados. |
| Indicadores de Desempeño | Implementado con rendimientos referenciales | Lenguaje prudente y sin promesa predictiva. | Integrar con resultados trazables. |
| Learning Analytics | Futuro conceptual | Disclaimers adecuados; datos hardcodeados y modelos no operativos. | Mantener como arquitectura conceptual hasta validación histórica. |
| Usuarios | Implementado | CRUD con permisos. | Resolver flujo de usuarios sin rol. |
| Roles y Permisos | Implementado | Spatie operativo para módulos existentes. | Extender permisos a Gestión Institucional. |
| Configuración | Implementado como vista informativa | Sin edición de parámetros. | Documentar alcance o agregar configuración auditable futura. |
| Carreras y Colegios | Implementados como catálogos | Consulta real, sin CRUD. | Definir administración de datos maestros. |

Módulos que deben permanecer como fase futura:

- Aplicación completa de evaluaciones y persistencia de respuestas.
- Cálculo trazable de resultados por materia/tema.
- Learning Analytics entrenado y validado.
- Modelos IRT, clustering, regresión logística y Random Forest operativos.
- Notificaciones automáticas y configuración institucional editable, si se aprueba su alcance.

## 12. Revisión de Normalización

La estructura principal mantiene una separación adecuada:

- `users` administra autenticación y acceso.
- `tutores_academicos` conserva el perfil profesional y `user_id` opcional.
- `postulantes` conserva datos personales y objetivos académicos.
- `inscripciones_academicas` vincula postulante, programa y grupo.
- `matriculas_academicas` y `cuotas_academicas` separan control administrativo.
- `habilitaciones_academicas` separa decisión de acceso.
- `materias`, `areas_conocimiento`, `temas`, `preguntas` y `alternativas` forman una jerarquía curricular.
- `plantilla_preguntas` resuelve la relación muchos-a-muchos con ponderación.
- `rendimientos_postulante` separa lectura académica de datos personales.

Redundancias justificables:

- `id_post`, `id_prog` e `id_grupo` en matrícula pueden funcionar como snapshot operativo derivado de la inscripción para consultas frecuentes. Debe documentarse y sincronizarse si cambia la inscripción.
- `id_prog` en asistencia facilita reportes, aunque puede derivarse del grupo. La aplicación ya lo normaliza desde el grupo al guardar.

Riesgos:

1. Universidad y carrera/área de programa son textos libres, aun existiendo catálogos.
2. `tutor_responsable_grupo` duplica la responsabilidad definida en `asignaciones_tutores`.
3. El CRUD de Áreas no mantiene `id_mat`, pese a la FK disponible.
4. `rendimientos_postulante` no registra la evaluación o periodo origen; carece de trazabilidad hacia resultados aplicados.
5. La restricción única `id_insc` en matrícula impide más de una matrícula histórica por inscripción, incluso con soft delete. Debe confirmarse si esa cardinalidad es intencional.
6. Las restricciones únicas junto con soft deletes pueden impedir reutilizar códigos, C.I. o vínculos eliminados lógicamente. Es válido si la política institucional prohíbe reutilización; en caso contrario necesita índices parciales de PostgreSQL.
7. La habilitación tiene unicidad por inscripción y matrícula, pero no una regla documental explícita sobre múltiples registros históricos por postulante.

Conclusión de normalización: la base está mayormente alineada con 3FN, con snapshots operativos razonables, pero existen textos y campos legados que pueden generar divergencia. La corrección debe priorizar fuentes únicas de verdad y trazabilidad antes de ampliar reportes.

## 13. Recomendaciones de Corrección por Orden

### 1. Correcciones críticas

1. Cerrar acceso institucional mediante permisos Spatie por recurso/acción y bloquear usuarios sin rol.
2. Incorporar materia al CRUD de Áreas y normalizar áreas existentes sin materia.
3. Diseñar el flujo mínimo de evaluación aplicada, respuestas y resultados trazables.
4. Separar habilitación calculada de override manual y calcular cuotas vencidas por fecha.

### 2. Correcciones medias

1. Crear índice propio de Ficha Académica.
2. Completar reglas condicionales de fechas y coherencia de estados.
3. Controlar capacidad y entidades activas en inscripciones.
4. Evitar solapamientos de asignaciones tutoriales y respetar vigencia.
5. Normalizar programa-universidad/carrera y retirar tutor textual legado.
6. Configurar validaciones globales en español.
7. Vincular explícitamente usuario y postulante.
8. Definir alcance real de catálogos y Configuración.

### 3. Correcciones visuales

1. Corregir títulos duplicados del navegador.
2. Retirar el buscador deshabilitado de Resultados.
3. Homologar tokens visuales en Postulantes y Gestión Evaluativa.
4. Añadir etiquetas accesibles a botones de icono.
5. Reemplazar terminología docente/estudiante donde no sea parte de una migración técnica.

### 4. Correcciones opcionales

1. Deprecar rutas y vistas heredadas.
2. Revisar datos institucionales de seeders.
3. Definir índices parciales para soft deletes si se permite reutilización.
4. Incorporar auditoría de cambios administrativos.
5. Agregar pruebas de autorización, fechas, cupos, sincronización y trazabilidad cuando se abra la fase de estabilización.

## 14. Conclusión

INTELECTA cuenta con una cobertura modular superior a una maqueta de evaluaciones y ya comunica una plataforma administrativa-académica institucional. La navegación, presentación responsive, estructura por dominios y separación general de entidades son sólidas para exposición y evolución.

No obstante, requiere correcciones antes del cierre:

- autorización institucional;
- integridad de la jerarquía curricular;
- resultados aplicados trazables;
- reglas de habilitación y vencimiento;
- coherencia de fechas, cupos y estados.

Después de resolver los cuatro hallazgos críticos y los controles medios de mayor impacto, el sistema podrá entrar en estabilización funcional. Learning Analytics debe continuar identificado como arquitectura conceptual y lectura referencial hasta disponer de resultados históricos reales, metodología validada y métricas de desempeño verificables.

