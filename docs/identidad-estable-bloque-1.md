# Bloque 1 — Identidad estable User ↔ Postulante

## Resultado y alcance

Implementado sobre `develop`, partiendo de `ae43287ceabcb84dcdbf22dc2e39263948c8ae8a`, sin commit ni push.
Se conserva `users.email` para login/recuperación y `postulantes.email_post` como contacto académico.
El portal exige autenticación, rol **Estudiante** y una FK explícita hacia un expediente no archivado.
No hay búsqueda alternativa por email, nombre o CI.

**Efecto operativo deliberado:** en la base actual los tres estudiantes demo todavía no pueden entrar al portal académico, porque no se certificaron sus vínculos. Esto no impide administrar sus expedientes. No se ejecutó ningún comando de vinculación en la base actual.

## Esquema y resolución

Migración aditiva: `2026_09_08_000000_add_user_id_to_postulantes.php`.

- `user_id BIGINT NULL`, sin default.
- `UNIQUE (user_id)`, incluyendo expedientes archivados.
- FK a `users.id ON DELETE RESTRICT`; sin cascade ni backfill.
- `User::postulante()` (HasOne) y `Postulante::user()` (BelongsTo).
- Cardinalidad 0..1 en ambos extremos. Múltiples expedientes sin cuenta siguen siendo válidos.
- `user_id` no es fillable ni forma parte del DTO/validación del CRUD académico.
- El middleware `EnsureStudentAccess` consulta la relación por FK una vez y la entrega a los controllers.
- Se ejecuta después de autenticar y antes del binding de recursos académicos, evitando consultar su existencia para cuentas no habilitadas.
- Respuesta 403 en español, tanto JSON como la página Inertia Forbidden.
- GET de evaluaciones, iniciar/enviar, mi ficha y ranking utilizan la misma comprobación.
- El envío de una evaluación ajena se resuelve dentro de la relación del postulante y devuelve 404.
- Se retiró la interpretación de `Postulante` como rol en Requests, dashboard y middleware administrativo. No se alteraron los cuatro roles reales ni sus permisos.
- El estado activo/inactivo académico, la habilitación, la expiración y los ámbitos por grupo conservan sus reglas anteriores: no son parte de este bloque.

## Correos y baja de cuenta

- Perfil ordinario: solo actualiza `name`; enviar un email no vacío provoca validación 422. Incluso SA debe usar el flujo de administración para cambiar el correo.
- React muestra el correo de acceso de solo lectura y no lo envía desde el perfil.
- CRUD de usuarios: el Administrador puede mantener los demás cambios ya autorizados, pero no cambiar un email existente.
- La autorización transitoria se centraliza en `User::canChangeLoginEmail()`: exige Super Administrador, no un permiso compartido ni un cargo.
- El controller comprueba el cambio bajo bloqueo del usuario. El formulario administrativo refleja la capacidad y confirma cambios sensibles.
- SA conserva las validaciones y normalización existentes, unicidad, y pone `email_verified_at = NULL` al cambiarlo.
- Se eliminan tokens de recuperación asociados al email anterior y al nuevo; se rota remember_token y se revocan sesiones persistidas cuando el driver es database.
- Las sesiones en otros drivers, sesiones concurrentes en vuelo y un flujo completo de revocación/activación quedan para Bloque 2; esto no equivale a una garantía general de revocación instantánea.
- La baja ordinaria bloquea cuentas vinculadas, también con expediente archivado. Usa transacción/bloqueo y no cierra la sesión antes de comprobar la eliminación.
- La FK protege también ante borrados directos; el controller convierte conflictos referenciales en una respuesta controlada.
- No se rediseñó la baja del último Super Administrador ni se introdujo borrado en cascada.

## Vinculación explícita, exclusivamente tras revisión humana

Se implementaron `VincularUsuarioPostulanteAction` y `intelecta:vincular-postulante`.

Ejemplo **con marcadores, no ejecutar hasta sustituirlos tras revisión**:

```text
php artisan intelecta:vincular-postulante USER_ID_CONFIRMADO ID_POST_CONFIRMADO --actor=ID_SA_RESPONSABLE --motivo="Referencia de revisión documental aprobada"
```

El comando exige confirmación interactiva; por defecto cancela y no ofrece opción de fuerza.
No busca candidatos ni certifica identidades civiles.

1. Elegir IDs y confirmar evidencia institucional independiente del email.
2. Verificar entorno/base indicada en la confirmación.
3. Identificar al SA responsable y registrar la referencia de revisión, sin PII innecesaria.
4. La Action exige actor SA y cuenta objetivo con rol Estudiante.
5. Bloquea User y Postulante en orden consistente dentro de una transacción.
6. Rechaza IDs inexistentes, expedientes archivados y cualquier vínculo previo en cualquiera de los extremos.
7. La unicidad de PostgreSQL cubre conflictos concurrentes; la Action convierte la excepción UNIQUE en un error de validación.
8. No permite reemplazar, transferir o desvincular; esas operaciones requieren otro procedimiento.
9. Registra IDs anterior/nuevo, responsable y motivo en la bitácora existente.

**Límite de confianza de CLI:** `--actor` atribuye responsabilidad, no autentica por sí mismo a una persona. Solo operadores TI autorizados con acceso al servidor deben ejecutar Artisan. Una futura interfaz deberá autenticar y autorizar al actor real.
La bitácora conserva su comportamiento existente de mejor esfuerzo; no se convirtió en un registro infalible ni se cambió su política de fallos.

## Inventario real y candidatos

La primera conexión falló. Después de que el usuario activó Laragon se verificó:

| Dato | Resultado |
|---|---:|
| APP_ENV | local |
| Host/puerto | 127.0.0.1:5432 |
| Base actual | intelecta |
| Usuarios | 9 |
| Postulantes | 72 |
| Postulantes archivados | 0 |
| Tutores / archivados | 4 / 0 |
| Roles / permisos | 4 / 72 |
| Grupos de emails duplicados normalizados en usuarios / postulantes | 0 / 0 |
| Postulantes con fecha de nacimiento conocida | 0 |
| Vínculos User–Postulante tras migración | 0 |
| Expedientes sin cuenta vinculada | 72 |

| Clasificación | User ID candidato | Postulante ID | FK después de migrar |
|---|---:|---:|---|
| CANDIDATO 1 | 7 | 1 | NULL |
| CANDIDATO 2 | 8 | 2 | NULL |
| CANDIDATO 3 | 9 | 3 | NULL |

Las coincidencias de nombres y el escenario del seeder aportan evidencia de intención **de los fixtures demo**, suficiente para declarar una instalación nueva por objetos. No se trataron como autorización para modificar la base existente.
Los otros 69 expedientes no tienen candidatos por correo identificados. Todos siguen sin identidad digital atribuida.

## Respaldo, aplicación y recuperación

Antes de migrar se obtuvo inventario y huellas agregadas de las 39 tablas públicas.
El respaldo se creó fuera del repositorio:

- Ruta: `C:/laragon/tmp/intelecta-identity-backups/intelecta-before-block1-20260909-032954.dump`
- Formato PostgreSQL custom, 195265 bytes.
- SHA-256: `18feabcbc0e771f03cad10add3438100caba3038143cb87d6e361b351ec597c1`.
- Catálogo del archivo verificado con `pg_restore --list`.
- Contiene datos sensibles: no subir a Git ni compartirlo públicamente.
- No se ensayó una restauración completa del dump; la verificación del catálogo no la sustituye.

Se revisó primero el SQL con `migrate --pretend --path=...`.
Después se ejecutó únicamente:

```text
php artisan migrate --path=database/migrations/2026_09_08_000000_add_user_id_to_postulantes.php --force
```

Después de aplicar: misma cantidad y huellas de todos los datos originales, exceptuando la nueva fila del historial `migrations` (32 → 33). Para postulantes se compararon exactamente las columnas originales y se verificó por separado que las 72 nuevas FK son NULL.
No se alteraron credenciales, roles, permisos, contactos, fechas de nacimiento, edades ni registros académicos.

Recuperación recomendada ante un problema: restaurar el dump en **otra base nueva y verificada**, comprobar integridad y decidir la recuperación controlada. No sobrescribir automáticamente la base actual.
El `down()` retira FK, UNIQUE y columna, por lo que perdería cualquier vínculo posteriormente certificado. No ejecutar rollback sin exportar esos vínculos y analizar el código desplegado; tampoco restablecer autorización por email como fallback.
No se ejecutó rollback ni restauración sobre la base actual.

## Seeders y factory

| Archivo | Cambio / revisión |
|---|---|
| RolesAndUsersSeeder | Devuelve objetos User de los tres fixtures Estudiante, indexados explícitamente como 0, 1 y 2; mantiene su generación de roles/permisos. |
| BaseLimpiaAvalanchaSeeder | Recibe esos objetos y asocia cada expediente demo nuevo mediante user()->associate(User). No busca User por email_post. No vincula expedientes preexistentes al reejecutarse. |
| PostulantesSeeder | Fixtures alternativos sin cuenta; documenta la ausencia intencional de identidad digital y preserva vínculos existentes. |
| DatabaseSeeder | Inspeccionado, sin cambio necesario: sigue llamando al seeder principal. |
| UserFactory | Inspeccionado, sin cambio necesario: no inventa perfil ni rol. |
| PostulanteFactory | Nueva factory, sin cuenta por defecto; estado withUser(User) explícito. |

Los seeders no son una migración: RolesAndUsersSeeder conserva updateOrCreate/syncRoles y puede sobrescribir credenciales/roles. **No ejecutarlo en una base existente como método para vincular personas.**
No se crearon Personal, Cargos ni seeders asociados.

## Búsqueda global y clasificación

Se inspeccionaron `email_post`, `user.email`, `->email`, búsquedas Eloquent, SQL y fixtures en app, routes, resources, database y tests; no se hicieron reemplazos ciegos.

| Tipo | Ubicaciones / tratamiento |
|---|---|
| A — identidad académica | EvaluacionAplicadaController y SeguimientoAcademicoController: retiradas ambas búsquedas por email; resolución por relación FK y middleware común. |
| B — contacto | Modelo/DTO/Requests de Postulante, PostulanteForm, Show, Index y ficha administrativa: se conserva email_post como contacto, incluyendo sus validaciones. |
| B — cuenta digital | Login, reset y confirmación de contraseña continúan usando users.email deliberadamente; nunca email_post. Perfil muestra acceso sin permitir edición. |
| C — filtro/reporte | PostulanteRepository y AcademicoRepository conservan filtros LIKE por contacto, sin atribuir titularidad. Serialización de ficha y vistas conserva el contacto de un expediente ya seleccionado/autorizado. |
| C — trazabilidad/presentación | BitacoraService, listeners y UI de usuarios/roles/tutores conservan correos para registrar o mostrar datos, no para resolver identidad estudiantil. |
| D — fixtures | Seeders principales usan objetos e índices explícitos; standalone no vincula. Tests previos de seguimiento/resultados usan FK y contactos distintos del login. |
| D — regresión de seguridad | Nuevos tests conservan intencionalmente correos coincidentes sin FK para demostrar rechazo. |

La búsqueda por correo de los tutores demo preexistente no se transformó en este bloque: corresponde a Tutor–User, no a identidad User–Postulante; esa migración está fuera de alcance.

## Pruebas y evidencia

- PHPUnit completo (SQLite en memoria): **229/229**, **1297 assertions**.
- Subconjunto de identidad/portal/email/seeders/resultados/postulantes sobre PostgreSQL aislado: **41/41**, **367 assertions**.
- Node: **7/7**.
- PostgreSQL — checks básicos existentes: **83/83**.
- PostgreSQL — fecha de nacimiento existente: **11/11**.
- PostgreSQL — nueva prueba de migración/FK/UNIQUE/NULL/delete/archivados: **15/15**.
- PostgreSQL — instalación limpia aislada: **10/10**, migrate:fresh --seed exit 0.
- Build Vite: correcto; aviso no bloqueante de tiempo del plugin Laravel.
- Pint sobre archivos afectados: correcto.
- git diff --check: correcto.

Se usa una base separada creada expresamente: `intelecta_identity_test_20260909_032955`.
La instalación limpia generó 9 users, 72 postulantes, 3 FK explícitas y 69 NULL; se verificó que cambiar contactos no altera las relaciones.
**Nunca se ejecutó migrate:fresh sobre intelecta.**
La suite PostgreSQL utiliza RefreshDatabase solo dentro de esa base de prueba.

Scripts:

- `docs/test-postgres-student-identity.php`: aplica la migración real sobre tablas temporales, prueba SQLSTATE 23505/23503 y hace rollback; no avanza secuencias reales.
- `docs/test-postgres-identity-installation.php`: permite fresh exclusivamente con APP_ENV=testing, driver pgsql, nombre explícito y prefijo de base de pruebas, sin config cache. Rechaza la base principal.
- Los scripts previos de CHECK y fecha de nacimiento se ejecutaron sin modificar sus pruebas.

Fallos intermedios, no ocultos:

- Se corrigieron el orden middleware/binding y un fixture con nombre Faker incompatible con las validaciones de persona.
- Una prueba inicial de reejecutar todo BaseLimpiaAvalanchaSeeder en SQLite detectó conflicto UNIQUE en asistencia por representación fecha/date-time. Es una limitación del flujo de reejecución de asistencia, no de la instalación limpia ni de la nueva FK. No se modificó esa lógica ajena al bloque. La no vinculación de datos legacy se prueba con un expediente preexistente en una instalación de prueba, sin depender de resembrar asistencia.
- Las ejecuciones finales indicadas arriba terminaron sin fallos.

## Pendientes explícitos

Bloque 2: invitaciones/activación, creación de cuentas no autoverificadas, recuperación y verificación institucional completa, revocación de sesiones en todos los drivers, trazabilidad reforzada, interfaz TI autenticada para vínculos/correos y procedimiento de corrección/desvinculación.
User todavía no implementa MustVerifyEmail: invalidar email_verified_at no introduce por sí solo un bloqueo global de login por verificación. Se mantiene esa compatibilidad y se documenta para Bloque 2.
Registro público sigue existiendo, pero no concede acceso académico sin rol y FK.
Pendiente previo: baja del último SA. No se refactorizaron todos los permisos ni el uso de un solo rol en el CRUD.
Personal, cargos, ámbitos docentes, finanzas, snapshots y demás módulos permanecen fuera de alcance.
Pint normalizó también la indentación preexistente de routes/web.php; los cambios funcionales de rutas se limitan al middleware de los cinco endpoints estudiantiles.

## Archivos nuevos o modificados

39 archivos en total: 27 modificados y 12 nuevos, sin staging.

### Backend — modelos, autorización y flujos

- [app/Models/User.php](C:/laragon/www/intelecta/app/Models/User.php)
- [app/Domains/Postulantes/Models/Postulante.php](C:/laragon/www/intelecta/app/Domains/Postulantes/Models/Postulante.php)
- [app/Domains/Postulantes/Actions/VincularUsuarioPostulanteAction.php](C:/laragon/www/intelecta/app/Domains/Postulantes/Actions/VincularUsuarioPostulanteAction.php)
- [app/Console/Commands/VincularPostulante.php](C:/laragon/www/intelecta/app/Console/Commands/VincularPostulante.php)
- [app/Http/Middleware/EnsureStudentAccess.php](C:/laragon/www/intelecta/app/Http/Middleware/EnsureStudentAccess.php)
- [app/Http/Middleware/EnsureAdministrativeAccess.php](C:/laragon/www/intelecta/app/Http/Middleware/EnsureAdministrativeAccess.php)
- [app/Http/Controllers/Admin/UsuarioController.php](C:/laragon/www/intelecta/app/Http/Controllers/Admin/UsuarioController.php)
- [app/Http/Controllers/DashboardController.php](C:/laragon/www/intelecta/app/Http/Controllers/DashboardController.php)
- [app/Http/Controllers/Estudiante/SeguimientoAcademicoController.php](C:/laragon/www/intelecta/app/Http/Controllers/Estudiante/SeguimientoAcademicoController.php)
- [app/Http/Controllers/EvaluacionAplicadaController.php](C:/laragon/www/intelecta/app/Http/Controllers/EvaluacionAplicadaController.php)
- [app/Http/Controllers/ProfileController.php](C:/laragon/www/intelecta/app/Http/Controllers/ProfileController.php)
- [app/Http/Requests/ProfileUpdateRequest.php](C:/laragon/www/intelecta/app/Http/Requests/ProfileUpdateRequest.php)
- [app/Http/Requests/Resultados/EnviarRespuestasEvaluacionRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Resultados/EnviarRespuestasEvaluacionRequest.php)
- [app/Http/Requests/Resultados/IniciarEvaluacionAplicadaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Resultados/IniciarEvaluacionAplicadaRequest.php)
- [bootstrap/app.php](C:/laragon/www/intelecta/bootstrap/app.php)
- [routes/web.php](C:/laragon/www/intelecta/routes/web.php)

### Esquema, seeders y factory

- [database/migrations/2026_09_08_000000_add_user_id_to_postulantes.php](C:/laragon/www/intelecta/database/migrations/2026_09_08_000000_add_user_id_to_postulantes.php)
- [database/seeders/RolesAndUsersSeeder.php](C:/laragon/www/intelecta/database/seeders/RolesAndUsersSeeder.php)
- [database/seeders/BaseLimpiaAvalanchaSeeder.php](C:/laragon/www/intelecta/database/seeders/BaseLimpiaAvalanchaSeeder.php)
- [database/seeders/PostulantesSeeder.php](C:/laragon/www/intelecta/database/seeders/PostulantesSeeder.php)
- [database/factories/PostulanteFactory.php](C:/laragon/www/intelecta/database/factories/PostulanteFactory.php)

### Frontend

- [resources/js/Components/Postulantes/PostulanteForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Postulantes/PostulanteForm.jsx)
- [resources/js/Components/Sistema/UsuarioForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Sistema/UsuarioForm.jsx)
- [resources/js/Pages/Errors/Forbidden.jsx](C:/laragon/www/intelecta/resources/js/Pages/Errors/Forbidden.jsx)
- [resources/js/Pages/Estudiante/Evaluaciones.jsx](C:/laragon/www/intelecta/resources/js/Pages/Estudiante/Evaluaciones.jsx)
- [resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.jsx](C:/laragon/www/intelecta/resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.jsx)

### Pruebas

- [tests/Feature/Auth/LoginEmailGovernanceTest.php](C:/laragon/www/intelecta/tests/Feature/Auth/LoginEmailGovernanceTest.php)
- [tests/Feature/Portal/StableStudentAccessTest.php](C:/laragon/www/intelecta/tests/Feature/Portal/StableStudentAccessTest.php)
- [tests/Feature/Postulantes/IdentitySeederTest.php](C:/laragon/www/intelecta/tests/Feature/Postulantes/IdentitySeederTest.php)
- [tests/Feature/Postulantes/StableIdentityTest.php](C:/laragon/www/intelecta/tests/Feature/Postulantes/StableIdentityTest.php)
- [tests/Feature/Portal/SeguimientoPostulanteTest.php](C:/laragon/www/intelecta/tests/Feature/Portal/SeguimientoPostulanteTest.php)
- [tests/Feature/Postulantes/PostulanteModuleTest.php](C:/laragon/www/intelecta/tests/Feature/Postulantes/PostulanteModuleTest.php)
- [tests/Feature/ProfileTest.php](C:/laragon/www/intelecta/tests/Feature/ProfileTest.php)
- [tests/Feature/Resultados/ResultadosTrazablesTest.php](C:/laragon/www/intelecta/tests/Feature/Resultados/ResultadosTrazablesTest.php)
- [tests/Feature/Validation/AuthInputValidationTest.php](C:/laragon/www/intelecta/tests/Feature/Validation/AuthInputValidationTest.php)
- [tests/Feature/Validation/RequestValidationTest.php](C:/laragon/www/intelecta/tests/Feature/Validation/RequestValidationTest.php)

### Documentación y verificaciones PostgreSQL

- [docs/identidad-estable-bloque-1.md](C:/laragon/www/intelecta/docs/identidad-estable-bloque-1.md)
- [docs/test-postgres-student-identity.php](C:/laragon/www/intelecta/docs/test-postgres-student-identity.php)
- [docs/test-postgres-identity-installation.php](C:/laragon/www/intelecta/docs/test-postgres-identity-installation.php)

