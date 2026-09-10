# 1. Resumen

Bloque 4 implementado y aplicado de forma no destructiva a `intelecta`. Tutor Académico es una extensión de Personal institucional; se retiraron las seis columnas de identidad duplicada. Los cuatro `id_tutor`, todas las asignaciones, asistencias y cuentas existentes se conservaron.

# 2. Estado inicial

Rama `develop`, working tree limpio, HEAD `d13206e feat: incorporar cargos y personal institucional`. PostgreSQL 17.2 disponible. Bloques 1–3 versionados antes de intervenir. Se revalidó la base inmediatamente antes de migrar: seguía coincidiendo con el respaldo, tabla por tabla.

# 3. Inventario Tutor previo

| Comprobación | Resultado |
|---|---|
| Tutores existentes / activos / archivados | 4 / 4 / 0 |
| Tutor sin User / User duplicado / CI duplicado | 0 / 0 / 0 |
| Nombres, apellidos, CI, celular o correo NULL | 0 |
| Tutor vinculado a cuenta sin rol Docente | 0 |
| Personal / cargos existentes | 0 / 0 |
| Asignaciones / asistencias | 16 / 544 |
| Usuarios / roles / permisos | 9 / 4 / 80 |

Solo dos FK apuntan a Tutor: `asignaciones_tutores.id_tutor` (CASCADE al borrar físicamente) y `asistencias_academicas.id_tutor` (SET NULL al borrar físicamente), ambas con ON UPDATE CASCADE. Se conservaron sus definiciones; el archivado utiliza soft delete.

Snapshot persistido: [evidencia-tutor-bloque-4.json](C:/laragon/www/intelecta/docs/evidencia-tutor-bloque-4.json). Contiene conteos, hashes e IDs, no valores de identidad/contacto ni credenciales.

# 4. Modelo final Tutor

`User 0..1 ↔ PersonalInstitucional 0..1 ↔ TutorAcademico`. Cada Tutor tiene exactamente un Personal; Personal puede carecer de cuenta y de Tutor.

Tutor conserva `id_tutor`, `personal_id`, especialidad, formación, experiencia, estado docente, observación, timestamps y `deleted_at`. `nombre_completo` es un atributo calculado desde Personal, no una columna ni una copia sincronizada.

# 5. Migraciones

- `2026_09_11_000000_add_personal_id_to_tutores_academicos.php`: FK aditiva nullable, UNIQUE y RESTRICT.
- Comando controlado `tutores:migrar-identidad`: migración de datos, separada de seeders.
- `2026_09_11_000100_finalize_tutor_personal_identity.php`: comprueba cobertura y compatibilidad antes de imponer NOT NULL y retirar columnas legacy.

Secuencia para otras instalaciones con datos, durante mantenimiento y con respaldo verificado:

```powershell
php artisan migrate --path=database/migrations/2026_09_11_000000_add_personal_id_to_tutores_academicos.php
php artisan tutores:migrar-identidad --dry-run
php artisan tutores:migrar-identidad --apply
php docs/audit-tutor-identity.php
php artisan migrate --path=database/migrations/2026_09_11_000100_finalize_tutor_personal_identity.php
```

El comando sin opciones solo simula; `--dry-run` prevalece sobre `--apply`. Si existe cualquier conflicto, toda la aplicación de datos se detiene sin escrituras parciales. En instalación vacía, ambas migraciones se aplican antes del seeder sin necesidad del comando.

# 6. Personal_id

Verificado realmente en PostgreSQL: BIGINT, UNIQUE y FK a `personal_institucional.id_personal` con ON DELETE RESTRICT. Durante transición admitió NULL. Antes del endurecimiento había 4/4 vínculos y cero discrepancias de copia; ahora es NOT NULL. La unicidad también reserva relaciones archivadas.

# 7. Migración User directo → Personal

Se usó únicamente el `Tutor.user_id` estructural preexistente. El servicio examina Personal ya asociado a ese User y lo reutiliza solo si es compatible. No busca User por nombre, CI, correo o rol; no crea cuentas ni asigna roles.

Incluye todos los tutores, sin excluir archivados, transacción, bloqueo de escritores en PostgreSQL, preflight y reporte por ID. El proceso es idempotente, incluso después de retirar las columnas.

# 8. Migración de nombres/contacto

Correspondencia exacta: nombres_tutor→nombres, apellidos_tutor→apellidos, ci_tutor→ci, celular_tutor→celular, correo_tutor→correo_contacto.

No se truncó, normalizó ni sustituyó ningún valor durante la migración. Los campos se validan con InputRules. Un valor no NULL distinto en Personal provoca conflicto; solo se completan NULL con datos válidos y sin colisión de CI. Nuevos timestamps de Personal representan su creación por esta operación, no una fecha histórica inferida. Los timestamps originales de Tutor se conservaron.

# 9. Tratamiento de cargo

Los cuatro Personal migrados tienen `cargo_id = NULL`. No se creó un catálogo de cargos en la base principal ni se infirió “Docente”. Un Personal preexistente conservaría su cargo acreditado. Los fixtures sí declaran cargos explícitos.

# 10. Tratamiento de estados

Los cuatro nuevos Personal quedaron en `pendiente`; los cuatro Tutor conservaron `activo`. No se modificó estado de User. Las pruebas cubren inactivación de Tutor, inactivación de Personal y bloqueo de User como operaciones independientes.

# 11. Campos retirados de Tutor

Se retiraron físicamente `user_id`, `nombres_tutor`, `apellidos_tutor`, `ci_tutor`, `celular_tutor` y `correo_tutor`. No quedan aliases editables ni un vínculo Tutor→User paralelo.

La copia anterior es recuperable desde el respaldo. El rollback de esquema reconstruye las columnas con los datos ACTUALES de Personal; no sustituye un respaldo histórico.

# 12. Fuente de verdad final

| Entidad | Autoridad |
|---|---|
| User | Cuenta, correo de acceso, contraseña, verificación, bloqueo y roles |
| PersonalInstitucional | Identidad, CI, contacto y vínculo explícito con User |
| TutorAcademico | Información profesional/docente y relaciones académicas |
| Cargo | Posición organizacional |
| Roles/permisos | Autorización funcional, sin redistribución en este bloque |

# 13. CRUD Tutor

Decisión A: nuevo Tutor se crea seleccionando Personal existente. Si la persona no existe, primero se registra desde el formulario de Organización institucional; no se duplicó ese flujo.

Personal activo o pendiente es elegible si no tiene Tutor, incluido archivado. Personal inactivo mantiene su Tutor histórico, pero no aparece como nueva selección. La identidad de un Tutor existente no se puede reasignar desde el CRUD.

Se mantienen `tutores.ver/crear/editar`. Editar identidad es una opción explícita y exige `personal.editar`, reutilizando `OrganizacionService` dentro de la misma transacción. Validaciones compartidas, whitelists, bloqueo de fila y UNIQUE protegen el guardado. Identificadores inválidos, CI ocupado y campos anidados ajenos son rechazados.

# 14. Frontend

Formulario profesional con selector Personal; edición de identidad/contacto separada mediante casilla opcional. Cargo y estado Personal se muestran como referencia. El correo de acceso es solo lectura y su carga depende de autorización organizacional; no se confunde con correo_contacto.

Se retiró el selector User. El enlace a Personal utiliza el mecanismo existente `VincularUsuarioPersonalAction`, sin endpoint duplicado. Listas, ficha, detalles, grupos, selectores y portal usan Personal. Se verificaron respuestas HTTP/Inertia, reglas Node y compilación; no se realizó una revisión visual manual exhaustiva en navegador.

# 15. Asignaciones

Las 16 filas originales, sus IDs y sus FK se conservaron exactamente. Consultas/selects cargan `personal_id` e identidad con eager loading. Pruebas cubren listado, filtro por Tutor, creación y edición con el mismo FK académico. Un tutor archivado sigue siendo visible en su historial.

# 16. Asistencia

Las 544 filas originales se conservaron exactamente. Se adaptaron listados, opciones, resumen estudiantil y ficha. Pruebas de creación y edición comprueban que el FK sigue siendo `id_tutor`. No se cambió la semántica de asistencia.

# 17. Bitácora

La edición de identidad registra `editar_personal` sobre Personal, reutilizando el servicio del Bloque 3. La creación/edición profesional registra `crear_tutor/editar_tutor` sobre Tutor. Solo se registran ID de Personal y nombres de campos modificados, no una copia completa de CI/contacto.

El proceso de migración conserva la bitácora existente y entrega reporte técnico separado. No genera eventos de sincronización legacy ni notificaciones.

# 18. Datos actuales migrados

| Tutor ID | Personal ID | User ID | Estado Personal | Cargo |
|---|---|---|---|---|
| 1 | 1 | 3 | pendiente | NULL |
| 2 | 2 | 4 | pendiente | NULL |
| 3 | 3 | 5 | pendiente | NULL |
| 4 | 4 | 6 | pendiente | NULL |

Cero Tutor sin Personal, cero discrepancias durante transición. Huella del conjunto de campos profesionales/IDs/timestamps antes y después: `d6fb183650bff34d9e9c215be2953928`.

Solo cambiaron las tablas `migrations`, `personal_institucional` y `tutores_academicos`. Las otras 38 tablas coinciden íntegramente, incluidos User, roles, permisos, postulantes, sesiones, asignaciones y asistencias.

# 19. Conflictos encontrados

Base principal: ninguno. Los cuatro registros se clasificaron INCOMPLETO porque requerían crear Personal. La simulación y la aplicación terminaron con cero conflictos.

Casos incompatibles, CI ocupado, datos inválidos, User discordante y reservas archivadas fueron comprobados con fixtures aislados. No se eligió automáticamente entre identidades contradictorias.

# 20. Seeders

| Seeder | Cambio |
|---|---|
| PersonalInstitucionalSeeder | Devuelve mapa de objetos Personal; datos personales y contactos demo declarados aquí |
| BaseLimpiaAvalanchaSeeder | Pasa objetos Personal a tutores y usa personal_id; no escribe identidad/contacto en Tutor |
| TutoresAcademicosSeeder, alternativo | Requiere cinco objetos Personal explícitos con claves matematica/fisica/quimica/razonamiento/paa; falla antes de escribir si faltan o se repiten referencias |
| DatabaseSeeder / RolesAndUsersSeeder / CargosSeeder | Mantienen orquestación o responsabilidades previas, sin cambio de permisos/roles |

El alternativo ya no funciona como comando suelto sin referencias: su orquestador debe pasar el mapa de Personal. No busca por texto ni crea cuentas; ejecutarlo dos veces sobre los mismos objetos conserva cinco Tutor. No se ejecutó ningún seeder sobre `intelecta`.

# 21. Factories

Nueva `TutorAcademicoFactory`. `create(['personal_id' => $personal->id_personal])` exige referencia explícita. `withPersonal()` crea Personal solo si el test lo pide. Sin Personal, la creación falla por NOT NULL. Ninguna opción crea User o Cargo implícitamente.

# 22. PostgreSQL

Respaldo: `C:/laragon/tmp/intelecta-identity-backups/intelecta-before-block4-20260910_032445.dump`.

Tamaño: 204223 bytes. SHA-256: `5c5441530e656061d02f6ab6bf81bdaa7a457c203b7050b5595e90266c2d274a`.

`pg_restore --list`: catálogo válido, 384 entradas. Restauración de prueba exitosa en `intelecta_tutor_test_20260910_032445`; las 41 tablas restauradas coincidieron con el inventario original antes de cualquier prueba.

Pruebas reales: FK, UNIQUE, NOT NULL, RESTRICT, SQLSTATE 23502/23503/23505, transición nullable, copia exacta, idempotencia, conservación de relaciones y rollback/reaplicación. No se restauró sobre la base principal.

# 23. Instalación limpia

`php artisan migrate:fresh --seed` se ejecutó únicamente en bases aisladas con guardas de nombre y APP_ENV=testing. Resultado: 8 cargos, 5 Personal, 4 Tutor enlazados explícitamente, 9 cuentas activas/verificadas, 72 postulantes (3 vinculados), 16 asignaciones y 544 asistencias. Cero notificaciones reales.

Bases desechables de esta verificación: `intelecta_tutor_test_20260910_032445`, `intelecta_personal_test_20260910_032445`, `intelecta_accounts_test_20260910_032445`, `intelecta_identity_test_20260910_032445`. Se conservaron para inspección. Nunca se ejecutó fresh sobre `intelecta`.

# 24. Regresión Bloque 1

Identidad estable de Postulante, acceso estudiantil y seeders explícitos pasan. No se añadió fallback por email. La tabla principal Postulantes conserva su hash original; ningún vínculo fue alterado. La ficha estudiantil sigue mostrando Tutor desde Personal.

# 25. Regresión Bloque 2

Ciclo de cuenta, gobierno de correo de acceso, verificación, revocación, bloqueo y protección concurrente del último SA pasan. La creación/edición de Tutor no modifica emails de acceso, roles, password, verificación ni estado de cuenta.

# 26. Regresión Bloque 3

Cargo/Personal y vinculación explícita pasan, incluidas pruebas concurrentes. Se actualizaron únicamente las expectativas históricas que exigían que Tutor aún no tuviera personal_id y el rollback aislado para retirar primero las dependencias del Bloque 4. No se debilitaron las pruebas de permisos ni de seguridad.

# 27. Tests

PHPUnit completo: **327/327**, **2.025 aserciones**. Node: **9/9**. Suites nuevas de Tutor/migración/consumidores: **33/33**, **231 aserciones**, tanto SQLite como PostgreSQL.

PostgreSQL funcional de regresión de los cuatro bloques: **122/122**, **950 aserciones**, cero fallos (586,34 segundos). Incluye TutorPersonal, TutorConsumers, Organizacion, AccountLifecycle, StableIdentity, StableStudentAccess, LoginEmailGovernance, EmailVerification e IdentitySeeder. Las 33 pruebas específicas anteriores están incluidas, no se suman como casos diferentes.

Las suites nuevas cubren migración, estados, permisos, identidad, búsqueda/paginación, ausencia de consultas adicionales al serializar la página cargada, archivado, factories, seeder alternativo y consumidores académicos.

Scripts PostgreSQL ya verificados: Tutor 99; Personal instalación 41; concurrencia Personal 3; validaciones básicas 83; fecha Bolivia 11; identidad estudiantil 15; ciclo de cuenta 12; instalación cuentas 12; último SA concurrente 4; instalación identidad 10. Total: **290/290** comprobaciones. El script de fecha se reejecutó correctamente pasando el binario Node como argumento; una invocación inicial pasó por error el nombre de la base y no se contó como válida. Una corrida adicional de regresión PostgreSQL coincidió con mantenimiento y recibió 503; fue descartada y repetida con la aplicación activa, sin excluir ni debilitar pruebas.

# 28. Build

Vite pasó, 3.449 módulos compilados. Última compilación: 4,87 segundos, sin errores.

# 29. Pint

Formato aplicado a archivos modificados/nuevos. `vendor/bin/pint --dirty --test`: passed.

# 30. git diff --check

Sin errores de whitespace, incluido el informe.

# 31. Archivos modificados

Manifest completo (39 archivos; sin staging):

- `C:/laragon/www/intelecta/app/Domains/Academico/Actions/GuardarTutorAcademicoAction.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/DTOs/TutorAcademicoData.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Models/AsignacionTutor.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Models/AsistenciaAcademica.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Models/TutorAcademico.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Repositories/AcademicoRepository.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Repositories/AsignacionTutorRepository.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Repositories/AsistenciaAcademicaRepository.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Repositories/TutorAcademicoRepository.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Services/TutorAcademicoService.php`
- `C:/laragon/www/intelecta/app/Domains/Institucional/Models/PersonalInstitucional.php`
- `C:/laragon/www/intelecta/app/Http/Controllers/Institucional/TutorAcademicoController.php`
- `C:/laragon/www/intelecta/app/Http/Requests/Institucional/TutorAcademicoRequest.php`
- `C:/laragon/www/intelecta/database/seeders/BaseLimpiaAvalanchaSeeder.php`
- `C:/laragon/www/intelecta/database/seeders/PersonalInstitucionalSeeder.php`
- `C:/laragon/www/intelecta/database/seeders/TutoresAcademicosSeeder.php`
- `C:/laragon/www/intelecta/docs/test-postgres-personal-installation.php`
- `C:/laragon/www/intelecta/resources/js/Pages/Estudiante/MiFicha.jsx`
- `C:/laragon/www/intelecta/resources/js/Pages/Institucional/AsignacionTutores/Index.jsx`
- `C:/laragon/www/intelecta/resources/js/Pages/Institucional/Asistencia/Index.jsx`
- `C:/laragon/www/intelecta/resources/js/Pages/Institucional/FichaAcademica/Show.jsx`
- `C:/laragon/www/intelecta/resources/js/Pages/Institucional/Grupos/Index.jsx`
- `C:/laragon/www/intelecta/resources/js/Pages/Institucional/Tutores/Index.jsx`
- `C:/laragon/www/intelecta/resources/js/Pages/Institucional/Tutores/Show.jsx`
- `C:/laragon/www/intelecta/resources/js/lib/inputValidation.js`
- `C:/laragon/www/intelecta/tests/Feature/Institucional/OrganizacionSeederTest.php`
- `C:/laragon/www/intelecta/tests/input-validation.test.mjs`
- `C:/laragon/www/intelecta/app/Console/Commands/MigrarIdentidadTutor.php`
- `C:/laragon/www/intelecta/app/Domains/Academico/Services/MigrarIdentidadTutorService.php`
- `C:/laragon/www/intelecta/database/factories/TutorAcademicoFactory.php`
- `C:/laragon/www/intelecta/database/migrations/2026_09_11_000000_add_personal_id_to_tutores_academicos.php`
- `C:/laragon/www/intelecta/database/migrations/2026_09_11_000100_finalize_tutor_personal_identity.php`
- `C:/laragon/www/intelecta/docs/audit-tutor-identity.php`
- `C:/laragon/www/intelecta/docs/evidencia-tutor-bloque-4.json`
- `C:/laragon/www/intelecta/docs/test-postgres-tutor-identity.php`
- `C:/laragon/www/intelecta/tests/Feature/Institucional/TutorConsumersTest.php`
- `C:/laragon/www/intelecta/tests/Feature/Institucional/TutorPersonalMigrationTest.php`
- `C:/laragon/www/intelecta/tests/Feature/Institucional/TutorPersonalTest.php`
- `C:/laragon/www/intelecta/docs/tutor-personal-bloque-4.md`

# 32. Riesgos / pendientes

- No quedan migraciones del Bloque 4 pendientes en la base principal.
- Acreditar cargo y cambiar estado de los cuatro Personal migrados requiere decisión institucional; permanecen NULL/pendiente intencionadamente.
- En otras bases con conflictos, el comando detiene toda la aplicación. Requiere resolver la ambigüedad, no sobrescribir automáticamente.
- El rollback de esquema reconstruye identidad actual. Para recuperar el estado histórico exacto se necesita el respaldo; no restaurarlo sobre una base en uso sin un plan aprobado.
- El respaldo contiene datos reales; conservarlo protegido fuera del repositorio. Las bases de prueba también requieren manejo apropiado.
- El seeder alternativo exige referencias de Personal; no admite inferencias por email/nombre/CI.
- No se implementaron multirrol, ámbitos/Policies, cambios globales de roles/permisos ni módulos fuera de alcance.

# 33. Estado Git final

Rama `develop`, HEAD conserva `d13206e`. Cambios presentes en working tree para revisión. NO git add, NO commit y NO push. La aplicación quedó fuera de mantenimiento.
