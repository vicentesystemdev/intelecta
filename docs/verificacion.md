# Verificación de INTELECTA

Fecha: **11 de octubre de 2026**. Los resultados de esta página corresponden a la consolidación de octubre; los JSON de septiembre permanecen como evidencia histórica.

## Entorno de verificación

- Windows / PowerShell, PHP 8.3.33, Node.js 22.23.2, npm 10.9.8.
- Laravel 13.16.1 e Inertia Laravel 2.0.24, según `composer.lock`; React 18.3.1 y Vite 8.0.16, según `package-lock.json`.
- Suite normal con SQLite `:memory:`, caché/sesiones en memoria, cola síncrona y correo en memoria. Las variables de conexión se establecieron explícitamente para no utilizar la base de trabajo; no había configuración Laravel cacheada.
- PostgreSQL 17.2 temporal, escuchando solo en loopback, en el puerto 55439 y con directorio de datos separado de la instalación habitual. Se creó una base exclusiva de pruebas. No se modificó `.env` ni la base PostgreSQL de trabajo.

## Comprobaciones ejecutadas

| Comando / comprobación | Resultado real |
| --- | --- |
| `composer validate --no-check-publish` | Configuración válida; advertencias por versiones exactas de Dompdf y Laravel Excel, conservadas para evitar cambios de dependencias |
| `composer check-platform-reqs` | Requisitos de plataforma satisfechos |
| `composer install --dry-run --no-interaction` | Archivo de bloqueo instalable; nada que instalar, actualizar o quitar en el entorno existente |
| `php artisan route:list --except-vendor` | Correcto: 127 rutas de aplicación |
| `php artisan test --compact`, revisión inicial | 404 aprobadas, 4 omitidas, 3053 aserciones; sin fallos |
| `php artisan test --compact`, verificación final después de corregir la prueba y terminar el build | 406 aprobadas, 4 omitidas, 3061 aserciones; sin fallos |
| `php artisan test --group=postgres-concurrency --compact`, conexión PostgreSQL aislada | 4 aprobadas, 28 aserciones; sin fallos ni omisiones |
| `node --test tests/input-validation.test.mjs` | 20 aprobadas; sin fallos ni omisiones |
| `npm run build` después de los cambios de interfaz | Compilación correcta |
| `npm ci --ignore-scripts` y compilación posterior | 549 paquetes instalados desde el archivo de bloqueo; build correcto, con versiones bloqueadas conservadas |
| `npm audit --json` | 28 vulnerabilidades: 1 baja, 4 moderadas, 20 altas y 3 críticas; comprobación con avisos pendientes, no una auditoría sin hallazgos |
| `php -l` en PHP y Blade modificados y nueva prueba | Sin errores de sintaxis |
| `git diff --check` | Sin errores de espacios en el diff |
| Enlaces Markdown relativos en README, arquitectura y `docs/*.md` | 343 destinos comprobados, ninguno faltante |

La primera ejecución PHP cubre autenticación, ciclo de vida de cuentas, RBAC, acceso docente por ámbito, identidad de postulantes/tutores, integridad académica, resultados trazables y reportes. Las cuatro omisiones de la suite normal corresponden a pruebas que requieren PostgreSQL aislado y `RUN_POSTGRES_CONCURRENCY=1`; no representan pruebas aprobadas en SQLite. Se ejecutaron después expresamente en PostgreSQL y las cuatro pasaron: capacidad de inscripción, inscripción frente a inicio de simulacro, código de matrícula y revocación de asignación frente a asistencia.

La primera ejecución de las dos nuevas comprobaciones del seeder detectó una expectativa incorrecta en la prueba: suponía una tabla de permisos vacía, pero las migraciones crean ocho permisos organizacionales. Se corrigió la prueba para comparar el catálogo antes y después del rechazo. No fue un fallo del sistema ni se eliminaron permisos para hacer pasar la prueba.

Otra repetición coincidió con una reconstrucción de assets y una prueba HTTP recibió
`ViteManifestNotFoundException` durante el reemplazo del manifiesto. Esa ejecución no
se considera aprobada. La verificación final se ejecutó con `npm run build`
terminado y sin compilaciones simultáneas, y pasó completa. Las pruebas HTTP actuales
requieren los assets compilados o un servidor Vite disponible.

Los avisos críticos de npm afectan a `concurrently` y a dependencias transitivas `proxy-addr` y `shell-quote` dentro del árbol instalado. La severidad y el conteo corresponden al reporte obtenido en esta fecha; no se afirma explotabilidad en una instalación concreta. Se conserva el bloqueo por el alcance conservador solicitado. Se requiere evaluación y actualización controlada antes de producción; no se ejecutó `npm audit fix` ni cambios automáticos de dependencias.

## Instalación PostgreSQL desde cero

La conexión habitual al puerto 5432 no estaba disponible. Se utilizó una instancia temporal separada para comprobar las migraciones y el seeder sin arrancar ni alterar la base habitual. En una base nueva se ejecutó el equivalente de `php artisan migrate --seed`, con notificaciones simuladas, caché en memoria y entorno `testing`.

| Registro | Conteo comprobado |
| --- | --- |
| Migraciones aplicadas | 39 |
| Cuentas ficticias | 9 |
| Postulantes | 72 |
| Materias | 4 |
| Preguntas | 125 |
| Plantillas | 13 |
| Evaluaciones aplicadas | 200 |
| Respuestas persistidas | 2200 |
| Rendimientos | 58 |

Una consulta contrastó cada evaluación finalizada con la suma de `puntaje_obtenido_resp` de sus respuestas: **cero diferencias**. Esta comprobación valida carga y cálculo del escenario ficticio, no demuestra que los registros correspondan a personas o exámenes reales.

La instancia temporal fue detenida al terminar las comprobaciones PostgreSQL.

## Protección de datos y publicación

La revisión de archivos versionados no encontró `.env` privados, bases SQLite/SQL, logs ni dependencias generadas. Se comprobó la exclusión de `.env`, variantes del entorno, SQLite, `vendor`, `node_modules` y `public/build`; `.env.example` permanece publicable con secretos vacíos. Una búsqueda de formatos habituales de claves privadas y tokens no encontró coincidencias en archivos versionados. Las cuentas y su contraseña públicas son fixtures demo; la carga queda restringida a `local`/`testing`.

Se conservan los archivos de bloqueo y las auditorías. GitHub confirmó repositorio público, rama predeterminada `main` y acceso para push. La publicación debe realizarse con avance directo y push normal, revalidando el remoto; no se autoriza ni se utiliza force push.

## Límites

No se realizó una auditoría de despliegues externos ni una prueba de correo SMTP real. La suite y las verificaciones de instalación no sustituyen una evaluación de producción. Consulte los [pendientes del estado actual](estado_actual.md) para la habilitación manual, consolidación entre cohortes e instrumentos históricos.
