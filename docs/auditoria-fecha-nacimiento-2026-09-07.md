# INTELECTA — Fecha de nacimiento y transición gradual de edad

Fecha: 07/09/2026. Implementación completada únicamente en postulantes. Nueva migración aplicada localmente; sin backfill, sin commit ni push. Los cambios de la fase previa y composer.json se preservaron.

## 1. Usos encontrados de edad_post

Se inspeccionaron código PHP/React, migraciones, seeders, factories, tests, SQL, reportes, exportaciones y documentación, excluyendo dependencias, Git y assets compilados. La búsqueda inicial encontró referencias directas en 18 archivos:

| Archivo | Uso | Acción |
| --- | --- | --- |
| [2026_06_08_230000_create_postulantes_table.php](C:/laragon/www/intelecta/database/migrations/2026_06_08_230000_create_postulantes_table.php) | Columna histórica nullable. | Sin cambios; no se elimina ni se modifica una migración aplicada. |
| [2026_09_07_000000_add_basic_input_checks.php](C:/laragon/www/intelecta/database/migrations/2026_09_07_000000_add_basic_input_checks.php) | CHECK edad BETWEEN 14 AND 80. | Intacto y validado en PostgreSQL. |
| [Postulante.php](C:/laragon/www/intelecta/app/Domains/Postulantes/Models/Postulante.php) | Fillable/cast de edad almacenada. | Se conservan para legacy; nuevo accessor edad_actual prioriza fecha. |
| [StorePostulanteRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Postulantes/StorePostulanteRequest.php) | Entrada y reglas de edad manual. | Fecha estricta obligatoria en CREATE; edad enviada se excluye. |
| [PostulanteData.php](C:/laragon/www/intelecta/app/Domains/Postulantes/DTOs/PostulanteData.php) | Propiedad edadPost y escritura de edad_post. | Sustituidas por fechaNacimientoPost; nunca sobrescribe edad histórica. |
| [ReporteAcademicoController.php](C:/laragon/www/intelecta/app/Http/Controllers/ReporteAcademicoController.php) | Construcción de edad para reporte individual. | Solo cambia la fuente a edad_actual; sin corregir otras reglas del reporte. |
| [PostulanteForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Postulantes/PostulanteForm.jsx) | Edad editable y payload. | Campo controlado DD/MM/AAAA; sin edad manual en payload. |
| [inputValidation.js](C:/laragon/www/intelecta/resources/js/lib/inputValidation.js) | Metadatos HTML de edad 14–80. | Metadatos de longitud/formato de fecha. |
| [Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Postulantes/Index.jsx) | Edad en tabla y modal de detalle. | edad_actual; fecha latinoamericana en detalle. |
| [Show.jsx](C:/laragon/www/intelecta/resources/js/Pages/Postulantes/Show.jsx) | Edad del detalle. | edad_actual más fecha o aviso pendiente. |
| [Show.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/FichaAcademica/Show.jsx) | Edad en ficha académica. | edad_actual más fecha latinoamericana. |
| [PostulantesSeeder.php](C:/laragon/www/intelecta/database/seeders/PostulantesSeeder.php) | 60 fixtures con edades explícitas. | 60 fixtures con fechas sintéticas explícitas; no completa fechas de registros preexistentes. |
| [BaseLimpiaAvalanchaSeeder.php](C:/laragon/www/intelecta/database/seeders/BaseLimpiaAvalanchaSeeder.php) | Edades generadas para 72 fixtures. | Lista de fechas sintéticas explícitas para nuevos fixtures; no deriva fechas de edades. |
| [PostulanteModuleTest.php](C:/laragon/www/intelecta/tests/Feature/Postulantes/PostulanteModuleTest.php) | Payload válido y rechazo de menor de edad. | Payload con fecha; conserva prueba de mínimo desde fecha relativa. |
| [RequestValidationTest.php](C:/laragon/www/intelecta/tests/Feature/Validation/RequestValidationTest.php) | Límites/formas inválidas y bordes de edad. | Mismos límites de admisión comprobados desde fechas; validaciones ajenas intactas. |
| [input-validation.test.mjs](C:/laragon/www/intelecta/tests/input-validation.test.mjs) | Comprobación de metadatos edad. | Metadatos y round-trip de fechas, calendario y contrato del formulario. |
| [validation.php](C:/laragon/www/intelecta/lang/es/validation.php) | Etiqueta humana de edad. | Conservada para legacy; nueva etiqueta y mensajes de fecha en español. |
| [auditoria-validaciones-basicas-2026-09-07.md](C:/laragon/www/intelecta/docs/auditoria-validaciones-basicas-2026-09-07.md) | Fotografía de la fase anterior (columna, reglas, metadatos). | Documento histórico preservado; este informe describe la evolución posterior. |

Revisión de consumidores indirectos:

- Actions CrearPostulanteAction/ActualizarPostulanteAction, PostulanteService, PostulanteRepository y PostulanteController transmiten el DTO o serializan el modelo. No requerían una edad propia ni cambios arquitectónicos.
- UpdatePostulanteRequest heredaba las reglas; ahora diferencia opcionalidad legacy y elegibilidad de alta.
- El repositorio de postulantes, la ficha y la consulta individual del reporte cargan el modelo con sus columnas de edad/fecha. No hay filtros, ordenamientos ni agregaciones SQL por edad_post que necesiten convertir a cálculo dinámico.
- Reportes/Index.jsx consume la clave edad construida en ReporteAcademicoController, ahora calculada. Los exportadores y servicios de exportación inspeccionados no consumen edad_post; no se modificaron.
- LearningAnalytics.jsx contiene edades en datos demostrativos locales con clave age, no una consulta de edad_post. Se identificó expresamente y no se convirtió este cambio en una reparación del módulo analítico.
- No existe factory de Postulante. Los fixtures directos de otros tests que no aportan fecha siguen admitidos como legacy por la columna nullable.
- No existe datepicker configurable ni utilidad compartida de fecha pura reutilizable en los componentes/lib inspeccionados. Se reutilizan Input, Label, InputError, useForm y validationProps; no se instalaron paquetes.
- El nuevo helper, las pruebas y la migración mencionan edad_post únicamente para exclusión/compatibilidad temporal o documentación.

## 2. Migración creada

[database/migrations/2026_09_07_010000_add_fecha_nacimiento_to_postulantes.php](C:/laragon/www/intelecta/database/migrations/2026_09_07_010000_add_fecha_nacimiento_to_postulantes.php)

Agrega exclusivamente postulantes.fecha_nacimiento_post con tipo PostgreSQL DATE y NULL permitido, sin valor por defecto ni UPDATE de datos. NULL representa una fecha exacta desconocida; una edad histórica no permite reconstruir día y mes.

No se agregaron columnas a users, tutores, administradores, roles ni otros perfiles. No se alteraron las migraciones anteriores ni el CHECK intelecta_postulantes_edad_check. No se creó un CHECK dinámico con CURRENT_DATE.

La migración se aplicó con --path apuntando solo al archivo nuevo. Su down elimina únicamente la nueva columna; no se ejecutó rollback. En el futuro, un rollback requeriría respaldar las fechas reales ya capturadas.

## 3. Flujo final

```text
Usuario: 15/08/2005
    ↓
React: texto controlado, ayuda «Formato: DD/MM/AAAA»
    ↓
NormalizedFormRequest: normalización común
Store/UpdatePostulanteRequest + BirthDate: calendario estricto
    ↓
DTO: fecha_nacimiento_post = 2005-08-15
    ↓
PostgreSQL: DATE 2005-08-15
    ↓
Modelo: serialización date:Y-m-d
    ↓
formatDateLatam: 15/08/2005
```

El formulario no utiliza input type=date ni muestra ISO o MM/DD/YYYY. La utilidad [resources/js/lib/dateOnly.js](C:/laragon/www/intelecta/resources/js/lib/dateOnly.js) centraliza ISO ↔ DD/MM/AAAA por componentes y valida el calendario sin new Date(string), timestamps ni conversión UTC. Laravel acepta también ISO canónico de aplicación; el campo visible exige DD/MM/AAAA.

El backend no corrige silenciosamente una fecha imposible. Solo normaliza una fecha de calendario válida; una entrada inválida permanece disponible para que la regla produzca el error correspondiente.

## 4. Cálculo de edad

El cálculo único está en [BirthDate::age](C:/laragon/www/intelecta/app/Domains/Postulantes/Support/BirthDate.php); [Postulante::edadActual](C:/laragon/www/intelecta/app/Domains/Postulantes/Models/Postulante.php) lo expone como edad_actual y lo incluye en la serialización.

Calcula años completos comparando año y aniversario mes/día. No guarda una edad derivada que pueda quedar obsoleta ni duplica cálculos en React. Para nacidos el 29 de febrero, el incremento en años no bisiestos ocurre el 1 de marzo; está cubierto por tests.

«Hoy» usa la zona configurada por la aplicación, actualmente UTC; no se modificó globalmente esa configuración. Esto determina el día de cálculo, pero jamás transforma el día almacenado de nacimiento. También se probó la serialización con America/La_Paz, Pacific/Kiritimati y America/Los_Angeles.

## 5. Compatibilidad legacy

- Fecha presente: edad_actual se calcula desde ella, aunque edad_post tenga un valor diferente.
- Fecha ausente: edad_actual devuelve edad_post como compatibilidad temporal identificada en el modelo.
- Ambas ausentes: devuelve null sin excepción.
- Las altas HTTP nuevas requieren fecha y dejan edad_post en NULL; el CHECK histórico permite NULL.
- La entrada edad_post se excluye de la validación y el DTO nunca la escribe. Un cliente antiguo o manipulado no puede usarla como fuente de verdad.
- El DTO distingue fecha omitida de fecha enviada: omitirla en UPDATE conserva la fecha almacenada.
- Un legacy puede editar campos ajenos manteniendo su fecha NULL y su edad histórica. El formulario muestra «Fecha de nacimiento pendiente de completar».
- Se puede incorporar posteriormente una fecha real. La edad histórica permanece intacta pero deja de ser la fuente visible.
- Una fecha ya conocida no puede vaciarse explícitamente desde el formulario; sí corregirse a otra fecha válida.

## 6. Validaciones

| Caso | Comportamiento |
| --- | --- |
| Alta sin fecha, vacía o espacios | Rechazo: «La fecha de nacimiento es obligatoria». |
| Formato visible | DD/MM/AAAA, exactamente dos dígitos día/mes y cuatro año; ISO solo como formato canónico compatible del backend. |
| Formatos incorrectos | Rechaza 2005/08/15, 08-15-2005, 15-08-2005 y variantes ambiguas. |
| Calendario | Regex identifica estructura; checkdate comprueba día, mes y año reales. |
| Inexistentes | Rechaza 31/02/2005, 32/01/2005, 15/13/2005, 00/12/2005, 31/04/2005, año cero, etc. |
| Bisiestos | Acepta 29/02/2008 y 29/02/2012; rechaza 29/02/2005 y 29/02/2100. |
| Futuro | Rechaza mañana y fechas del año siguiente, en CREATE y UPDATE. |
| Edad mínima | 14 años completos, con prueba el día anterior, el cumpleaños y el día posterior usando fechas relativas. |
| Máximo 80 | Solo CREATE. No se encontró documentación que establezca su significado institucional; se conserva el comportamiento de alta sin inventar una nueva política. |
| Envejecimiento | Cumplir 81 no invalida la fecha ni bloquea UPDATE. No se fuerza la edad calculada dentro del CHECK legacy. |
| Histórico sin fecha | Puede editarse y consultarse; fecha opcional hasta completar el dato real. |
| Tipos malformados | Arrays, booleanos, números y timestamps como fecha devuelven 422, no 500. |
| Edad del navegador | Se ignora; Laravel recalcula desde la fecha. |

Se reutilizaron NormalizedFormRequest, InputNormalizer, InputRules::MIN_AGE/MAX_AGE y lang/es. [app/Rules/PostulanteBirthDate.php](C:/laragon/www/intelecta/app/Rules/PostulanteBirthDate.php) concentra la validación propia del dominio. Las demás validaciones del postulante siguen activas.

Los dos seeders usan fechas sintéticas explícitas solo para datos de demostración nuevos. No deducen fechas desde edades y no actualizan el nacimiento de registros preexistentes, incluidos soft-deleted. No se ejecutaron esos seeders sobre PostgreSQL.

## 7. Archivos modificados o agregados en esta fase

Comparación por SHA-256 contra el estado al inicio de esta tarea, no contra HEAD (que ya incluía la fase previa). Lista completa: 32 archivos, incluidos informe y evidencias.

- [app/Domains/Postulantes/DTOs/PostulanteData.php](C:/laragon/www/intelecta/app/Domains/Postulantes/DTOs/PostulanteData.php)
- [app/Domains/Postulantes/Models/Postulante.php](C:/laragon/www/intelecta/app/Domains/Postulantes/Models/Postulante.php)
- [app/Domains/Postulantes/Support/BirthDate.php](C:/laragon/www/intelecta/app/Domains/Postulantes/Support/BirthDate.php)
- [app/Http/Controllers/ReporteAcademicoController.php](C:/laragon/www/intelecta/app/Http/Controllers/ReporteAcademicoController.php)
- [app/Http/Requests/Postulantes/StorePostulanteRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Postulantes/StorePostulanteRequest.php)
- [app/Http/Requests/Postulantes/UpdatePostulanteRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Postulantes/UpdatePostulanteRequest.php)
- [app/Rules/PostulanteBirthDate.php](C:/laragon/www/intelecta/app/Rules/PostulanteBirthDate.php)
- [database/migrations/2026_09_07_010000_add_fecha_nacimiento_to_postulantes.php](C:/laragon/www/intelecta/database/migrations/2026_09_07_010000_add_fecha_nacimiento_to_postulantes.php)
- [database/seeders/BaseLimpiaAvalanchaSeeder.php](C:/laragon/www/intelecta/database/seeders/BaseLimpiaAvalanchaSeeder.php)
- [database/seeders/PostulantesSeeder.php](C:/laragon/www/intelecta/database/seeders/PostulantesSeeder.php)
- [docs/audit-input-data.php](C:/laragon/www/intelecta/docs/audit-input-data.php)
- [docs/auditoria-fecha-nacimiento-2026-09-07.md](C:/laragon/www/intelecta/docs/auditoria-fecha-nacimiento-2026-09-07.md)
- [docs/evidencia-fecha-nacimiento/01-crear.png](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/01-crear.png)
- [docs/evidencia-fecha-nacimiento/02-fecha-invalida.png](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/02-fecha-invalida.png)
- [docs/evidencia-fecha-nacimiento/03-editar-fecha.png](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/03-editar-fecha.png)
- [docs/evidencia-fecha-nacimiento/04-legacy-pendiente.png](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/04-legacy-pendiente.png)
- [docs/evidencia-fecha-nacimiento/resultado.json](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/resultado.json)
- [docs/fecha-nacimiento-verificacion-2026-09-07.json](C:/laragon/www/intelecta/docs/fecha-nacimiento-verificacion-2026-09-07.json)
- [docs/test-birth-date-ui.mjs](C:/laragon/www/intelecta/docs/test-birth-date-ui.mjs)
- [docs/test-birth-date-ui.php](C:/laragon/www/intelecta/docs/test-birth-date-ui.php)
- [docs/test-postgres-birth-date.php](C:/laragon/www/intelecta/docs/test-postgres-birth-date.php)
- [lang/es/validation.php](C:/laragon/www/intelecta/lang/es/validation.php)
- [resources/js/Components/Postulantes/PostulanteForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Postulantes/PostulanteForm.jsx)
- [resources/js/Pages/Institucional/FichaAcademica/Show.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/FichaAcademica/Show.jsx)
- [resources/js/Pages/Postulantes/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Postulantes/Index.jsx)
- [resources/js/Pages/Postulantes/Show.jsx](C:/laragon/www/intelecta/resources/js/Pages/Postulantes/Show.jsx)
- [resources/js/lib/dateOnly.js](C:/laragon/www/intelecta/resources/js/lib/dateOnly.js)
- [resources/js/lib/inputValidation.js](C:/laragon/www/intelecta/resources/js/lib/inputValidation.js)
- [tests/Feature/Postulantes/PostulanteBirthDateTest.php](C:/laragon/www/intelecta/tests/Feature/Postulantes/PostulanteBirthDateTest.php)
- [tests/Feature/Postulantes/PostulanteModuleTest.php](C:/laragon/www/intelecta/tests/Feature/Postulantes/PostulanteModuleTest.php)
- [tests/Feature/Validation/RequestValidationTest.php](C:/laragon/www/intelecta/tests/Feature/Validation/RequestValidationTest.php)
- [tests/input-validation.test.mjs](C:/laragon/www/intelecta/tests/input-validation.test.mjs)

Pint produjo además ajustes de formato en los archivos ya tocados (imports/espacios/llaves del controlador de reportes, formato de fixtures y un nombre de tipo equivalente en BaseLimpiaAvalanchaSeeder). No son cambios de reglas de negocio.

La base SQLite y el perfil de Chrome de QA están en storage/framework/testing, ignorados por Git. Contienen solo datos sintéticos. Los procesos de prueba se cerraron; no se eliminaron datos del usuario.

## 8. Tests y verificación visual

| Ejecución | Resultado final | Aserciones / detalle |
| --- | --- | --- |
| php vendor/phpunit/phpunit/phpunit --colors=never | 198/198; 0 fallidos | 1.014 aserciones; 53,519 s; SQLite aislado en memoria. |
| node --test tests/input-validation.test.mjs | 7/7; 0 fallidos | 192,351 ms; Node no publica contador global de aserciones. |
| php docs/test-postgres-input-checks.php | 83/83; 0 fallidos | 83 aserciones, 18 CHECK; tablas temporales y rollback. |
| php docs/test-postgres-birth-date.php [ruta Node] | 11/11; 0 fallidos | 11 aserciones; 757 ms; DATE, normalización, DTO, modelo y helper real React. |
| npm run build | Correcto | 3.445 módulos; 26,37 s; advertencia no bloqueante de tiempos del plugin Laravel. |
| php vendor/bin/pint --dirty --test | Correcto | Sin problemas de formato. |
| git diff --check | Correcto | Sin errores de whitespace. |
| php docs/audit-input-data.php | Correcto | 1.268 registros, 0 incompatibles, 0 pendientes de normalización; lectura solamente. |
| UI Chrome + Playwright ya instalado | 6 escenarios; 17/17 comprobaciones | 0 errores JavaScript; locale en-US, zona America/La_Paz, SQLite aislado. |

La suite inicial de esta fase pasó con 197 tests/979 aserciones; después se añadió cobertura de edad en reporte, listado y ficha académica, y la repetición final pasó con 198/1.014. No se desactivaron ni ocultaron fallos de otras validaciones.

La prueba PostgreSQL usa un clon temporal sin defaults de secuencia y una clave explícita: no inserta postulantes reales ni consume su secuencia. Verifica exactamente entrada 15/08/2005, valor y tipo SQL DATE 2005-08-15, serialización ISO sin hora y salida 15/08/2005 del mismo helper utilizado por React.

La herramienta integrada de navegador falló al iniciar su runtime; se completó la comprobación con Chrome local aislado y Playwright ya disponible, sin instalar dependencias. Se inspeccionaron visualmente las capturas de creación, edición y legacy:

- [Crear: 15/08/2005 y ayuda DD/MM/AAAA](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/01-crear.png).
- [Rechazo visible de fecha inexistente](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/02-fecha-invalida.png).
- [Editar: precarga 15/08/2005](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/03-editar-fecha.png).
- [Legacy: fecha pendiente sin bloquear el formulario](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/04-legacy-pendiente.png).

También se verificó envío de fecha sin edad manual, persistencia de una edición ajena con fecha NULL, posterior captura de fecha real y presentación de edad calculada. Los helpers docs/test-birth-date-ui.php y .mjs permiten repetir en un entorno de QA: nunca deben desplegarse como endpoint de producción y el inicializador se niega a sobrescribir su SQLite existente.

Resultados estructurados: [docs/fecha-nacimiento-verificacion-2026-09-07.json](C:/laragon/www/intelecta/docs/fecha-nacimiento-verificacion-2026-09-07.json) y [docs/evidencia-fecha-nacimiento/resultado.json](C:/laragon/www/intelecta/docs/evidencia-fecha-nacimiento/resultado.json).

## 9. Estado de los registros anteriores

PostgreSQL al terminar:

| Estado | Cantidad |
| --- | ---: |
| Postulantes totales, incluyendo eliminados lógicos | 72 |
| fecha_nacimiento_post IS NULL | 72 |
| fecha_nacimiento_post IS NOT NULL | 0 |
| Eliminados lógicos | 0 |

No se inventó ninguna fecha. El hash SHA-256 del conjunto ordenado id_post/edad_post antes y después coincide:

```text
9b2caee9a1d1619b72c3efd530facf23339c93310f0c73f529185c0f7676589f
```

Las altas realizadas para comprobar la interfaz quedaron exclusivamente en SQLite de QA.

## 10. Pendiente para eliminar edad_post

1. Completar y verificar fechas reales de todos los postulantes afectados, incluidos soft-deleted; nunca completar por estimación. Confirmar mediante consulta que ya no quedan fechas NULL y revisar integraciones/importaciones.
2. Retirar el fallback temporal cuando ninguna lectura lo necesite; comprobar consumidores de edad_actual y contratos externos.
3. Crear una migración posterior que retire intelecta_postulantes_edad_check.
4. Eliminar edad_post en esa migración posterior, con respaldo y revisión previa.
5. Retirar fillable/cast legacy, la exclusión de compatibilidad y código/tests residuales; conservar las migraciones aplicadas y documentación histórica.
6. Repetir regresión, persistencia PostgreSQL y revisión visual antes de aplicar la retirada definitiva.

Ninguno de esos pasos destructivos se ejecutó en esta fase.

## 11. Estado Git final

Rama main. Sin commit ni push. composer.json conserva exactamente su contenido al inicio de esta fase. Las migraciones ya aplicadas y los archivos de validación anteriores que no se necesitaban tocar mantienen sus hashes iniciales.

La siguiente salida incluye también cambios anteriores: no atribuir todo el árbol modificado a esta tarea.

```text
## main...origin/main
 M .env.example
 M app/Domains/Postulantes/DTOs/PostulanteData.php
 M app/Domains/Postulantes/Models/Postulante.php
 M app/Http/Controllers/Auth/ConfirmablePasswordController.php
 M app/Http/Controllers/Auth/NewPasswordController.php
 M app/Http/Controllers/Auth/PasswordController.php
 M app/Http/Controllers/Auth/PasswordResetLinkController.php
 M app/Http/Controllers/Auth/RegisteredUserController.php
 M app/Http/Controllers/ProfileController.php
 M app/Http/Controllers/ReporteAcademicoController.php
 M app/Http/Requests/Admin/StoreUsuarioRequest.php
 M app/Http/Requests/Admin/UpdateRolPermisosRequest.php
 M app/Http/Requests/Admin/UpdateUsuarioRequest.php
 M app/Http/Requests/Auth/LoginRequest.php
 M app/Http/Requests/Evaluaciones/StoreAreaConocimientoRequest.php
 M app/Http/Requests/Evaluaciones/StorePlantillaEvaluacionRequest.php
 M app/Http/Requests/Evaluaciones/StorePreguntaRequest.php
 M app/Http/Requests/Evaluaciones/StoreTemaRequest.php
 M app/Http/Requests/Evaluaciones/UpdateAreaConocimientoRequest.php
 M app/Http/Requests/Evaluaciones/UpdateTemaRequest.php
 M app/Http/Requests/Institucional/AsignacionTutorRequest.php
 M app/Http/Requests/Institucional/AsistenciaAcademicaRequest.php
 M app/Http/Requests/Institucional/AsistenciaGrupoRequest.php
 M app/Http/Requests/Institucional/CuotaAcademicaRequest.php
 M app/Http/Requests/Institucional/GrupoAcademicoRequest.php
 M app/Http/Requests/Institucional/HabilitacionAcademicaRequest.php
 M app/Http/Requests/Institucional/InscripcionAcademicaRequest.php
 M app/Http/Requests/Institucional/MatriculaAcademicaRequest.php
 M app/Http/Requests/Institucional/ProgramaAcademicoRequest.php
 M app/Http/Requests/Institucional/SimulacroProgramadoRequest.php
 M app/Http/Requests/Institucional/TutorAcademicoRequest.php
 M app/Http/Requests/Postulantes/StorePostulanteRequest.php
 M app/Http/Requests/Postulantes/UpdatePostulanteRequest.php
 M app/Http/Requests/ProfileUpdateRequest.php
 M app/Http/Requests/Resultados/EnviarRespuestasEvaluacionRequest.php
 M app/Http/Requests/Resultados/IniciarEvaluacionAplicadaRequest.php
 M composer.json
 M config/app.php
 M database/seeders/BaseLimpiaAvalanchaSeeder.php
 M database/seeders/PostulantesSeeder.php
 M resources/js/Components/Evaluaciones/AlternativasEditor.jsx
 M resources/js/Components/Evaluaciones/PlantillaEvaluacionForm.jsx
 M resources/js/Components/Evaluaciones/PreguntaForm.jsx
 M resources/js/Components/Postulantes/PostulanteForm.jsx
 M resources/js/Components/Sistema/UsuarioForm.jsx
 M resources/js/Pages/Auth/ConfirmPassword.jsx
 M resources/js/Pages/Auth/ForgotPassword.jsx
 M resources/js/Pages/Auth/Login.jsx
 M resources/js/Pages/Auth/Register.jsx
 M resources/js/Pages/Auth/ResetPassword.jsx
 M resources/js/Pages/Estudiante/Evaluaciones.jsx
 M resources/js/Pages/Evaluaciones/Areas/Create.jsx
 M resources/js/Pages/Evaluaciones/Areas/Index.jsx
 M resources/js/Pages/Evaluaciones/Temas/Create.jsx
 M resources/js/Pages/Evaluaciones/Temas/Index.jsx
 M resources/js/Pages/Institucional/AsignacionTutores/Index.jsx
 M resources/js/Pages/Institucional/Asistencia/Index.jsx
 M resources/js/Pages/Institucional/FichaAcademica/Show.jsx
 M resources/js/Pages/Institucional/Grupos/Index.jsx
 M resources/js/Pages/Institucional/HabilitacionAcademica/Index.jsx
 M resources/js/Pages/Institucional/Inscripciones/Index.jsx
 M resources/js/Pages/Institucional/MatriculasCuotas/Index.jsx
 M resources/js/Pages/Institucional/Programas/Index.jsx
 M resources/js/Pages/Institucional/Simulacros/Index.jsx
 M resources/js/Pages/Institucional/Tutores/Index.jsx
 M resources/js/Pages/Postulantes/Index.jsx
 M resources/js/Pages/Postulantes/Show.jsx
 M resources/js/Pages/Profile/Partials/DeleteUserForm.jsx
 M resources/js/Pages/Profile/Partials/UpdatePasswordForm.jsx
 M resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.jsx
 M resources/js/Pages/Sistema/RolesPermisos/Index.jsx
 M tests/Feature/Postulantes/PostulanteModuleTest.php
?? app/Domains/Postulantes/Support/
?? app/Http/Requests/Auth/PasswordEmailRequest.php
?? app/Http/Requests/Auth/RegisterRequest.php
?? app/Http/Requests/Auth/ResetPasswordRequest.php
?? app/Http/Requests/Auth/UpdatePasswordRequest.php
?? app/Http/Requests/NormalizedFormRequest.php
?? app/Rules/
?? app/Support/
?? database/migrations/2026_09_07_000000_add_basic_input_checks.php
?? database/migrations/2026_09_07_010000_add_fecha_nacimiento_to_postulantes.php
?? docs/audit-input-data.php
?? docs/auditoria-fecha-nacimiento-2026-09-07.md
?? docs/auditoria-validaciones-basicas-2026-09-07.md
?? docs/evidencia-fecha-nacimiento/
?? docs/fecha-nacimiento-verificacion-2026-09-07.json
?? docs/test-birth-date-ui.mjs
?? docs/test-birth-date-ui.php
?? docs/test-postgres-birth-date.php
?? docs/test-postgres-input-checks.php
?? docs/validaciones-datos-2026-09-07.json
?? lang/
?? resources/input-options.json
?? resources/js/lib/dateOnly.js
?? resources/js/lib/inputValidation.js
?? tests/Feature/Postulantes/PostulanteBirthDateTest.php
?? tests/Feature/Validation/
?? tests/input-validation.test.mjs
```
