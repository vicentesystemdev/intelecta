# 1. Resumen

Bloque 5 implementado y matriz aplicada a `intelecta` sin seeders sobre la base actual. Se separan seguridad y operación académica, se habilita Administrador + Docente y se retiran accesos docentes globales sin ámbito. No se implementaron Policies, roles nuevos, ámbitos, RRHH ni Finanzas.

Validación terminada: PHPUnit 373/373, Node 11/11, suite PostgreSQL 371/371 y 46/46 pruebas RBAC finales, además de 383 comprobaciones de migración, instalación y concurrencia. Evidencia consolidada: [rbac-evidencia-bloque-5.json](rbac-evidencia-bloque-5.json).

# 2. Estado inicial

10/09/2026, rama `develop`, working tree limpio. HEAD `20cafdd` — `refactor: consolidar identidad de tutores en personal institucional`. PostgreSQL 17.2 disponible. Se comprobaron `git status` y los cinco commits recientes antes de editar.

Respaldo fuera del repositorio: `C:/laragon/tmp/intelecta-identity-backups/intelecta-before-block5-20260910_075546.dump`, 203710 bytes; SHA-256 `2abcb688e187e005dce3ffaf8d3c9aceb4abbf44e4f2952d3d2c5bcb678e3cf8`. Restauración verificada: 41/41 tablas coincidentes por conteo y huella.

# 3. Inventario RBAC previo

| Elemento | Estado previo |
|---|---:|
| Roles web | 4 |
| Catálogo de permisos | 80 |
| role_has_permissions | 179 |
| model_has_roles | 9 |
| model_has_permissions | 0 |
| Cuentas | 9 |
| SA / Administrador / Docente / Estudiante | 1 / 1 / 4 / 3 |
| Permisos por rol, en ese orden | 80 / 80 / 19 / 0 |

Snapshot sin credenciales ni datos personales: [rbac-preflight-bloque-5.json](rbac-preflight-bloque-5.json). Consumidores exactos por permiso, método, URI y nombre: [rbac-consumidores-bloque-5.json](rbac-consumidores-bloque-5.json). No existen rutas con middleware literal `role:`; sí contextos deliberados en middleware, Requests y Gate.

# 4. Matriz objetivo aprobada/implementada

La [matriz explícita de 81 filas](rbac-matriz-bloque-5.md) se construyó **antes** de modificar asignaciones, a partir de rutas, Requests y consultas reales. Sus columnas incluyen SA, Administrador, Docente, Estudiante y motivo.

| Rol | Antes | Después |
|---|---:|---:|
| Super Administrador | 80 | 70 |
| Administrador | 80 | 61 |
| Docente | 19 | 6 |
| Estudiante | 0 | 0 |

70 capacidades activas, todas con ruta consumidora; 137 vínculos rol-permiso. Catálogo de 81 filas: las 80 originales más `usuarios.asignar_roles`, con consumidor nuevo y concreto. No se crearon dependencias ficticias para rescatar permisos sin uso.

# 5. Cambios Super Administrador

Conserva el bypass `Gate::before`, sus funciones técnicas y acceso académico. La autorización de seguridad requiere SA activo y verificado, releído de la base; Gate no sustituye ese control. SA es exclusivo en nuevas asignaciones y no exige Personal. Su matriz no es editable desde la pantalla ordinaria.

# 6. Cambios Administrador

Conserva 61 capacidades académicas: organización, Personal, Cargos, postulantes, programas, grupos, inscripciones, matrículas/cuotas, habilitación, tutores, asignaciones, asistencia, simulacros, contenido, catálogos y seguimiento.

Pierde las ocho capacidades de seguridad consumidas previamente: `usuarios.ver/crear/editar`, `roles-permisos.ver/editar`, `bitacora.ver/exportar`, `configuracion.ver`. Tampoco recibe `usuarios.asignar_roles`. Los 11 permisos sin consumidor también salen de sus asignaciones. La bitácora global y configuración se consideran parte del contexto de seguridad.

# 7. Cambios Docente

Conserva: `materias.ver`, `areas.ver`, `temas.ver`, `preguntas.ver`, `preguntas.crear`, `plantillas.ver`. Las consultas de plantillas se limitaron a verificar su contenido existente: preguntas, alternativas y taxonomía, sin expedientes ni resultados de personas.

Se retiran temporalmente estos 13 permisos: `dashboard.ver`, `programas.ver`, `grupos.ver`, `postulantes.ver`, `ficha-academica.ver`, `ranking.ver`, `asistencia.ver`, `simulacros.ver`, `resultados.ver`, `reportes.ver`, `indicadores.ver`, `learning_analytics.ver`, `evaluaciones.ver`. Son consultas globales o centros de navegación hacia información institucional. No se añadieron bypass ni ámbitos simulados.

# 8. Estudiante

Conserva autorización positiva: rol Estudiante + Postulante vinculado no archivado + reglas del portal, con cuenta activa/verificada. No recibe permisos administrativos. `Postulante` es un perfil, no un quinto rol; su intento de asignación se rechaza.

Los tres estudiantes actuales siguen sin vincularse automáticamente. Mantienen su rol, pero el portal continúa denegando acceso hasta acreditar su expediente.

# 9. Multirrol

Spatie conserva N:M y unión aditiva de permisos. No hay rol principal, permisos negativos ni permisos directos ordinarios. Administrador + Docente funciona en cualquier orden. Editar User, Personal o Tutor conserva ambos roles; añadir/quitar se hace enviando el conjunto completo al endpoint independiente.

Se prohíben nuevas combinaciones de Estudiante o SA con otros roles. Una combinación legacy técnica no se transforma ni se elimina silenciosamente: el contexto institucional se comprueba positivamente.

# 10. Compatibilidad rol-perfil

| Nueva asignación | Requisito |
|---|---|
| Estudiante | Postulante explícitamente vinculado y no archivado |
| Docente | Personal vinculado + Tutor no archivado |
| Administrador | Personal vinculado |
| Super Administrador | Sin requisito de Personal |

No se exige Personal activo: pendiente es válido. Tutor inactivo no implica quitar roles; la operatividad queda para reglas/Policies posteriores. Tutor archivado no acredita una nueva asignación.

Las asignaciones ya existentes no se reacreditan para conservarse. El Administrador id 2 sigue operativo sin Personal: **perfil institucional pendiente de acreditación**. Reasignar un rol previamente retirado sí cuenta como nueva asignación.

# 11. Middleware

`EnsureAdministrativeAccess` exige cuenta activa y alguno de los contextos SA/Administrador/Docente; después se aplica el permiso de la ruta. Ya no clasifica por “no es Estudiante”. `canManageOrganization` también es positivo.

`EnsureSecurityAccess` protege las 12 rutas de `/admin/sistema` con SA activo fresco, incluso si un rol recibe permisos de seguridad mediante una manipulación externa. El dashboard comprueba SA/Administrador antes de consultar información global.

# 12. Redirecciones

Prioridad determinista: SA o Administrador → dashboard; Docente → preguntas u otro catálogo pedagógico disponible; Estudiante → portal; sin rol/capacidades → inicio público. Cuenta pendiente → verificación.

Login descarta el destino previo guardado y usa `homeRoute()`. Portada y navegación reciben `auth.context` y `auth.homeUrl` del backend. Administrador + Docente llega al dashboard; los cuatro docentes actuales llegan a `/preguntas`.

# 13. Gestión de roles

Endpoint `PUT /admin/sistema/usuarios/{usuario}/roles`, Request `AsignarRolesUsuarioRequest`, operación `CuentaService::assignRoles`, evento `asignar_roles`.

Valida lista de strings, nombres whitelist existentes en guard web, sin duplicados, máximo cuatro, combinaciones y perfiles. Revalida actor, bloquea cuenta y usa el mismo mutex PostgreSQL del Bloque 2. La protección del último SA se aplica al reemplazo completo, incluido multirrol.

Crear y editar User rechaza `role` y `roles`, incluso vacíos; el servicio también lo impide. La creación queda sin roles. No hay efectos laterales sobre perfiles/cargos.

Para nuevos estudiantes: crear cuenta → confirmar documentalmente el vínculo con el comando explícito existente → asignar Estudiante. La vinculación ahora acepta cuentas sin roles además de cuentas exclusivamente Estudiante, resolviendo el ciclo de acreditación sin autovincular nada.

Si una asignación cambia roles, se revocan sesiones y enlaces de recuperación, manteniendo la semántica del Bloque 2. Para una cuenta pendiente, reenviar activación después de asignar roles. Una asignación idéntica es idempotente y no revoca.

# 14. Gestión de permisos

Solo SA activo accede y edita. `PermisosRolService` serializa cambios bajo el mutex de cuentas, revalida al actor y aplica techos iguales a la matriz objetivo: no puede conceder seguridad al Administrador, acceso global al Docente ni administración al Estudiante.

La UI deshabilita permisos fuera del techo; la defensa real está en backend. La caché se invalida también después del commit.

# 15. Permisos retirados

11 capacidades sin consumidor salen de **todos** los pivotes activos: `usuarios.eliminar`, `roles.ver/crear/editar`, `docentes.ver/crear/editar/eliminar`, `evaluaciones.crear/editar/cerrar`.

Clasificación: RETIRABLE AHORA de la matriz activa. Ninguna fila original del catálogo fue eliminada en `intelecta`.

# 16. Permisos legacy pendientes

Las 11 filas anteriores permanecen como LEGACY no asignado ni seleccionable. RESERVADO CON JUSTIFICACIÓN: ninguno.

`postulantes.eliminar`, `preguntas.eliminar`, `plantillas.eliminar` realmente cambian estado; se conservan sin rename. El bypass SA continúa vigente incluso para comprobaciones nominales legacy, pero no existen endpoints consumidores de esas 11 capacidades retiradas.

# 17. Seeders

`RolesAndUsersSeeder` usa la misma fuente `MatrizRbac` que el despliegue y los techos UI. Una ejecución ordinaria de `DatabaseSeeder` produce toda la matriz nueva sin pasos manuales.

Los nueve fixtures siguen declarando roles mediante referencias explícitas, sin combinaciones innecesarias. Los seeders posteriores completan los perfiles demo explícitos de Bloques 1–4; asignar un rol en producción no crea esos perfiles. No se ejecutó ningún seeder sobre la base actual.

# 18. Migración de autorización

Comando `rbac:matriz`: solo informa por defecto; para escribir exige acción explícita, nombre exacto de la BD y snapshot nuevo. No modifica cuentas, credenciales, roles de usuarios, perfiles, cargos ni sesiones. Rechaza roles/guards/catálogos inesperados y permisos directos no revisados.

Ejecutado en la base actual:

```powershell
php artisan rbac:matriz --apply --confirm-database=intelecta --snapshot=C:/laragon/tmp/intelecta-identity-backups/intelecta-rbac-block5-before-20260910_075546.json
```

La segunda ejecución respondió “Sin cambios”. Snapshot SHA-256: `75052a9519777b545c2d6916190f92cdad81aa34ab1893a34dcafab0c5bf3b6c`.

Rollback ensayado **solo en una copia aislada**: `rbac:matriz --restore=<snapshot-de-esa-base> --confirm-database=<esa-base>`. Comprueba base, IDs, catálogo y matriz posterior; rechaza sobrescribir modificaciones posteriores. Conserva el catálogo anterior y retira únicamente la definición añadida por este despliegue cuando corresponde. Es un procedimiento técnico privilegiado del operador, no una alternativa HTTP a los controles de SA.

No se necesitó migración estructural nueva. Único cambio de estructura de autorización: la definición consumida `usuarios.asignar_roles`.

# 19. Frontend

Modal independiente con conjunto anterior/nuevo, casillas múltiples, confirmación, errores por campo y aviso de revocación. Edición de cuenta sin selector de rol. Tabla, detalle y AdminLayout muestran todos los roles.

Menú de sistema condicionado a capacidad SA del backend; organización por capacidades organizacionales y el resto por permisos. Portada corregida en sus tres enlaces al panel. Sin `roles[0]` funcional ni visual.

# 20. Bitácora

Asignaciones registran actor, cuenta, arrays completos anterior/nuevo. Matriz registra rol, conjunto anterior/nuevo y capacidades agregadas/retiradas. Login/logout, cuenta, Personal y Tutor usan una etiqueta de todos los roles, ordenada, sin perder el conjunto por tomar el primero.

No se registran passwords ni tokens. El despliegue de la BD actual conserva la bitácora existente: su trazabilidad técnica reside en snapshot, matriz, evidencia y salida del comando; los cambios interactivos se registran en `bitacora_sistema`.

# 21. Datos actuales

Comparación completa posterior: **39 de 41 tablas idénticas**. Solo cambian `permissions` (80 → 81) y `role_has_permissions` (179 → 137). Caché de permisos invalidada; la tabla cache conservó su huella. Repetir el comando no alteró asignaciones.

Se preservan 9 User, 9 vínculos User–Role, 72 Postulante, 4 Personal pendientes, 4 Tutor activos, 0 Cargo y 3 sesiones. Administrador sin Personal intacto; docentes sin cargo intactos; tres candidatos Estudiante sin vínculo intactos. Ninguna credencial fue sustituida.

# 22. PostgreSQL

Version 17.2. Ensayo sobre copia del respaldo: 90 comprobaciones de snapshot, aplicación, idempotencia, rollback exacto de 41 tablas y reaplicación. Los 46 tests finales RBAC pasaron en PostgreSQL, incluyendo pivote multirrol, perfiles, techos y preservación.

Suite PostgreSQL completa: 371/371, 2323 assertions, 736945 ms. Después se verificó la versión final de los 46 tests RBAC (390 assertions), que incluye dos pruebas nuevas de contexto de portada y consumidores. Entre las ejecuciones quedan cubiertos los 373 casos distintos; no se suman los casos repetidos.

# 23. Instalación limpia

Se ejecutó `php artisan migrate:fresh --seed --force` exclusivamente en `intelecta_rbac_test_20260910_075546`: matriz exacta, cuatro roles, 81 definiciones, 137 pivotes, nueve cuentas activas/verificadas, cero permisos directos, cinco Personal, cuatro Tutor, ocho Cargo y tres estudiantes vinculados explícitamente a sus fixtures.

Se verificaron también instalaciones aisladas de cuentas, identidad y Personal. **Nunca se ejecutó fresh ni seeding en intelecta.**

# 24. Regresión Bloque 1

StableIdentity, StableStudentAccess e IdentitySeeder incluidos en PHPUnit. PostgreSQL: 15 comprobaciones de identidad estable y 10 de instalación limpia; sin autovínculos ni cambios a los tres candidatos actuales.

Cambio deliberado de contrato: la vinculación explícita admite cuenta sin roles para acreditar un nuevo Estudiante. Conserva IDs, confirmación humana, auditoría, unicidad, no archivado, no reemplazo y autorización SA fresco.

# 25. Regresión Bloque 2

AccountLifecycle, Authentication, EmailVerification y LoginEmailGovernance incluidos. Tokens, activación, bloqueo, verificación y revocación intactos. Las pruebas de cambio de rol se trasladaron a la operación dedicada; no se debilitó el control.

PostgreSQL: 12 comprobaciones de ciclo de vida, 12 de instalación y seis escenarios concurrentes reales: bloqueo/bloqueo, retiro/retiro, bloqueo/retiro, correo/correo, reemplazo multirrol/multirrol y retiro/multirrol. Siempre queda exactamente un SA activo.

# 26. Regresión Bloque 3

Organizacion, User–Personal, Cargos y OrganizacionSeeder incluidos. PostgreSQL: 41 comprobaciones de instalación/rollback/organización y tres escenarios de vinculación concurrente. Cargo no participa en autorización. No se creó Personal para el Administrador actual.

# 27. Regresión Bloque 4

TutorPersonal y TutorConsumers incluidos. Script PostgreSQL de migración Tutor–Personal: 100/100 comprobaciones, incluidos rollback/transición/reaplicación, preservación y consumidores. Sin columnas de identidad legacy reintroducidas en la base actual ni cambios a estados de los docentes.

# 28. Tests

| Ejecución | Resultado | Assertions |
|---|---|---:|
| PHPUnit completo final, SQLite aislado | 373/373 | 2418 |
| Node, validaciones y contratos UI | 11/11 | — |
| PostgreSQL, suite completa | 371/371 | 2323 |
| PostgreSQL, RBAC final | 46/46 | 390 |
| Scripts PostgreSQL, instalación/migraciones/concurrencia | 383/383 comprobaciones | Contadores propios |

No se suman ejecuciones solapadas como si fueran tests únicos. La suite completa inicial de 327 tests se adaptó únicamente a los contratos nuevos explícitos; la final tiene 373.

# 29. Build

`vite build` correcto: 3450 módulos, sin errores. Validación funcional de backend/contratos UI y compilación; no se afirma una revisión visual manual en navegador.

# 30. Pint

`vendor/bin/pint --dirty` aplicado y `--dirty --test` correcto.

# 31. git diff --check

Correcto, sin errores de whitespace.

# 32. Archivos modificados

Incluye archivos nuevos no agregados al índice:

- `app/Console/Commands/AplicarMatrizRbac.php`
- `app/Console/Commands/VincularPostulante.php`
- `app/Domains/Academico/Services/TutorAcademicoService.php`
- `app/Domains/Institucional/Services/OrganizacionService.php`
- `app/Domains/Postulantes/Actions/VincularUsuarioPostulanteAction.php`
- `app/Domains/Seguridad/Services/BitacoraService.php`
- `app/Domains/Seguridad/Services/CuentaService.php`
- `app/Domains/Seguridad/Services/DesplegarMatrizRbac.php`
- `app/Domains/Seguridad/Services/PermisosRolService.php`
- `app/Domains/Seguridad/Support/MatrizRbac.php`
- `app/Http/Controllers/Admin/RolPermisoController.php`
- `app/Http/Controllers/Admin/UsuarioController.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Middleware/EnsureAdministrativeAccess.php`
- `app/Http/Middleware/EnsureSecurityAccess.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Requests/Admin/AsignarRolesUsuarioRequest.php`
- `app/Http/Requests/Admin/StoreUsuarioRequest.php`
- `app/Http/Requests/Admin/UpdateRolPermisosRequest.php`
- `app/Http/Requests/Admin/UpdateUsuarioRequest.php`
- `app/Listeners/Seguridad/RegistrarLoginExitoso.php`
- `app/Listeners/Seguridad/RegistrarLogout.php`
- `app/Models/User.php`
- `bootstrap/app.php`
- `database/seeders/RolesAndUsersSeeder.php`
- `docs/rbac-bloque-5.md`
- `docs/rbac-consumidores-bloque-5.json`
- `docs/rbac-evidencia-bloque-5.json`
- `docs/rbac-matriz-bloque-5.md`
- `docs/rbac-preflight-bloque-5.json`
- `docs/test-postgres-last-sa-concurrency.php`
- `docs/test-postgres-rbac-deployment.php`
- `lang/es/validation.php`
- `resources/js/Components/AppSidebar.jsx`
- `resources/js/Components/Sistema/UsuarioForm.jsx`
- `resources/js/Components/Sistema/UsuarioRolesForm.jsx`
- `resources/js/Layouts/AdminLayout.jsx`
- `resources/js/Pages/Sistema/RolesPermisos/Index.jsx`
- `resources/js/Pages/Sistema/Usuarios/Index.jsx`
- `resources/js/Pages/Welcome.jsx`
- `routes/web.php`
- `tests/Feature/Auth/AccountLifecycleTest.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/Auth/LoginEmailGovernanceTest.php`
- `tests/Feature/Institucional/OrganizacionSeederTest.php`
- `tests/Feature/Institucional/OrganizacionTest.php`
- `tests/Feature/Portal/StableStudentAccessTest.php`
- `tests/Feature/Seguridad/BitacoraSistemaTest.php`
- `tests/Feature/Seguridad/RbacDeploymentTest.php`
- `tests/Feature/Seguridad/RbacGovernanceTest.php`
- `tests/Feature/Validation/AuthInputValidationTest.php`
- `tests/Feature/Validation/RequestValidationTest.php`
- `tests/input-validation.test.mjs`

# 33. Riesgos y pendientes

- Bloque 6 debe devolver capacidades docentes sensibles solo con Policy + asignación + ámbito. No se dejaron accesos globales como sustituto.
- Administrador actual id 2: acreditación institucional pendiente. Los tres estudiantes actuales requieren confirmación documental y vínculo explícito. No se resolvieron por inferencia.
- Los cuatro Personal docentes siguen pendientes y sin Cargo; su operatividad por estado queda para reglas posteriores.
- No hay negativo por usuario ni multitenancy; las combinaciones nuevas permitidas se limitan deliberadamente a las acordadas.
- El rol SA mantiene Gate bypass. Sus operaciones críticas cuentan con protección explícita; un operador con acceso directo a SQL/PHP tiene autoridad de despliegue y debe custodiar snapshots/backups.
- El logger institucional conserva su comportamiento existente ante fallos de escritura; no se rediseñó su disponibilidad. Las pruebas comprueban eventos y ausencia de secretos en condiciones operativas.
- Las bases y respaldos aislados se conservan para evidencia; no se borró información material ni se limpiaron bases del usuario.
- Si se cambian roles de una cuenta pendiente después de invitarla, reenviar activación, como advierte el modal.

# 34. Estado Git final

Rama `develop`, HEAD `20cafdd` conservado; cambios locales listos para revisión. **NO git add, NO commit, NO push.**
