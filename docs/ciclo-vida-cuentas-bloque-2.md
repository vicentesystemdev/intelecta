# Bloque 2 — Ciclo de vida, activación y seguridad de cuentas

## 1. Resumen

Implementación de estados, alta controlada, activación/verificación, revocación, bloqueo/desbloqueo y protección del último Super Administrador activo. El Bloque 1 se conserva: solo postulantes.user_id atribuye identidad académica. No se vincularon candidatos ni se crearon Personal/Cargos.

## 2. Estado inicial

- Rama develop, HEAD 7acf29e (feat: vincular identidad estudiantil mediante user_id).
- Working tree limpio antes de editar.
- PostgreSQL 127.0.0.1:5432, base intelecta, APP_ENV=local.
- 9 users, 4 roles, 3 sesiones, 0 password_reset_tokens.
- Las 9 cuentas tenían email_verified_at no NULL.
- 72 postulantes, 0 user_id asignados; candidatos User 7/8/9 ↔ Postulante 1/2/3 intactos.
- SESSION_DRIVER=database y MAIL_MAILER=log.
- Inventario inicial por PDO en transacción READ ONLY, con rollback; sin arranque de seeders.

## 3. Modelo de estados implementado

EstadoCuenta es un enum PHP con tres valores persistidos: pendiente, activa, bloqueada.
Estado no equivale a rol ni perfil. User::cuentaActiva exige activa y correo verificado.

| Estado | Significado |
|---|---|
| pendiente | Puede completar verificación y contraseña, pero no módulos privados. |
| activa | Verificada y habilitada; sigue necesitando permisos y perfiles correspondientes. |
| bloqueada | No admite login ni sesiones previas; conserva roles, perfiles e historial. |

## 4. Migración users

2026_09_09_000000_add_account_lifecycle_to_users.php es aditiva:

- estado_cuenta NOT NULL, default pendiente y CHECK de tres valores.
- version_acceso NOT NULL, default 0 y CHECK >= 0 en PostgreSQL.
- CHECK PostgreSQL: estado_cuenta <> 'activa' OR email_verified_at IS NOT NULL.
- Verificados existentes → activa; no verificados → pendiente, sin depender del rol.
- No modifica updated_at, credenciales, roles, verificación ni perfiles existentes.

version_acceso no es un estado adicional: es un contador de revocación necesario para impedir que una sesión vieja sobreviva en drivers no enumerables, incluso después de desbloquear.

## 5. Datos existentes migrados

Aplicada solo la migración anterior, tras revisar el SQL con --pretend.
Resultado real: 9 activas/verificadas, 0 pendientes, 0 bloqueadas; todas con version_acceso=0.
Se compararon las columnas originales de las 39 tablas públicas: solo cambió el historial migrations (33 → 34); las columnas originales de users permanecieron intactas.
No se ejecutaron bloqueos, altas, correos, cambios de rol o seeders en la base actual.

Respaldo previo:

- Ruta: C:/laragon/tmp/intelecta-identity-backups/intelecta-before-block2-20260909-135844.dump
- Tamaño: 196105 bytes, formato PostgreSQL custom.
- SHA-256: 884c4955f922c073f4c3cc25257929609122310d21391019d5faa4c86e9faf1c.
- Catálogo comprobado con pg_restore --list. No se realizó una restauración completa.
- El respaldo está fuera del repositorio y contiene datos sensibles: no publicarlo.

## 6. Verificación de correo

User implementa MustVerifyEmail. El middleware verified ya es efectivo.
La URL temporal firmada sigue siendo la nativa de Laravel y se contrasta con el correo actual.
La transición se serializa, vuelve a leer/bloquear User y comprueba que el email no cambió desde la validación de la solicitud.
Verificar pendiente → activa. Verificar una bloqueada, si una operación interna válida lo registra, conserva bloqueada.
HTTP de una cuenta bloqueada termina su sesión; no se habilita una excepción de acceso para verificarla.
La activación no asigna roles ni perfiles.

## 7. Registro público

GET y POST /register quedaron fuera del routing. POST no crea usuarios.
Welcome ya no anuncia canRegister; no hay controles de registro en Welcome/login.
El scaffold de registro puede permanecer en el árbol, pero no tiene endpoint y no está expuesto.
No se borraron archivos del framework.

## 8. Alta controlada de cuentas

Solo SA activo puede crear cuentas, aunque Administrador conserve usuarios.crear.
Nombre, email normalizado y rol se validan con las reglas existentes.
Siempre se crea pendiente y sin verificar, con una credencial aleatoria fuerte hasheada internamente.
Se rechazan password, password_confirmation, estado_cuenta y email_verified_at manipulados en el CRUD.
SA tampoco establece contraseñas desde edición: debe usar el flujo del titular.
La cuenta nueva no recibe sesión automática ni vínculo a un Postulante.

## 9. Activación / establecimiento de contraseña

Se reutiliza Password Broker y password_reset_tokens; no hay tabla de tokens ni criptografía custom.
Secuencia: alta SA → enlace de establecimiento → titular define contraseña → login limitado → enlace de verificación → activa.
Token hasheado en repositorio nativo, caducidad configurada actualmente en 60 minutos y consumo de un solo uso.
El reenvío SA para cuentas pendientes reemplaza el token anterior y se limita por throttle HTTP.
La emisión del token se serializa con los cambios críticos; el transporte de correo ocurre fuera del bloqueo global.
Si falla el correo, la cuenta queda pendiente, el fallo se informa sin detalles del transporte y TI puede reenviar.
Un bloqueo/cambio de email concurrente puede invalidar un enlace emitido: nunca le restaura acceso.
Se usa el mailer configurado. No se asumió SMTP productivo ni dominio institucional.
Notificaciones de establecimiento y verificación tienen textos en español.
Los tests utilizan Notification::fake; los seeders no emiten notificaciones.

## 10. Login por estado

Bloqueada se excluye desde Auth::attempt y recibe el mismo error genérico de credenciales.
Pendiente puede autenticarse, pero se redirige a verificación sin obedecer una URL privada intended.
Activa conserva las redirecciones según rol, siempre sujetas al middleware y al Bloque 1.
Rol inexistente o ausencia de FK no se suplen con email.

## 11. Middleware de cuenta

EnsureAccountAccess es común a la pila web:
autenticación/sesión → estado y versión de acceso → autorización estudiante/binding → módulo.
Relee la cuenta; no confía en un estado de User almacenado en sesión.
Pendientes solo admiten verificación, actualización/confirmación de contraseña y logout.
Mi Perfil ordinario también se redirige a la pantalla mínima de activación mientras la cuenta está pendiente.
JSON recibe 403 para módulos no habilitados y 401 cuando la sesión está revocada; navegación web se redirige de forma controlada.
Se conservan EnsureStudentAccess, EnsureAdministrativeAccess y verified.

## 12. Bloqueo/desbloqueo

Operaciones SA-only, transaccionales, auditadas y con motivo validado para el bloqueo.
Bloquear cambia estado y revoca acceso, sin borrar roles, Postulante, Tutor ni históricos.
Desbloquear devuelve activa si está verificada; en otro caso pendiente.
Desbloquear también revoca versiones antiguas, no recicla sesiones previas.
No existe edición libre de estado en el CRUD.

## 13. Revocación de sesiones

RevocarAccesoService es la única lógica reutilizable:

- Rota remember_token e incrementa version_acceso.
- Invalida tokens de recuperación cuando la operación lo solicita.
- Con driver database elimina sesiones por user_id en la conexión/tabla configuradas.
- Con file, cookie, redis, array u otro driver no intenta enumerar archivos/claves: la versión rechaza sesiones anteriores.
- Una sesión database que se reescriba por una petición en vuelo queda igualmente obsoleta.
- El middleware invalida y termina una sesión revocada en la siguiente petición.
- Para conservar sesiones legítimas anteriores a la migración, la ausencia inicial de versión solo se admite cuando la cuenta sigue en versión 0.
- Una cookie remember vigente puede inicializar una sesión nueva; se contrasta también con el token actual después de releer User.
- El cambio de contraseña propio renueva la sesión actual con la nueva versión y revoca las demás.

La revocación no cancela retroactivamente trabajo ya completado ni garantiza interrumpir una petición que ya estaba ejecutándose antes de bloquear; sí controla las siguientes peticiones.

## 14. users.email

Solo SA activo puede cambiarlo. Se conserva normalización y UNIQUE.
Cambiarlo pone email_verified_at=NULL y estado pendiente, salvo bloqueada, que conserva su bloqueo.
Revoca versiones/sesiones, remember y tokens del correo anterior/nuevo.
El último SA activo no puede cambiar su correo hasta contar con otro SA activo/verificado.
No altera postulantes.user_id ni email_post ni otros expedientes.

## 15. Recuperación de contraseña

Validación y consumo del token se serializan con bloqueo, reenvío y cambio de correo.
Reset no modifica estado, correo, roles ni vínculos; por tanto nunca desbloquea.
El password broker consume el token después de usarlo. La contraseña no se registra en bitácora.
La recuperación pública conserva el mecanismo nativo y añade throttle HTTP.
Una cuenta bloqueada puede restablecer su contraseña si obtiene un token válido posterior, pero seguirá bloqueada y sin login.

## 16. Autoeliminación

Retirados el endpoint DELETE /profile, el método de borrado del controller y la inclusión del formulario en Mi Perfil.
DELETE /profile devuelve 405 porque solo quedan GET/PATCH. No hay borrado físico ordinario de User en el CRUD.
La baja normal es bloqueo. La FK RESTRICT del Bloque 1 sigue intacta.
El archivo del componente legacy puede permanecer sin renderizado; no existe autorización ni endpoint destructivo detrás.

## 17. Protección del último SA

CuentaService concentra altas, edición de rol/email, bloqueo/desbloqueo y transiciones.
PostgreSQL usa pg_advisory_xact_lock(720260902) para serializar operaciones críticas; se bloquea además la fila objetivo.
La comprobación del SA alternativo se ejecuta dentro de esa sección y exige activa + verificada.
Se protege retirar rol, syncRoles escalar, bloquear y cambiar email del último SA. No queda endpoint de eliminación.
Cuentas SA pendientes/bloqueadas no cuentan como alternativa.
El intento de retirar al último SA se registra después del rollback de la operación rechazada.

Prueba real con dos procesos PHP independientes y barrera común:
bloquear/bloquear, degradar/degradar, bloquear/degradar y cambiar email/cambiar email.
En los cuatro casos: una operación aceptada, una rechazada y exactamente un SA activo restante.

La garantía corresponde a las vías ordinarias de aplicación que usan este servicio. SQL administrativo, tinker o seeders tienen autoridad técnica superior y no deben usarse para alterar seguridad de cuentas reales. Cualquier futuro escritor debe respetar el mismo contrato.

## 18. Administrador vs Super Administrador

| Operación | Administrador | SA activo |
|---|---|---|
| Visualización ya autorizada | Conservada | Permitida |
| Crear User | No | Sí |
| Cambiar email/rol/identidad desde CRUD | No | Sí |
| Bloquear/desbloquear | No | Sí |
| Reenviar activación | No | Sí, si pendiente |
| Eliminar físicamente desde aplicación | No | No |

Gate::before se conserva. Puede satisfacer permisos genéricos de navegación, pero no reemplaza las comprobaciones explícitas de SA activo en Requests y CuentaService.
La matriz de 72 permisos no se redistribuyó.
La asignación de rol sigue siendo escalar, no se implementó multirrol.
La Action/comando de vinculación del Bloque 1 ahora exige también actor SA activo, sin alterar la regla de FK.

## 19. Seeders

RolesAndUsersSeeder marca explícitamente los fixtures demo como activa + verificada; no usa el flujo de invitación ni envía correo.
BaseLimpiaAvalanchaSeeder mantiene las asociaciones explícitas del Bloque 1, sin cambios de lógica.
DatabaseSeeder, PostulantesSeeder y sus responsabilidades previas siguen vigentes.
No se ejecutó RolesAndUsersSeeder sobre intelecta; conserva updateOrCreate/syncRoles y no es herramienta de migración de cuentas reales.

## 20. Factory

UserFactory ofrece active(), pending(), blocked() y unverified() como alias de pending().
Default documentado: activa/verificada, para fixtures de módulos autenticados existentes.
Las pruebas de seguridad seleccionan explícitamente los estados relevantes.
La versión inicial del factory es 0; no se crean roles ni perfiles implícitos.

## 21. Frontend

Gestión de usuarios muestra estado, email, verificación y rol.
SA ve acciones por estado: pendiente → reenviar/bloquear; activa → bloquear; bloqueada → desbloquear.
El bloqueo pide motivo y las operaciones se confirman en modal; se muestran errores del último SA.
Administrador no ve controles de seguridad ni formulario de edición.
No hay campos para definir contraseña administrativa.
Mi Perfil mantiene correo de solo lectura y pierde la autoeliminación.
La pantalla de activación explica en español la verificación pendiente.

## 22. Bitácora

Se registran crear, solicitar_activacion, fallo_envio_activacion, verificar_correo, activar_cuenta, bloquear_cuenta, desbloquear_cuenta, cambiar_correo_acceso, cambiar_rol, cambiar_password y rechazar_ultimo_sa.
Incluye actor, ID afectado, fecha del registro y motivo/estados cuando corresponden.
No se pasan contraseñas, tokens ni hashes de credenciales a BitacoraService.
Se conserva su comportamiento previo de mejor esfuerzo; no se afirma que el sink sea infalible.

## 23. Regresión Bloque 1

Se conservan relaciones, FK nullable/UNIQUE/RESTRICT, ausencia de fallback y protección del portal.
Las pruebas de cambio de email ahora verifican la transición pendiente y completan la verificación antes de pedir acceso nuevamente: la FK no cambió.
Los candidatos actuales 7↔1, 8↔2 y 9↔3 siguen con user_id NULL.
No se alteraron fecha de nacimiento, edad, America/La_Paz ni CHECKs anteriores.

## 24. PostgreSQL

Se probó la migración real up/down/up en tabla TEMP y se comprobó que los usuarios reales no cambiaron.
Se verificaron valores válidos, rechazo de NULL/valor inválido, activa sin verificación y versión negativa.
Se aplicó luego la migración específica a intelecta, respaldada; no se hizo rollback sobre ella.
Se ejecutaron los scripts anteriores de validaciones, fecha de nacimiento e identidad estable.

Para recuperación, restaurar primero el dump en otra base nueva y verificarlo. No sobrescribir automáticamente intelecta.
El down() elimina estado/versiones y por tanto pierde bloqueos y revocaciones: no usarlo como recuperación ordinaria ni desplegar código antiguo que ignore estas reglas.

## 25. Instalación limpia

Base aislada: intelecta_accounts_test_20260909_135846.
El script requiere APP_ENV=testing, driver pgsql, nombre explícito con prefijo accounts_test y ausencia de config cache; contrasta current_database antes de permitir fresh.
migrate:fresh --seed produce 9 activas/verificadas, un SA activo, 72 postulantes, 3 FK explícitas y 69 NULL.
Comprueba ausencia de register/profile.destroy y Notification::assertNothingSent().
Nunca se ejecutó migrate:fresh en intelecta.
Los escenarios concurrentes manipulan solo fixtures de esa base de prueba.

## 26. Tests

Ejecución completa final confirmada: PHPUnit 250/250, 1418 assertions, incluyendo el transporte de correo fuera de la transacción crítica.
Node 7/7.
PostgreSQL: checks básicos 83/83, nacimiento 11/11, identidad 15/15, ciclo de vida 12/12, instalación 12/12, concurrencia 4/4.
Suite funcional PostgreSQL: 89/89, 602 assertions (cuentas, autenticación, recuperación, perfil y regresión del Bloque 1).
Tras el último ajuste del transporte de correo se repitió AccountLifecycleTest en PostgreSQL: 21/21, 136 assertions, y la instalación limpia aislada: 12/12.

Fallos intermedios:
la primera regresión tuvo 13 fallos y 1 error por expectativas de registro abierto, autoeliminación, edición administrativa o acceso inmediatamente después de cambiar email.
Se actualizaron esas expectativas a la política aprobada, sin eliminar sus objetivos de seguridad. Se conservó la prueba de espacios en contraseñas trasladándola a establecimiento nativo.

## 27. Build

Vite correcto. La primera compilación mostró un aviso no bloqueante de tiempo del plugin Laravel; la última terminó correctamente.
No se añadieron dependencias.

## 28. Pint

Pint sobre los archivos afectados: correcto.

## 29. git diff --check

Sin errores.

## 30. Archivos modificados

43 archivos nuevos o modificados. Se conserva todo sin staging para revisión.

### Backend y routing

- [app/Console/Commands/VincularPostulante.php](C:/laragon/www/intelecta/app/Console/Commands/VincularPostulante.php)
- [app/Domains/Postulantes/Actions/VincularUsuarioPostulanteAction.php](C:/laragon/www/intelecta/app/Domains/Postulantes/Actions/VincularUsuarioPostulanteAction.php)
- [app/Http/Controllers/Admin/UsuarioController.php](C:/laragon/www/intelecta/app/Http/Controllers/Admin/UsuarioController.php)
- [app/Http/Controllers/Auth/AuthenticatedSessionController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/AuthenticatedSessionController.php)
- [app/Http/Controllers/Auth/EmailVerificationNotificationController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/EmailVerificationNotificationController.php)
- [app/Http/Controllers/Auth/EmailVerificationPromptController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/EmailVerificationPromptController.php)
- [app/Http/Controllers/Auth/NewPasswordController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/NewPasswordController.php)
- [app/Http/Controllers/Auth/PasswordController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/PasswordController.php)
- [app/Http/Controllers/Auth/VerifyEmailController.php](C:/laragon/www/intelecta/app/Http/Controllers/Auth/VerifyEmailController.php)
- [app/Http/Controllers/ProfileController.php](C:/laragon/www/intelecta/app/Http/Controllers/ProfileController.php)
- [app/Http/Requests/Admin/StoreUsuarioRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Admin/StoreUsuarioRequest.php)
- [app/Http/Requests/Admin/UpdateUsuarioRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Admin/UpdateUsuarioRequest.php)
- [app/Http/Requests/Auth/LoginRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Auth/LoginRequest.php)
- [app/Models/User.php](C:/laragon/www/intelecta/app/Models/User.php)
- [app/Providers/AppServiceProvider.php](C:/laragon/www/intelecta/app/Providers/AppServiceProvider.php)
- [bootstrap/app.php](C:/laragon/www/intelecta/bootstrap/app.php)
- [routes/auth.php](C:/laragon/www/intelecta/routes/auth.php)
- [routes/web.php](C:/laragon/www/intelecta/routes/web.php)
- [app/Domains/Seguridad/Enums/EstadoCuenta.php](C:/laragon/www/intelecta/app/Domains/Seguridad/Enums/EstadoCuenta.php)
- [app/Domains/Seguridad/Services/CuentaService.php](C:/laragon/www/intelecta/app/Domains/Seguridad/Services/CuentaService.php)
- [app/Domains/Seguridad/Services/RevocarAccesoService.php](C:/laragon/www/intelecta/app/Domains/Seguridad/Services/RevocarAccesoService.php)
- [app/Http/Middleware/EnsureAccountAccess.php](C:/laragon/www/intelecta/app/Http/Middleware/EnsureAccountAccess.php)
- [app/Http/Requests/Admin/OperacionCuentaRequest.php](C:/laragon/www/intelecta/app/Http/Requests/Admin/OperacionCuentaRequest.php)

### Migración, seeder y factory

- [database/factories/UserFactory.php](C:/laragon/www/intelecta/database/factories/UserFactory.php)
- [database/seeders/RolesAndUsersSeeder.php](C:/laragon/www/intelecta/database/seeders/RolesAndUsersSeeder.php)
- [database/migrations/2026_09_09_000000_add_account_lifecycle_to_users.php](C:/laragon/www/intelecta/database/migrations/2026_09_09_000000_add_account_lifecycle_to_users.php)

### Frontend

- [resources/js/Components/Sistema/UsuarioForm.jsx](C:/laragon/www/intelecta/resources/js/Components/Sistema/UsuarioForm.jsx)
- [resources/js/Pages/Auth/VerifyEmail.jsx](C:/laragon/www/intelecta/resources/js/Pages/Auth/VerifyEmail.jsx)
- [resources/js/Pages/Profile/Edit.jsx](C:/laragon/www/intelecta/resources/js/Pages/Profile/Edit.jsx)
- [resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.jsx](C:/laragon/www/intelecta/resources/js/Pages/Profile/Partials/UpdateProfileInformationForm.jsx)
- [resources/js/Pages/Sistema/Usuarios/Index.jsx](C:/laragon/www/intelecta/resources/js/Pages/Sistema/Usuarios/Index.jsx)

### Tests

- [tests/Feature/Auth/EmailVerificationTest.php](C:/laragon/www/intelecta/tests/Feature/Auth/EmailVerificationTest.php)
- [tests/Feature/Auth/LoginEmailGovernanceTest.php](C:/laragon/www/intelecta/tests/Feature/Auth/LoginEmailGovernanceTest.php)
- [tests/Feature/Auth/RegistrationTest.php](C:/laragon/www/intelecta/tests/Feature/Auth/RegistrationTest.php)
- [tests/Feature/Portal/StableStudentAccessTest.php](C:/laragon/www/intelecta/tests/Feature/Portal/StableStudentAccessTest.php)
- [tests/Feature/Postulantes/StableIdentityTest.php](C:/laragon/www/intelecta/tests/Feature/Postulantes/StableIdentityTest.php)
- [tests/Feature/ProfileTest.php](C:/laragon/www/intelecta/tests/Feature/ProfileTest.php)
- [tests/Feature/Validation/AuthInputValidationTest.php](C:/laragon/www/intelecta/tests/Feature/Validation/AuthInputValidationTest.php)
- [tests/Feature/Auth/AccountLifecycleTest.php](C:/laragon/www/intelecta/tests/Feature/Auth/AccountLifecycleTest.php)

### Documentación y scripts

- [docs/ciclo-vida-cuentas-bloque-2.md](C:/laragon/www/intelecta/docs/ciclo-vida-cuentas-bloque-2.md)
- [docs/test-postgres-account-installation.php](C:/laragon/www/intelecta/docs/test-postgres-account-installation.php)
- [docs/test-postgres-account-lifecycle.php](C:/laragon/www/intelecta/docs/test-postgres-account-lifecycle.php)
- [docs/test-postgres-last-sa-concurrency.php](C:/laragon/www/intelecta/docs/test-postgres-last-sa-concurrency.php)

## 31. Riesgos/pedientes

- Configurar y probar entrega SMTP/servicio de correo productivo, APP_URL y HTTPS antes de desplegar. El mailer local log puede contener enlaces sensibles de desarrollo; proteger sus logs.
- Bitácora sigue siendo de mejor esfuerzo; no es auditoría inmutable.
- La protección de cuentas no reemplaza la futura redistribución de permisos ni la eliminación/revisión de Gate::before.
- No se implementaron Personal, Cargos, multirrol, ámbitos docentes ni módulos fuera de alcance.
- No hay asociación automática de los tres candidatos. Activar una cuenta no certifica su expediente.
- El procedimiento de recuperación debe preservar estado y versión; restaurar snapshots antiguos puede restaurar credenciales/sesiones antiguas y requiere revocación planificada.
- Los operadores con SQL/Artisan privilegiado deben seguir los procedimientos; no ejecutar seeders como backfill.

## 32. Estado Git final

Se mantiene develop en 7acf29e. Sin git add, sin commit y sin push.
