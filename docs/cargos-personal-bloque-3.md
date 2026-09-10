# Bloque 3 — Cargos y Personal Institucional

## 1. Resumen

Implementado el catálogo Cargo y el perfil PersonalInstitucional, con gestión organizacional independiente de la seguridad de User. Incluye formularios, permisos efectivos, estados, vínculo explícito de identidad para TI, bitácora y pruebas PostgreSQL.

No se implementó Tutor.personal_id, multirrol, redistribución global de permisos, unidades organizacionales ni historial de nombramientos.

## 2. Estado inicial

- Rama develop, working tree limpio, HEAD 49173e1 (feat: implementar ciclo de vida seguro de cuentas).
- PostgreSQL disponible en 127.0.0.1:5432, base intelecta.
- 9 users activos/verificados, 4 roles, 72 permisos, 163 asignaciones rol-permiso.
- 3 sesiones, 0 tokens de recuperación, 72 postulantes, 4 tutores.
- No existían cargos, personal_institucional ni permisos cargos.* / personal.*.
- Inventario por PDO en transacción READ ONLY; huellas de las 39 tablas públicas para comparación posterior.
- No se ejecutaron seeders ni altas durante el inventario.

Respaldo anterior a las migraciones: [dump PostgreSQL](C:/laragon/tmp/intelecta-identity-backups/intelecta-before-block3-20260909_185856.dump).

Tamaño: 196703 bytes. SHA-256: 917ad9941be0e7f57dc645a90dbfa1972167d5870d755dd08f94c5c2f4ba64e0.
El catálogo se verificó con pg_restore --list; no se ensayó restauración completa.
Está fuera del repositorio y contiene datos sensibles: no publicarlo.

## 3. Modelo Cargo

Tabla cargos: id_cargo, nombre_cargo (160), descripcion nullable, estado, timestamps.
Sin role_id, permission_id, código institucional, unidades ni fechas de nombramiento.
Relación hasMany con PersonalInstitucional. Sin borrado físico HTTP.

El nombre conserva su presentación; InputNormalizer elimina extremos y colapsa espacios.
La unicidad se valida y respalda con índice UNIQUE sobre LOWER(nombre_cargo).
En PostgreSQL se comprobó también SECRETARÍA frente a Secretaría. Los acentos no se eliminan.

## 4. Modelo PersonalInstitucional

Tabla personal_institucional: id_personal, user_id nullable, cargo_id nullable, nombres (120), apellidos (120), ci nullable (30), celular nullable (16), correo_contacto nullable (254), estado y timestamps.

User es opcional, al igual que Cargo y los datos de contacto.
user_id y estado quedan fuera de asignación masiva ordinaria; la edición usa listas explícitas.
No se incorporan credenciales, roles, datos profesionales de Tutor ni campos adicionales hipotéticos.

## 5. Migraciones

- 2026_09_10_000000_create_cargos_and_personal_institucional.php: estructura nueva, índices, FK y CHECKs.
- 2026_09_10_000100_add_organizational_permissions.php: ocho permisos nuevos y asignaciones aditivas a los roles Administrador y Super Administrador que ya existan; invalida la caché de permisos.

Se revisó el SQL con --pretend y se aplicaron únicamente estas dos migraciones a intelecta.
No se ejecutó migrate:fresh ni seed en esa base.

La migración de permisos es despliegue controlado de metadatos de autorización, no un seeder de cuentas. No modifica los 72 permisos anteriores ni sus 163 asignaciones.

El rollback se probó solo en la base aislada, con tablas nuevas vacías. Su down elimina las tablas/permisos del módulo: no debe utilizarse sobre datos reales ya incorporados sin respaldo y decisión explícita.

## 6. Estados

| Entidad | Estados | Estado inicial ordinario |
|---|---|---|
| Cargo | activo, inactivo | activo |
| PersonalInstitucional | pendiente, activo, inactivo | pendiente |
| User (Bloque 2) | pendiente, activa, bloqueada | sin cambios |

EstadoCargo y EstadoPersonal son enums PHP; las migraciones congelan los valores persistidos y PostgreSQL los protege.
Cambiar estado exige un endpoint y permiso específicos. El CRUD general no admite estado manipulable.

Personal activo significa operativo institucionalmente, no acceso concedido.
Personal inactivo no bloquea User. Bloquear/desbloquear User tampoco cambia Personal.

## 7. Relaciones User–Personal

0..1 ↔ 0..1 mediante personal_institucional.user_id, nullable + UNIQUE + FK users.id ON DELETE RESTRICT.
User::personalInstitucional y PersonalInstitucional::user consultan exclusivamente esa FK.

VincularUsuarioPersonalAction recibe IDs explícitos y motivo; solo SA activo/TI puede confirmar.
Usa transacción, el mutex de cuentas del Bloque 2, bloqueo de filas y tratamiento de conflictos UNIQUE.
No exige un estado o rol específico al User objetivo. Sí exige que ambos extremos estén libres.
Repetir, reemplazar o reutilizar un vínculo se rechaza; no se ofrece desvinculación en esta fase.

El formulario común nunca cambia user_id. No se compara correo, nombre o CI para establecer identidad.
No se obliga a TI/SA a tener Personal.

## 8. Relaciones Cargo–Personal

cargo_id nullable → cargos.id_cargo con ON DELETE RESTRICT.
Un único cargo principal actual; sin tabla de nombramientos.

Una asignación NUEVA exige cargo activo. La comprobación bloquea la fila del cargo dentro de la transacción, serializándose con la inactivación.
Si se inactiva primero, se rechaza la asignación; si se asigna primero, la inactivación posterior conserva esa relación existente.
Editar una persona con cargo inactivo puede conservarlo. También puede retirarlo o seleccionar otro cargo activo.

Cambiar/inactivar Cargo nunca cambia cuentas, roles o permisos.

## 9. Validaciones

Se reutilizan NormalizedFormRequest, InputNormalizer, InputRules e InputFormat.

- Cargo: requerido, 2–160 caracteres, Unicode, al menos una letra y unicidad normalizada.
- Se añadió el formato compartido denomination; no diccionario ni plausibilidad semántica.
- Nombres/apellidos: exactamente InputRules::person, 2–120.
- CI opcional: exactamente InputRules::document y UNIQUE si informado; múltiples NULL permitidos. No se introduce normalización adicional de CI.
- Celular: InputRules::phone y normalización compartida.
- Contacto: InputRules::email y minúsculas/trim; no es correo de login ni llave de identidad.
- IDs: enteros existentes; se rechazan booleanos y formas incorrectas antes de consultar BD.
- Estado, user_id y atributos de seguridad manipulados no se incorporan al CRUD común.
- Límites/patrones de interfaz añadidos al módulo compartido inputValidation.js.

Las reglas de formato siguen siendo responsabilidad de aplicación; las FK, UNIQUE y estados tienen respaldo en BD. Un SQL privilegiado que omita la normalización no sustituye el flujo validado.

## 10. CRUD Cargos

Listado con búsqueda, estado, paginación y cantidad de personas asignadas.
Alta y edición mediante ModalInstitucional.
Activar/inactivar mediante ConfirmModal; no hay DELETE.
Cargo inactivo se conserva junto a sus relaciones y no concede acceso.

Rutas: admin.institucional.cargos.index/store/update/estado.

## 11. CRUD Personal

Listado con nombre, CI, contacto, cargo, estado y referencia controlada a User.
Alta/edición de datos organizacionales, sin credenciales ni gestión de seguridad.
Cambio de estado explícito y confirmación de independencia frente a User.
El listado ya muestra los datos necesarios; no se añadió una página de detalle redundante.

Rutas: admin.institucional.personal.index/store/update/estado.
Identidad separada: personal.usuarios-elegibles y personal.vincular.

## 12. Autorización

| Operación | Administrador | SA activo | Docente / Estudiante |
|---|---|---|---|
| Gestionar Cargos/Personal | Según permisos nuevos | Gate actual + cuenta activa | No |
| Confirmar vínculo User–Personal | No | Sí, comprobación TI explícita | No |
| Gestionar seguridad User | No | Reglas Bloque 2 | No |
| Borrado físico HTTP | No | No | No |

EnsureOrganizationAccess y User::canManageOrganization exigen rol de gestión, cuenta activa y permiso efectivo.
Incluso otorgar permisos directos a Docente/Estudiante no abre el CRUD global.
El middleware de estado del Bloque 2 sigue prevaleciendo.
Gate::before permanece sin cambios; la vinculación añade comprobación explícita de SA activo.

## 13. Permisos nuevos

- cargos.ver
- cargos.crear
- cargos.editar
- cargos.cambiar_estado
- personal.ver
- personal.crear
- personal.editar
- personal.cambiar_estado

Ocho en total, asignados exclusivamente a Administrador y SA en la migración/fixture correspondiente.
No se añaden permisos *.eliminar ni se redistribuyen los anteriores.
Editar no equivale a cambiar estado; las pruebas comprueban esa separación.

## 14. Frontend

Dos módulos React/Inertia, reutilizando AdminLayout, InstitutionalUi, ModalInstitucional, ConfirmModal y Pagination.
Menú gobernado por permisos efectivos calculados en backend, incluyendo restricción por rol y estado.

Los selects de Cargo ofrecen activos más el cargo inactivo ya asignado al registro editado.
La vinculación TI tiene búsqueda manual paginada de cuentas sin Personal, muestra ID/nombre/email/estado y nunca preselecciona ni recomienda por coincidencia de contacto.

Prueba visual en servidor temporal 127.0.0.1:8853 conectado EXCLUSIVAMENTE a personal_test:
- Administrador vio los módulos, sin botón de vinculación.
- Se creó un Personal sintético con cargo Docente, pendiente y sin User.
- Se comprobó visualmente el formulario y la separación contacto/acceso.
- El navegador rechazó nombre de Cargo exclusivamente numérico.
- SA vio el diálogo separado y una búsqueda devolvió una cuenta elegible sin seleccionarla automáticamente.
- No se ejecutó vinculación adicional desde el navegador; la confirmación y conflictos se probaron por HTTP/Action y concurrencia real.

Se cerraron las pestañas de prueba y el servidor temporal; no se utilizó la UI de la base actual.

## 15. Bitácora

Operaciones: crear_cargo, editar_cargo, cambiar_estado_cargo, crear_personal, editar_personal, cambiar_cargo_personal, cambiar_estado_personal y vincular_usuario_personal.

Incluye actor, entidad/ID y fecha del registro. Cambio de cargo registra IDs anterior/nuevo; cambio de estado, valores anterior/nuevo; vínculo, IDs y motivo.
La edición de Personal registra nombres de campos modificados, no una segunda copia de CI/contactos.
No se registran credenciales, tokens ni hashes. Se conserva el comportamiento previo de mejor esfuerzo de BitacoraService.

## 16. Datos actuales

Resultado en intelecta:

| Tabla / conjunto | Antes | Después |
|---|---:|---:|
| users | 9 | 9 |
| roles | 4 | 4 |
| permissions | 72 | 80 |
| role_has_permissions | 163 | 179 |
| sessions | 3 | 3 |
| password_reset_tokens | 0 | 0 |
| postulantes | 72 | 72 |
| tutores_academicos | 4 | 4 |
| cargos | no existía | 0 |
| personal_institucional | no existía | 0 |
| migrations | 34 | 36 |

Las 39 tablas previas solo cambiaron en migrations, permissions, role_has_permissions y cache (invalidación de una entrada).
Las huellas de users, roles asignados a usuarios, sesiones, expedientes y tutores permanecen idénticas.
Comparando únicamente los permisos/asignaciones anteriores, sus huellas también permanecen idénticas.

No hubo backfill ni asociación automática.
Los postulantes 1/2/3 siguen con user_id NULL; los candidatos User 7/8/9 del Bloque 1 permanecen intactos.
User 2 puede revisarse como candidato organizacional humano; su rol no confirma cargo ni autoriza crear su perfil actual.

## 17. Tutores candidatos Bloque 4

| Tutor actual | User explícito | Estado Tutor | Acción en este bloque |
|---|---:|---|---|
| 1 | 3 | activo | Inventariado, sin migración |
| 2 | 4 | activo | Inventariado, sin migración |
| 3 | 5 | activo | Inventariado, sin migración |
| 4 | 6 | activo | Inventariado, sin migración |

Los cuatro son candidatos para una migración deliberada del Bloque 4, no vínculos Personal confirmados.
No se copiaron nombres, CI, contactos ni atributos profesionales desde Tutor a Personal en la base actual.

## 18. Seeders

| Seeder | Cambio |
|---|---|
| CargosSeeder (nuevo) | Ocho denominaciones explícitas, objetos devueltos por claves del escenario. |
| PersonalInstitucionalSeeder (nuevo) | Cinco perfiles demo a partir de objetos User/Cargo recibidos; rechaza ejecución sin esas referencias. |
| RolesAndUsersSeeder | Ocho permisos adicionales; expone objetos de cuentas del fixture bajo claves explícitas. |
| BaseLimpiaAvalanchaSeeder | Orquesta los nuevos seeders y pasa las referencias; también usa esas referencias para el vínculo Tutor–User del fixture. |
| DatabaseSeeder | Sin cambio; sigue invocando el orquestador. |

Catálogo inicial: Rector, Vicerrector, Director de Carrera, Coordinador Académico, Docente, Contador, Responsable de Recursos Humanos y Secretaría.
Son exactamente las ocho necesidades propuestas en el encargo; no derivan de roles.

Escenario demo explícito: Marco Antonio → Coordinador Académico; Rodrigo, Carla, Luis Fernando y Patricia → Docente.
SA queda sin Personal; estudiantes no se convierten en Personal.
CI, celular y correo de contacto de estos perfiles quedan NULL: no se duplican los contactos profesionales de Tutor.

La coincidencia del nombre de un cargo y un rol no es una regla del sistema.
Los perfiles docentes demo son una transición de fixture: Tutor mantiene sus campos/especialidad/formación/experiencia hasta el Bloque 4; no hay sincronización runtime.

Idempotencia del nuevo seeder por user_id ya proporcionado, no email/nombre/CI. Se probó ejecución directa repetida.
Los seeders continúan siendo herramientas de instalación demo, no de migración de personas reales.

## 19. Factories

CargoFactory: active() e inactive(); predeterminado activo.
PersonalInstitucionalFactory: pending(), active(), inactive(); predeterminado pendiente.
PersonalFactory no crea User ni Cargo implícitamente; todos sus FK/contactos opcionales comienzan NULL.

## 20. PostgreSQL

Constraints reales inspeccionados: FK User y Cargo con RESTRICT, UNIQUE user_id, UNIQUE ci y CHECKs de ambos estados.
Índice funcional de nombre de Cargo probado, incluyendo mayúsculas Unicode.
Se verificaron múltiples NULL, rechazo de referencias inválidas, duplicados, estados inválidos y borrado técnico de entidades referenciadas.

Tres escenarios independientes de procesos PHP:
1. Un User compitiendo por dos Personal: una aceptación y un rechazo.
2. Dos Users compitiendo por un Personal: una aceptación y un rechazo.
3. Asignación esperando el bloqueo de un Cargo que se inactiva: rechazo tras commit de la inactivación.

Para el tercero se observó wait_event_type=Lock en pg_stat_activity, refrescando la instantánea estadística.
Las pruebas solo manipulan bases aisladas explícitas. La restricción de nuevas asignaciones inactivas está en el servicio transaccional, no en un trigger de BD.

## 21. Instalación limpia

BD principal de pruebas: intelecta_personal_test_20260909_185856.
BD separada para regresión concurrente del Bloque 2: intelecta_accounts_test_20260909_185856.

El script nuevo exige APP_ENV=testing, driver pgsql, ausencia de config cache, nombre explícito con prefijo personal_test y coincidencia con current_database().
Ejecuta migrate:fresh --seed solo después de estas comprobaciones, y verifica rollback aislado.
Resultado limpio: 8 cargos, 5 Personal vinculados por IDs, 9 cuentas activas/verificadas, 72 postulantes (3 FK explícitas/69 NULL) y 4 tutores con esquema anterior.
Notification::assertNothingSent confirma ausencia de notificaciones.

Nunca se ejecutó fresh/seed sobre intelecta.
Los datos sintéticos de concurrencia/UI se retiran mediante reinstalación de esas bases desechables al cerrar la verificación.

## 22. Regresión Bloque 1

Pasó la suite completa y se ejecutaron expresamente StableIdentityTest y StableStudentAccessTest en PostgreSQL.
Se preservaron identidad por FK, ausencia de fallback, rechazo sin perfil/rol y separación de correos.
El script PostgreSQL de identidad pasó 15/15.
No se modificó el esquema Tutor ni la identidad de los candidatos actuales.

## 23. Regresión Bloque 2

Pasaron AccountLifecycleTest, EmailVerificationTest, AuthenticationTest y LoginEmailGovernanceTest en PostgreSQL, además de la suite completa.
El script de ciclo de vida pasó 12/12.
La instalación previa pasó 12/12 y la protección del último SA pasó sus cuatro escenarios concurrentes.
Estado, verificación, revocación, correo protegido, registro público cerrado y autoeliminación cerrada se conservan.

## 24. Tests

| Verificación | Resultado |
|---|---|
| PHPUnit completo | 294/294 |
| Assertions completas | 1793 |
| Suite funcional PostgreSQL (Bloques 1/2/3) | 93/93, 720 assertions |
| Node | 8/8 |
| PostgreSQL validaciones básicas | 83/83 |
| PostgreSQL nacimiento/Bolivia | 11/11 |
| PostgreSQL identidad estudiantil | 15/15 |
| PostgreSQL ciclo de cuentas | 12/12 |
| PostgreSQL instalación/constraints/rollback Bloque 3 | 41/41 |
| PostgreSQL concurrencia Bloque 3 | 3/3 |
| PostgreSQL instalación de cuentas | 12/12 |
| PostgreSQL último SA concurrente | 4/4 |

Los scripts PostgreSQL suman 181 comprobaciones/escenarios. No son el mismo conjunto que los 93 tests funcionales.

Fallos intermedios, corregidos y no ocultados:
- Dos comparaciones de snapshots dependían del orden de claves; ahora ambos extremos se leen de BD.
- Un test invocaba CuentaService con User en lugar de ID; se corrigió la llamada.
- La invocación directa del seeder perdía user_id por la protección de asignación masiva. Se limitó unguarded al bloque explícito del fixture y se comprobó idempotencia.
- El observador de concurrencia conservaba estadísticas PostgreSQL en su transacción y no detectaba la espera. Se añadió pg_stat_clear_snapshot; los tres escenarios pasaron.
- Los scripts nuevos finalizan con código no cero ante excepciones para no confundir errores con éxito de CLI.

No se eliminó ningún test existente.

Comandos principales ejecutados (con PHP/Node de Laragon):
- php vendor/phpunit/phpunit/phpunit
- node --test tests/input-validation.test.mjs
- php docs/test-postgres-input-checks.php
- php docs/test-postgres-birth-date.php <ruta-node>
- php docs/test-postgres-student-identity.php
- php docs/test-postgres-account-lifecycle.php
- php docs/test-postgres-personal-installation.php <BD-personal-test-explícita>
- php docs/test-postgres-personal-concurrency.php <BD-personal-test-explícita>
- php docs/test-postgres-account-installation.php <BD-accounts-test-explícita>
- php docs/test-postgres-last-sa-concurrency.php <BD-accounts-test-explícita>

Las suites PostgreSQL usan variables de entorno del proceso (APP_ENV=testing, DB_CONNECTION=pgsql, DB_DATABASE aislada, DB_URL vacío, CACHE_STORE=array, MAIL_MAILER=array y SESSION_DRIVER=array); .env no fue modificado.

## 25. Build

Vite completado: 3449 módulos, última compilación correcta.
La primera emitió un aviso no bloqueante de tiempo del plugin Laravel; la última terminó sin ese aviso.
Sin dependencias nuevas.

## 26. Pint

Pint aplicado a los archivos afectados y comprobación --dirty --test correcta.

## 27. git diff --check

Sin errores. También se comprobó ausencia de staging.

## 28. Archivos modificados

40 archivos: 11 existentes modificados y 29 nuevos, incluyendo este informe.

- [app/Domains/Institucional/Actions/VincularUsuarioPersonalAction.php](C:/laragon/www/intelecta/app/Domains/Institucional/Actions/VincularUsuarioPersonalAction.php)
- [app/Domains/Institucional/Enums/EstadoCargo.php](C:/laragon/www/intelecta/app/Domains/Institucional/Enums/EstadoCargo.php)
- [app/Domains/Institucional/Enums/EstadoPersonal.php](C:/laragon/www/intelecta/app/Domains/Institucional/Enums/EstadoPersonal.php)
- [app/Domains/Institucional/Models/Cargo.php](C:/laragon/www/intelecta/app/Domains/Institucional/Models/Cargo.php)
- [app/Domains/Institucional/Models/PersonalInstitucional.php](C:/laragon/www/intelecta/app/Domains/Institucional/Models/PersonalInstitucional.php)
- [app/Domains/Institucional/Services/OrganizacionService.php](C:/laragon/www/intelecta/app/Domains/Institucional/Services/OrganizacionService.php)
- [app/Domains/Institucional/Support/PermisosOrganizacion.php](C:/laragon/www/intelecta/app/Domains/Institucional/Support/PermisosOrganizacion.php)
- [app/Http/Controllers/Institucional/CargoController.php](C:/laragon/www/intelecta/app/Http/Controllers/Institucional/CargoController.php)
- [app/Http/Controllers/Institucional/PersonalInstitucionalController.php](C:/laragon/www/intelecta/app/Http/Controllers/Institucional/PersonalInstitucionalController.php)
- [app/Http/Middleware/EnsureOrganizationAccess.php](C:/laragon/www/intelecta/app/Http/Middleware/EnsureOrganizationAccess.php)
- [app/Http/Middleware/HandleInertiaRequests.php](C:/laragon/www/intelecta/app/Http/Middleware/HandleInertiaRequests.php)
- [app/Http/Requests/Institucional/CargoRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/CargoRequest.php)
- [app/Http/Requests/Institucional/EstadoOrganizacionalRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/EstadoOrganizacionalRequest.php)
- [app/Http/Requests/Institucional/PersonalInstitucionalRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/PersonalInstitucionalRequest.php)
- [app/Http/Requests/Institucional/VincularPersonalRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Institucional/VincularPersonalRequest.php)
- [app/Models/User.php](C:/laragon/www/intelecta/app/Models/User.php)
- [app/Rules/InputFormat.php](C:/laragon/www/intelecta/app/Rules/InputFormat.php)
- [app/Support/Validation/InputRules.php](C:/laragon/www/intelecta/app/Support/Validation/InputRules.php)
- [bootstrap/app.php](C:/laragon/www/intelecta/bootstrap/app.php)
- [database/factories/CargoFactory.php](C:/laragon/www/intelecta/database/factories/CargoFactory.php)
- [database/factories/PersonalInstitucionalFactory.php](C:/laragon/www/intelecta/database/factories/PersonalInstitucionalFactory.php)
- [database/migrations/2026_09_10_000000_create_cargos_and_personal_institucional.php](C:/laragon/www/intelecta/database/migrations/2026_09_10_000000_create_cargos_and_personal_institucional.php)
- [database/migrations/2026_09_10_000100_add_organizational_permissions.php](C:/laragon/www/intelecta/database/migrations/2026_09_10_000100_add_organizational_permissions.php)
- [database/seeders/BaseLimpiaAvalanchaSeeder.php](C:/laragon/www/intelecta/database/seeders/BaseLimpiaAvalanchaSeeder.php)
- [database/seeders/CargosSeeder.php](C:/laragon/www/intelecta/database/seeders/CargosSeeder.php)
- [database/seeders/PersonalInstitucionalSeeder.php](C:/laragon/www/intelecta/database/seeders/PersonalInstitucionalSeeder.php)
- [database/seeders/RolesAndUsersSeeder.php](C:/laragon/www/intelecta/database/seeders/RolesAndUsersSeeder.php)
- [docs/cargos-personal-bloque-3.md](C:/laragon/www/intelecta/docs/cargos-personal-bloque-3.md)
- [docs/test-postgres-personal-concurrency.php](C:/laragon/www/intelecta/docs/test-postgres-personal-concurrency.php)
- [docs/test-postgres-personal-installation.php](C:/laragon/www/intelecta/docs/test-postgres-personal-installation.php)
- [resources/js/Components/AppSidebar.jsx](C:/laragon/www/intelecta/resources/js/Components/AppSidebar.jsx)
- [resources/js/Components/Institucional/OrganizationFilters.jsx](C:/laragon/www/intelecta/resources/js/Components/Institucional/OrganizationFilters.jsx)
- [resources/js/Components/Institucional/VincularPersonalModal.jsx](C:/laragon/www/intelecta/resources/js/Components/Institucional/VincularPersonalModal.jsx)
- [resources/js/Pages/Institucional/Cargos/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/Cargos/Index.jsx)
- [resources/js/Pages/Institucional/Personal/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Institucional/Personal/Index.jsx)
- [resources/js/lib/inputValidation.js](C:/laragon/www/intelecta/resources/js/lib/inputValidation.js)
- [routes/web.php](C:/laragon/www/intelecta/routes/web.php)
- [tests/Feature/Institucional/OrganizacionSeederTest.php](C:/laragon/www/intelecta/tests/Feature/Institucional/OrganizacionSeederTest.php)
- [tests/Feature/Institucional/OrganizacionTest.php](C:/laragon/www/intelecta/tests/Feature/Institucional/OrganizacionTest.php)
- [tests/input-validation.test.mjs](C:/laragon/www/intelecta/tests/input-validation.test.mjs)

## 29. Riesgos / pendientes

- La base actual empieza con catálogo y Personal vacíos por decisión explícita: Administrador debe registrar información acreditada; no ejecutar seeders para poblarla.
- Confirmar vínculos de identidad requiere revisión humana de TI. No hay reasignación/desvinculación; una corrección futura necesita procedimiento específico.
- La coherencia de nuevas asignaciones frente a cargos inactivos depende de usar OrganizacionService; SQL/seeders privilegiados no sustituyen ese contrato.
- La unicidad de nombres usa LOWER de la BD. PostgreSQL es la referencia para Unicode; SQLite tiene diferencias de colación.
- Bitácora es de mejor esfuerzo, no un historial inmutable de nombramientos ni una garantía de captura infalible.
- Los fixtures docentes duplican temporalmente datos de nombre entre Personal y Tutor, sin sincronización; consolidación pendiente del Bloque 4.
- Pendientes fuera de alcance: Tutor.personal_id y migración profesional, multirrol, rename Administrador, redistribución global, Policies por ámbito, unidades, historial de cargos y módulos administrativos futuros.
- El respaldo debe probarse mediante restauración en otra base antes de depender de él para recuperación; no sobrescribir la base actual automáticamente.

## 30. Estado Git final

Rama develop; HEAD 49173e1 sin cambios.
NO git add. NO commit. NO push.
Todos los cambios permanecen sin staging para revisión.

