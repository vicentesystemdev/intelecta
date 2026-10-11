# Guía de instalación de INTELECTA

Entrega Hito 5, 11 de octubre de 2026. Instalación local sobre una base nueva; para datos existentes consulte la última sección.

## Requisitos

- Git y Composer 2.
- PHP 8.3+ y extensiones exigidas por `composer.lock`. Para este proyecto: PDO, pdo_pgsql, pdo_sqlite para pruebas, mbstring, XML/DOM, fileinfo, curl, zip, GD, entre otras comprobadas por Composer.
- Node.js 22.12+ de la serie 22 y npm. Vite 8 admite también Node 20.19+, según el archivo de bloqueo.
- PostgreSQL accesible y una cuenta capaz de crear tablas e índices en una base dedicada. No se afirma compatibilidad institucional completa con MySQL ni con SQLite: algunas restricciones se implementan específicamente en PostgreSQL.
- Permiso de escritura en `storage/` y `bootstrap/cache/`.

## Clonar e instalar dependencias

```bash
git clone https://github.com/vicentesystemdev/intelecta.git
cd intelecta
git switch main
composer install
npm ci --ignore-scripts
```

Use los archivos de bloqueo; no ejecute actualizaciones generales. `npm ci --ignore-scripts` coincide con la precaución del script `setup` existente. El script `composer run setup` también ejecuta migraciones: se recomienda seguir los pasos explícitos para revisar antes la base de destino.

## Entorno y base de demostración

Copie `.env.example` a `.env` solo en una instalación nueva. PowerShell:

```powershell
if (!(Test-Path .env)) { Copy-Item .env.example .env }
```

Linux/macOS: `cp .env.example .env` después de comprobar que `.env` no existe.

Ejemplo en una sesión `psql` con una cuenta administradora local:

```sql
CREATE ROLE intelecta_demo LOGIN;
\password intelecta_demo
CREATE DATABASE intelecta_demo OWNER intelecta_demo;
```

`\password` solicita una contraseña propia sin guardarla en este documento. No cambie usuarios ni contraseñas de la base de trabajo. Configure en el `.env` privado:

```dotenv
APP_NAME=INTELECTA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=intelecta_demo
DB_USERNAME=intelecta_demo
DB_PASSWORD=
```

Complete `DB_PASSWORD` con la contraseña recién elegida. Si contiene espacios o `#`, enciérrela entre comillas. `APP_KEY` se genera con el siguiente comando; no publique `.env`. Las sesiones, caché y cola usan tablas de la misma base; el correo usa `log`, sin enviar mensajes externos. Para revisar invitaciones locales, consulte su log privado; contiene enlaces sensibles y no debe publicarse.

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Las migraciones crean también tablas para sesiones, caché y trabajos. No se necesita Redis. El acceso queda en `http://127.0.0.1:8000/login`; las cuentas ficticias están en [usuarios de demostración](usuarios_prueba_avalancha.md).

## Datos ficticios y orden de carga

`DatabaseSeeder` invoca únicamente `BaseLimpiaAvalanchaSeeder`, que coordina:

1. Roles, permisos y nueve cuentas ficticias; cargos y personal institucional.
2. Universidades, carreras y colegios.
3. Materias, áreas, temas, preguntas, alternativas y plantillas ponderadas.
4. Programas y grupos; tutores y asignaciones.
5. Postulantes vinculados explícitamente a las tres cuentas del portal.
6. Inscripciones, matrículas, cuotas y habilitaciones.
7. Asistencia y calendario de simulacros.
8. Evaluaciones aplicadas, respuestas, resultados derivados y asistencia en rendimiento.

El escenario prepara 72 postulantes y nueve cuentas. Los nombres, identificaciones y respuestas son fixtures ficticios; los dominios de correo del escenario no implican cuentas institucionales reales. Los períodos, plantillas y fechas son definidos por el seeder y algunas fechas dependen del día de ejecución; compruebe el calendario antes de la defensa.

El seeder utiliza `updateOrCreate`, `firstOrCreate`, sincronización de roles y relaciones. **Puede sobrescribir registros coincidentes y restablece la contraseña de las cuentas de demostración**: úselo una sola vez en una base dedicada. No es una carga inocua sobre datos reales ni garantiza una restauración exacta después de operaciones manuales. Las cuentas demo se bloquean fuera de entornos `local` y `testing`. No ejecute los seeders históricos adicionales sobre este escenario sin revisar sus diferencias.

## Ejecución y pruebas

Con los assets compilados basta `php artisan serve`. Para editar la interfaz, mantenga otra terminal con `npm run dev`. Para servidor, cola y Vite simultáneos:

```bash
composer run dev
```

Si una operación local requiere procesar correo/trabajos en cola y solo utiliza `artisan serve`, abra otra terminal con `php artisan queue:listen --tries=1 --timeout=0`.

```bash
composer validate
php artisan route:list
npm run build
php artisan test
node --test tests/input-validation.test.mjs
```

Espere a que termine el build antes de iniciar la suite: algunas pruebas HTTP renderizan el documento Inertia y requieren el manifiesto de assets. No reconstruya `public/build` durante su ejecución. La suite normal usa SQLite en memoria mediante `phpunit.xml`. Verifique que `bootstrap/cache/config.php` no exista y que su shell no imponga `DB_CONNECTION`, `DB_DATABASE` o `DB_URL` de una base real. `php artisan config:clear` elimina únicamente la configuración cacheada, si necesita regenerarla. No active `RUN_POSTGRES_CONCURRENCY` sobre una base de trabajo. La suite SQLite no valida los bloqueos ni todas las restricciones de PostgreSQL.

## Actualizar una instalación existente

Conserve `.env`, datos y archivos privados; haga respaldo y revise `git status` antes de actualizar código. No regenere `APP_KEY` en una instalación existente. Ejecute `php artisan migrate:status` para identificar pendientes y revise las migraciones antes de aplicarlas; no recargue cuentas demo.

La migración `2026_09_11_000100_finalize_tutor_personal_identity.php` exige que la identidad Tutor → Personal esté conciliada y puede detenerse si hay datos legacy pendientes. El procedimiento está documentado en [identidad de tutores](tutor-personal-bloque-4.md); el comando `php artisan tutores:migrar-identidad --dry-run` permite inspeccionar el estado. La conciliación de una base existente debe planificarse con respaldo; esta entrega no la ejecuta sobre datos de trabajo.

No use `migrate:fresh`, `db:wipe` ni restauraciones destructivas para instalar o actualizar el sistema de trabajo.
