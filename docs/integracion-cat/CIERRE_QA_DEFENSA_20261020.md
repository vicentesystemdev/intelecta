# Cierre QA — Matemática CAT DEMO — defensa del 20/10/2026

Fecha de verificación: 19/10/2026. Rama: `feature/integracion-cat-lab`.
Alcance de este cierre: DEF-CAT-01 y DEF-CAT-02 autorizados por el usuario.
La ausencia de bloqueos P0/P1 corresponde al informe de Antigravity comunicado
por el usuario; no se presenta como una auditoría adicional de todo el sistema.

## DEF-CAT-01: actualización del estado al vencer

`CatDemo.jsx` consulta la página actual mediante `router.reload`, pidiendo
únicamente `intento` y `acceso`. Usa la ruta GET existente del propietario:
`CatDemoController::show` → `IntentoCatService::consultar` → `vencer`.
Laravel determina el estado; React no declara unilateralmente el intento vencido.

* Se envía una sola consulta automática por intento durante la vida del
  componente; una referencia conserva los identificadores ya consultados.
* La consulta espera a que termine cualquier POST iniciado desde la página,
  para no interrumpir respuestas ni cálculos en curso. Si la respuesta devuelve
  un estado final, no se consulta de nuevo por vencimiento.
* No se usan las rutas de inicio, respuesta o recuperación para vencer.
  No se reenvían respuestas, no se cambian revisiones y no se renueva el plazo.
* El reloj redondea hacia arriba las fracciones restantes: `00:00` se alcanza
  en el límite real, evitando consultar hasta 999 ms antes del vencimiento.
* La nueva proyección del servidor sustituye el estado mostrado. Si la consulta
  falla, no hay reintentos automáticos ni bucle; queda indicada la recarga manual
  como recuperación ante el fallo de comunicación.

No se modificaron el backend productivo ni el contrato de FastAPI para resolver
este defecto. El GET ya aplica el vencimiento incluso con cálculo pendiente,
conservando las respuestas aceptadas y sin invocar al motor.

## DEF-CAT-02: alternativa seleccionada

La selección usa borde de dos píxeles con el color secundario, fondo secundario
al 10 % y texto principal. Las opciones restantes mantienen su fondo de tarjeta.
Un anillo `focus-within` muestra el foco; los radios nativos mantienen el nombre
de grupo, etiquetas, estado `checked` y navegación por flechas. El radio no se
encoge y el texto puede ocupar varias líneas en móvil.

Se comprobó visualmente el diseño móvil sin desbordamiento horizontal, en tema
claro y oscuro. La navegación con flecha abajo cambió la selección de A a B.
No se confirmó una respuesta durante esta comprobación visual.
Evidencias privadas locales:

* `storage/app/private/cat/qa-alternativa-movil.jpg`
* `storage/app/private/cat/qa-alternativa-movil-oscuro.jpg`

## Pruebas y límites de la verificación

* **94 pruebas Laravel satisfactorias, 1.018 aserciones, ninguna omitida**:
  suite CAT completa, FastAPI real en 8010 y regresión de resultados
  convencionales, acceso estudiantil y módulo de evaluaciones.
* La prueba PostgreSQL existente solo usa tablas temporales y rollback;
  no ejecuta migraciones ni escribe en las tablas académicas.
* La nueva prueba Laravel reproduce una recarga parcial real de Inertia al
  vencer con una respuesta aceptada y cálculo pendiente. Dos GET conservan
  la misma respuesta y el plazo, no crean intentos y no llaman al motor.
* **10 pruebas JavaScript satisfactorias**: temporizador UTC/La Paz, fracciones
  de segundo, recarga, cambios de reloj, consulta única, fallo sin bucle,
  espera de POST/cálculo pendiente, preservación del resultado final y selección.
  Ejecutan el componente JSX real con transporte y reloj controlados.
* `npm run build`, Pint y `git diff --check`: satisfactorios.
* Las pruebas de proyección comprueban que no llegan al estudiante el snapshot,
  claves, respuestas privadas ni parámetros TRI; las métricas siguen ocultas
  durante el intento y disponibles solo al propietario después de finalizar.

Comando de regresión PHP, usando un PHP con `pdo_sqlite`, `pdo_pgsql`,
`mbstring`, `openssl` y `curl`, y el servicio local ya configurado:

```powershell
$env:CAT_REAL_TESTS='1'
$env:CAT_PGSQL_CLOCK_TESTS='1'
$env:CAT_PGSQL_DATABASE='intelecta'
php vendor/phpunit/phpunit/phpunit tests/Feature/Cat tests/Feature/Resultados/ResultadosTrazablesTest.php tests/Feature/Portal/StableStudentAccessTest.php tests/Feature/Evaluaciones/EvaluacionesModuleTest.php --do-not-cache-result
node --test tests/cat-clock.test.mjs tests/cat-demo-ui.test.mjs
npm run build
```

El secreto del servicio permanece en el entorno local. No se publica en este
documento, las pruebas ni el respaldo.

## Preservación de datos

Se compararon lecturas de PostgreSQL en transacciones `READ ONLY`, antes y
después de las comprobaciones. Las ocho tablas conservaron cantidades y SHA-256:

| Tabla | Filas | SHA-256 de filas ordenadas |
|---|---:|---|
| instrumentos_cat | 1 | 819400b1efcdaebe993455eac5d5a6f6eaafe9248448f36e2c1c93701f10e0cc |
| instrumento_cat_preguntas | 16 | aeead8a156613017b6fc1067ef34fe4543862a865e27219751d239e530b53a6f |
| intentos_cat | 2 | 2d3650bf2976e31e342c103320421e5afe775be6a7ce91d39bc06be901f51349 |
| respuestas_cat | 20 | bededaa7f8b1488c0dcebe3ab50c3bd979281fdaa298246feaa959f7ac5993b4 |
| preguntas | 125 | 7e2021122497323c1afb1cd1c7d7effcaa594a0b76bcbd1a91b6f553dae23638 |
| alternativas | 625 | 095d15054bae7cabc50b9f2c13c94303d1a8e130642931dc56852387af220fb5 |
| evaluaciones_aplicadas | 208 | 438412ae8653d262302c4f36db0cb66ec7ee0b6e5af67aa99dfd266d67e740cd |
| rendimientos_postulante | 61 | 1753718fba5d0ebb6c6a88f88eb55cf9a63e9b7097a469666e45b9e55e2a8754 |

La consulta ordena `row_to_json(fila)::text`, serializa la lista obtenida y
calcula SHA-256. El detalle está guardado localmente en
`storage/app/private/cat/qa-final-{antes,despues}.json`.

FastAPI, instrumentos, preguntas originales, snapshots, parámetros, algoritmos
y resultados existentes se mantienen sin cambios. No hubo migraciones.
Los fixtures de las pruebas Laravel se crean en SQLite en memoria.
Las comprobaciones visuales usan la cuenta ficticia y SQLite aislado del puerto
8020; no el portal PostgreSQL principal.

## Archivos de este cierre

1. `resources/js/Pages/Estudiante/CatDemo.jsx`
2. `resources/js/Utils/relojCat.mjs`
3. `tests/Feature/Cat/CatDemoTest.php`
4. `tests/cat-clock.test.mjs`
5. `tests/cat-demo-ui.test.mjs`
6. `docs/integracion-cat/CIERRE_QA_DEFENSA_20261020.md`

Se preservan fuera de estos commits los cinco archivos no rastreados que ya
existían al comenzar: el comando de inventario, su prueba y los tres documentos
de contexto/inventario. No se incorporan a Git sin revisar su alcance.

## Respaldo Git local y recuperación

Etiqueta de cierre: `cat-demo-defensa-20261020`.
El commit `86e5b7c` contiene las dos correcciones y sus pruebas; la etiqueta
incluye además este documento y toda la integración previa en la rama.

Respaldo autocontenido:
`storage/app/private/cat/intelecta-cat-demo-defensa-20261020.bundle`.
Su manifiesto lateral `.bundle.json` registra SHA-256 y commit de cierre.
El respaldo se valida con `git bundle verify`, clonación local bare y
`git fsck --full`. No se ejecuta push.

Para recuperar en otro directorio, conservando el checkout actual:

```powershell
git clone -b feature/integracion-cat-lab C:/laragon/www/intelecta/storage/app/private/cat/intelecta-cat-demo-defensa-20261020.bundle C:/laragon/www/intelecta-cat-recuperado
git -C C:/laragon/www/intelecta-cat-recuperado rev-parse cat-demo-defensa-20261020
```

El bundle respalda archivos e historia rastreados. La base de datos, `.env`,
dependencias instaladas, capturas y archivos no rastreados permanecen fuera del
respaldo Git. No sustituye un respaldo de PostgreSQL ni del servicio Python.
Para la defensa deben estar disponibles Laravel, su entorno local y FastAPI
con el secreto correspondiente. Este cierre no cambia la naturaleza
experimental de los parámetros DEMO.
