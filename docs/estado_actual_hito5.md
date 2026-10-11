# Estado actual de INTELECTA — Hito 5

Fecha: **11 de octubre de 2026**. Repositorio oficial: [vicentesystemdev/intelecta](https://github.com/vicentesystemdev/intelecta). Esta revisión consolida el desarrollo existente; no implementa un nuevo motor académico ni actualiza dependencias.

## Estado inicial de Git

La copia estaba limpia en `develop`, commit `76a2a30`; coincidía con `origin/develop`. `main` y `origin/main` apuntaban a `a62edee`, del 17 de junio de 2026. Nueve commits de `develop` no estaban en `main`, cuyo historial era antecesor de `develop`; por ello la integración admite avance directo sin forzar ni descartar historia.

## Módulos y evidencia

| Capacidad | Estado | Evidencia del código |
| --- | --- | --- |
| Autenticación y cuentas | Implementada; sin registro público | [rutas auth](../routes/auth.php), `CuentaService`, `EnsureAccountAccess`, pruebas `Auth/AccountLifecycleTest.php` |
| Roles, permisos y ámbito docente | Implementada, con soporte multirol | [MatrizRbac](../app/Domains/Seguridad/Support/MatrizRbac.php), `AmbitoDocenteService`, políticas en `app/Policies`, pruebas `Seguridad` y `Academico/AmbitoDocenteTest.php` |
| Bitácora y exportación | Implementada | `BitacoraSistemaController`, `BitacoraService`, pruebas `Seguridad/BitacoraSistemaTest.php` |
| Postulantes y cuenta vinculada | Implementada | `PostulanteService`, `postulantes.user_id`, fecha de nacimiento y pruebas `Postulantes`/`Portal` |
| Cargos, personal y tutores | Implementada | `OrganizacionService`, `TutorAcademicoService`, identidad `personal_id`, pruebas `Institucional` |
| Programas, grupos e inscripciones | Implementada | `AcademicoService`, `ContextoAcademicoService`, `ElegibilidadInscripcionService`, pruebas de integridad y concurrencia |
| Matrículas y cuotas | Implementada con límites de habilitación | `MatriculaCuotaService` y `HabilitacionAcademicaRepository` |
| Asistencia y asignaciones | Implementada | `AsistenciaAcademicaService`, `AsignacionTutorService`, pruebas de ámbito e integridad |
| Materias, áreas, temas y preguntas | Implementada | `MateriaController`, servicios `Evaluaciones`, `ConsistenciaEvaluacionService`, pruebas `Evaluaciones` |
| Plantillas y simulacros | Implementada | `PlantillaEvaluacionService`, `SimulacroProgramadoController`, migraciones y pruebas `Academico`/`Resultados` |
| Aplicación y resultados | Implementada para preguntas de alternativas | [EvaluacionAplicadaService](../app/Domains/Resultados/Services/EvaluacionAplicadaService.php), [ResultadoAcademicoService](../app/Domains/Resultados/Services/ResultadoAcademicoService.php), [pruebas de trazabilidad](../tests/Feature/Resultados/ResultadosTrazablesTest.php) |
| Ficha, ranking y reportes | Implementada como seguimiento descriptivo | `ResultadoAcademicoRepository`, `AcademicoService`, `ReporteAcademicoController`, `ReporteExportacionService`, pruebas de reportes y portal |
| Indicadores de riesgo | Implementación descriptiva acotada | `ResultadoAcademicoRepository` clasifica por promedio; ruta de indicadores consulta hasta 24 registros |
| Learning Analytics | Conceptual / parcialmente presentado | [LearningAnalytics.jsx](../resources/js/Pages/Analisis/LearningAnalytics.jsx) contiene arreglos estáticos; ruta sin consultas en [web.php](../routes/web.php) |
| Catálogos y configuración | Alcance parcial de consulta | Carreras/colegios y Configuración; no equivalen a gestión completa de todos los maestros |
| Portadas de evaluaciones/tutores y catálogo público | Presentación estática parcial | `Modulos/CentroEvaluaciones.jsx`, `Modulos/TutoresAcademicos.jsx`, `Public/EvaluacionesPostulante.jsx` |
| TRI 3PL, EAP, CAT y modelos predictivos validados | Pendiente, fuera del alcance | No se implementan en esta consolidación; `intelecta_lab` es independiente |

## Resolución de la discrepancia sobre resultados

Las auditorías de junio, especialmente [auditoría v2](auditoria_final_sistema_intelecta_v2.md), describen el resultado como referencial. Esa conclusión corresponde a una revisión histórica y no representa el código actual:

1. Las migraciones del 15 de junio crean `evaluaciones_aplicadas` y `respuestas_evaluacion`.
2. `EvaluacionAplicadaService::enviar()` valida propiedad del intento, plantilla, preguntas y alternativas, y guarda una respuesta por pregunta en una transacción. La alternativa correcta obtiene el puntaje ponderado; la incorrecta u omitida obtiene cero.
3. `ResultadoAcademicoService::recalculate()` suma puntajes obtenidos y máximos de las respuestas persistidas y calcula `obtenido / máximo × 100`, con dos decimales.
4. El intento queda finalizado y `ResultadoAcademicoRepository::updatePerformance()` consolida evaluaciones finalizadas y promedios por materia.
5. `ResultadosTrazablesTest` verifica persistencia, cálculo, acceso ajeno, segundo envío, omisiones y rechazo de alternativas o plantillas inconsistentes.

El escenario de demostración utiliza las Actions del mismo flujo para generar respuestas ficticias. **El cálculo es real; la población y las respuestas del seeder son simuladas.** La pantalla Learning Analytics mantiene sus propios datos estáticos y no se debe confundir con los resultados operativos.

## Mejoras de esta consolidación

- README institucional y cuatro guías de entrega, índice de documentación y registro de verificaciones.
- `.env.example` alineado a PostgreSQL y nombre INTELECTA, sin claves reales.
- Nombre de respaldo INTELECTA en configuración, documento HTML e Inertia.
- Avisos explícitos de datos simulados en portadas y Learning Analytics, sin rediseño.
- Carga de cuentas demo restringida a `local`/`testing`, antes de modificar usuarios o permisos.
- Enlaces históricos convertidos a rutas relativas verificables y avisos que remiten al estado vigente, conservando el contenido de auditoría.
- Exclusión adicional de variantes `.env`, bases SQLite y carpetas de respaldos/dumps.

No se ejecutan migraciones ni seeders contra la base local de trabajo y no se cambian contraseñas reales.

## Pendientes y prioridad

| Prioridad | Pendiente comprobado | Implicación |
| --- | --- | --- |
| Alta antes de producción | Revisar vulnerabilidades de dependencias | `npm audit` reporta 28 avisos, incluidos 3 críticos, en el árbol bloqueado; se requiere evaluación y actualización controlada con pruebas, sin ejecutar correcciones masivas antes de la defensa |
| Alta antes de producción | Separar estado automático y ajuste manual de habilitación | `syncForMatricula()` actualiza el mismo registro después de modificar cuotas y puede sustituir ajustes manuales; exige diseño e historial, fuera de esta entrega |
| Alta antes de múltiples cohortes reales | Revisar alcance del rendimiento consolidado | `updatePerformance()` agrega todas las evaluaciones finalizadas del postulante y las asocia a la inscripción más reciente; no separa todos los períodos/programas de origen |
| Media antes de exigir inmutabilidad histórica | Versionar preguntas, alternativas y plantillas aplicadas | Se validan cambios de estado y puntaje, pero el intento no conserva una copia completa e inmutable del instrumento |
| Media | Validación PostgreSQL completa y concurrencia en CI aislada | SQLite no demuestra los bloqueos ni todos los CHECK de PostgreSQL; las pruebas omitidas deben identificarse como tales |
| Media | Conciliar identidades legacy antes de actualizar bases antiguas | La migración final Tutor → Personal exige vínculos consistentes; la instalación nueva evita esta precondición histórica |
| Baja para esta defensa | Completar portadas, configuración y catálogos con flujos operativos | Exponerlos según su alcance real; usar las rutas funcionales documentadas |
| Evolución futura | Learning Analytics con datos persistidos y modelos validados | Requiere diseño, datos y validación; actualmente es una demostración conceptual |

Los [resultados de las pruebas](verificacion_hito5.md) distinguen verificaciones actuales de las evidencias históricas. El [recorrido de defensa](guia_demostracion.md) prioriza los módulos operativos y declara las limitaciones.
