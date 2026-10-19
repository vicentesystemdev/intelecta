# Corrección del plazo CAT — 19 de octubre de 2026

## Causa y alcance comprobados

PHP (antes y después del bootstrap) y Laravel usan UTC. PostgreSQL usa
`America/La_Paz` (UTC−04:00). El navegador comprobado informa hora de Bolivia,
`getTimezoneOffset() = 240`.

Las columnas CAT `iniciado_en`, `vence_en`, `finalizado_en` y `respondida_en`
son `timestamp with time zone`. Los casts datetime habituales de Eloquent
escribían `Y-m-d H:i:s`, sin offset. PostgreSQL interpretaba la hora UTC como
hora boliviana, desplazando el instante almacenado 14.400 segundos al futuro.

Ejemplo del intento finalizado observado, consultado sin modificarlo:

* `created_at = 2026-10-19 17:21:02` (hora UTC sin zona).
* `iniciado_en = 2026-10-19 17:21:02-04` (equivale a 21:21:02 UTC).
* `vence_en = 2026-10-19 17:51:02-04` (equivale a 21:51:02 UTC).

Aunque entre inicio y plazo almacenados había 1.800 segundos, el vencimiento
quedaba 16.200 segundos después del inicio real: 270 minutos. A los nueve
segundos la interfaz podía mostrar 269:51. React recibía los segundos
equivocados desde Laravel; no era solamente una conversión visual.
`IntentoCatService::vencer` también comparaba contra ese instante desplazado.

## Corrección implementada

* `InstanteCat` lee instantes inmutables normalizados a UTC y escribe fechas
  con microsegundos y offset explícito `+00:00`, independientemente de la
  zona de la sesión PostgreSQL. Solo se aplica a las cuatro fechas CAT.
* El servicio captura un único inicio inmutable y calcula su vencimiento
  añadiendo exactamente los 30 minutos del instrumento. No recalcula el
  plazo al responder, recuperar o recargar.
* El controlador envía `vence_en` y `servidor_ahora` en ISO 8601 UTC con `Z`.
  Sigue publicando los segundos restantes calculados por el servidor.
* React calcula el reloj desde esos dos instantes y descuenta el tiempo
  con `performance.now()`. No depende de la zona ni de ajustes del reloj
  civil del cliente. Fechas ambiguas o inválidas muestran cero.
* Laravel conserva el control autoritativo del vencimiento, antes de aceptar
  respuestas y antes y después del cálculo del motor.

No se cambiaron configuraciones horarias globales, migraciones, tablas
convencionales, FastAPI, parámetros TRI ni resultados. No se ejecutaron
migraciones ni actualizaciones correctivas de datos existentes.

## Preservación del intento existente

Al inspeccionar PostgreSQL había un solo intento, finalizado con diez
respuestas, y ningún intento abierto. Se mantuvieron sus fechas históricas,
snapshot y resultados sin reescribirlos. La corrección aplica a nuevas
escrituras; no reinterpreta silenciosamente los registros históricos.

La huella SHA-256 conjunta de todas las filas de `intentos_cat` y
`respuestas_cat`, antes y después de las comprobaciones, coincidió:

`06d8a3bbe05daaa31acd8abdb6c6bf97ca692653a499209449ef2af9fdd85148`

## Verificación ejecutada

* PHPUnit: **93 pruebas satisfactorias, 980 aserciones**, ninguna omitida.
  Incluye toda la suite CAT, integración con FastAPI real y regresión de
  resultados convencionales, acceso estudiantil y módulo de evaluaciones.
* Regresión PostgreSQL real: sesión `America/La_Paz`, escritura y lectura
  de las cuatro fechas, cambio de sesión a UTC y reproducción explícita
  del error anterior de 14.400 segundos. Solo crea tablas temporales, sin
  tocar tablas ni secuencias reales, y revierte la transacción.
* Regresión Laravel: comienzo en UTC/La Paz, plazo de 1.800 segundos,
  1.791 segundos a los nueve segundos, 1.731 tras recarga/recuperación un
  minuto después; respuesta exactamente al vencimiento rechazada sin
  perder la respuesta aceptada previamente.
* Node: **4 pruebas satisfactorias** del reloj en UTC y `America/La_Paz`,
  equivalencia de offsets, recarga, vencimiento, fechas ambiguas y cambios
  del reloj del cliente.
* Navegador de Bolivia, cuenta ficticia y SQLite aislado en puerto 8020,
  con FastAPI real: nuevo intento mostró **29:59**; recarga observada de
  **29:50 a 29:50**, sin reiniciar el plazo. Evidencia local privada:
  `storage/app/private/cat/reloj-corregido-recarga.jpg`.
* `npm run build`, Laravel Pint y `git diff --check`: satisfactorios.

No se esperaron treinta minutos reales en el navegador: el límite exacto,
las respuestas tardías y la recuperación se verificaron con el reloj
controlado de las pruebas Laravel. La comprobación visual usó el entorno
aislado; en PostgreSQL principal solo se leyeron los datos existentes.

### Repetir las pruebas

Con PHP que tenga `pdo_sqlite`, `pdo_pgsql`, `mbstring`, `openssl` y `curl`,
y con las credenciales locales ya configuradas, establecer únicamente para
el proceso de prueba:

```powershell
$env:CAT_REAL_TESTS='1'
$env:CAT_PGSQL_CLOCK_TESTS='1'
$env:CAT_PGSQL_DATABASE='intelecta'
php vendor/phpunit/phpunit/phpunit tests/Feature/Cat tests/Feature/Resultados/ResultadosTrazablesTest.php tests/Feature/Portal/StableStudentAccessTest.php tests/Feature/Evaluaciones/EvaluacionesModuleTest.php --do-not-cache-result
node --test tests/cat-clock.test.mjs
npm run build
```

FastAPI debe estar disponible en la URL configurada. El secreto permanece
en el entorno local y no se imprime ni forma parte de los ejemplos.

## Archivos de esta corrección

1. `app/Domains/Cat/Casts/InstanteCat.php`
2. `app/Domains/Cat/Models/IntentoCat.php`
3. `app/Domains/Cat/Models/RespuestaCat.php`
4. `app/Domains/Cat/Services/IntentoCatService.php`
5. `app/Http/Controllers/Estudiante/CatDemoController.php`
6. `resources/js/Pages/Estudiante/CatDemo.jsx`
7. `resources/js/Utils/relojCat.mjs`
8. `tests/Feature/Cat/CatDemoTest.php`
9. `tests/Feature/Cat/CatPostgresClockTest.php`
10. `tests/cat-clock.test.mjs`
11. `docs/integracion-cat/CORRECCION_PLAZO_CAT.md`

Sin bloqueos técnicos pendientes para esta corrección. Los resultados
anteriores y los archivos ajenos a este cambio se preservan. Sin push.
