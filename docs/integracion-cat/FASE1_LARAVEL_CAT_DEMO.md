# Fase 1 Laravel: Matemática CAT DEMO

Implementación autorizada el 19/10/2026 en `feature/integracion-cat-lab`. Duración confirmada: **30 minutos**. Configuración: minimum=8, maximum=10, threshold=0.45, D=1. El contrato HTTP definitivo y el cliente FastAPI pertenecen a la segunda fase.

## Estado entregado

- Aplicada únicamente `database/migrations/2026_10_19_000000_create_cat_demo_tables.php` tras revisar su SQL con `migrate --pretend --path=...`.
- Ejecutado `php artisan intelecta:preparar-cat-demo`: instrumento `id_instrumento_cat=1`, código `MAT-CAT-DEMO-v1`, nombre Matemática CAT DEMO. El comando es repetible y no actualiza el instrumento existente.
- Pool exclusivamente `preguntas.id_preg=1–16`, sin duplicar ni editar enunciados, alternativas, claves o plantillas convencionales.
- Snapshot por intento, respuestas incrementales, revisión, replay, vencimiento, recuperación, página React/Inertia e historial CAT independientes.
- Puerto PHP `MotorCat` preparado; la implementación predeterminada `MotorCatPendiente` declara indisponibilidad. **No hay cliente HTTP, cálculo TRI en PHP ni motor simulado habilitado en la aplicación.** La UI puede conservar un intento pendiente; todavía no puede administrar una evaluación CAT real hasta conectar FastAPI.
- `Tests\Support\MotorCatSimulado` existe solo bajo `tests/`; su orden secuencial y sus métricas son dobles para comprobar el flujo, no selección adaptativa ni estimación psicométrica.

## Banco y parámetros simulados

La revisión de contenido identificó 16 ejercicios independientes entre 40 filas MAT. Las filas 17–40 repiten problemas con prefijos y alternativas reordenadas; no forman parte del instrumento. El inventario estructural previo no detectaba esa equivalencia.

La asociación DEMO guarda parámetros en `instrumento_cat_preguntas`, **sin añadir a/b/c a preguntas**. Versión: `simulados-demo-v1`. Origen explícito: `DEMO simulado determinista v1; sin calibración empírica`.

Para id_preg n entre 1 y 16:

- a = 1.0 + ((n−1) módulo 4) × 0.2.
- b = −2 + (n−1) × 4/15, redondeado a seis decimales.
- c = 0.15, valor experimental fijo.

Este diseño sintético no usa etiquetas de dificultad académica, número de alternativas ni respuestas observadas. No acredita calibración, orden real de dificultad, validez de una escala o uso para decisiones académicas. Cambiar el manifiesto exige publicar otra versión; el comando actual nunca reconfigura la versión publicada.

## Persistencia y garantías

| Tabla nueva | Función |
|---|---|
| `instrumentos_cat` | Identidad, versión de instrumento/parámetros, configuración y duración |
| `instrumento_cat_preguntas` | Relación con IDs originales y parámetros DEMO |
| `intentos_cat` | `id_intento_cat`, propietario `id_post`, snapshot privado, huella, inicio/vencimiento, revisión, ítem emitido, cálculo pendiente y resultado experimental |
| `respuestas_cat` | ID original de pregunta/alternativa del snapshot, corrección Laravel, revisión/orden, instante de aceptación y métricas por iteración |

Las respuestas no tienen FK a `alternativas`: el sistema convencional las borra/recrea al editar preguntas. Su identidad y clave se conservan en el snapshot. Las nuevas FK de instrumento/postulante/pregunta usan restrict al borrado físico; no impiden los cambios de estado o borrados lógicos ya existentes.

El snapshot contiene texto, clasificación, alternativas públicas y claves privadas, además de configuración y parámetros. Se copia al iniciar y nunca se reconstruye desde los modelos originales para corregir un intento existente. Su huella SHA-256 ordena recursivamente las claves de objetos, conserva el orden de listas y codifica JSON Unicode sin escapes. El snapshot está oculto a la serialización del modelo y el controlador proyecta explícitamente solo el ítem emitido, sin claves ni parámetros.

Eloquent rechaza cambios de snapshot, huella, propietario, instrumento, inicio y plazo; PostgreSQL añade el trigger `intentos_cat_inmutable` para imponer la misma regla incluso mediante SQL directo. La primera decisión válida fija la versión del motor, que también queda inmutable. Los resultados y revisiones siguen siendo actualizables por el servicio.

Hay un índice parcial único por postulante/instrumento mientras estado=en_progreso y restricciones únicas de respuesta por intento/pregunta e intento/revisión. Inicio bloquea el postulante para coordinar dobles peticiones; cada aceptación y aplicación de cálculo bloquea la cabecera del intento. Una revisión ya aceptada con los mismos IDs se reproduce sin insertar otra fila; cambiar la alternativa de esa revisión devuelve 409.

No se escriben `evaluaciones_aplicadas`, `respuestas_evaluacion` ni `rendimientos_postulante`. Se muestran aciertos observados y estimación CAT experimental separados; no se traduce theta a porcentaje o nota convencional.

## Flujo y rutas propias

Todas las rutas pertenecen al middleware web y exigen auth, verified y `EnsureStudentAccess`; conservan la protección CSRF del flujo Laravel. Se reutiliza `AccesoEvaluacionService`, con identidad tomada exclusivamente de la sesión. La habilitación se comprueba al iniciar, responder y reintentar el cálculo. El GET propio puede mostrar el estado aunque una restricción posterior oculte la pregunta y bloquee la continuación.

| Método y ruta | Operación |
|---|---|
| GET `/estudiante/cat` | Instrumentos e historial propio |
| POST `/estudiante/cat/instrumentos/{instrumentoCat}/iniciar` | Crear o continuar intento abierto |
| GET `/estudiante/cat/intentos/{intentoCat}` | Recuperar estado persistido y una pregunta emitida |
| POST `/estudiante/cat/intentos/{intentoCat}/responder` | Aceptar `id_preg`, `id_alt`, `revision`; corrección desde snapshot |
| POST `/estudiante/cat/intentos/{intentoCat}/recuperar` | Reintentar cálculo pendiente desde historial guardado |

Propiedad se consulta por `id_post` antes de recuperar el intento; no se hace binding global que revele intentos ajenos. IDs de alternativa se validan contra el snapshot, no con `exists:alternativas`, porque una edición puede haber eliminado la fila original.

Orden de aceptación: validar propietario/habilitación → bloquear intento → comprobar plazo, revisión e ítem emitido → guardar corrección y respuesta → incrementar revisión y dejar cálculo pendiente → confirmar transacción → solicitar cálculo. La llamada al motor ocurre sin mantener bloqueos de BD. El contexto de cálculo se captura bajo lock y la decisión se aplica solo si siguen coincidiendo revisión, estado y condición pendiente.

Si el motor no está disponible, una decisión es inválida o una llamada se interrumpe, la respuesta aceptada permanece y puede reintentarse desde el historial. Una decisión atrasada no sobrescribe una revisión posterior. Solo se acepta un siguiente ID del snapshot no administrado; la finalización exige al menos ocho respuestas y umbral alcanzado o máximo diez. Se rechazan versiones de motor cambiantes, métricas no finitas, ítems desconocidos/repetidos y cierres prematuros.

El vencimiento es servidor, con `now() >= vence_en`. Se comprueba al consultar, aceptar y aplicar decisiones; una respuesta recibida después del plazo no se guarda. Si el motor devuelve después del vencimiento, se conserva la respuesta aceptada a tiempo pero no se emite otro ítem. La indisponibilidad no pausa ni extiende los 30 minutos. El cierre vencido se distingue de una parada CAT válida. No se añadió un proceso periódico de expiración: una fila inactiva se marca vencida cuando se consulta u opera sobre ella, sin permitir por ello nuevas respuestas fuera de plazo.

## Archivos y responsabilidades

- `app/Domains/Cat/Models/`: `InstrumentoCat`, `IntentoCat`, `RespuestaCat`; la cuarta tabla es el pivote de instrumento/pregunta.
- `app/Domains/Cat/Services/PrepararInstrumentoDemo.php`, `app/Console/Commands/PrepararCatDemo.php`: publicación acotada del DEMO.
- `app/Domains/Cat/Services/IntentoCatService.php`: inicio, snapshots, respuestas, revisión, recuperación, vencimiento y validación de decisiones.
- `app/Domains/Cat/Contracts/MotorCat.php`, `DTOs/ContextoCat.php`, `DTOs/DecisionCat.php`: puerto interno PHP, no un esquema JSON de FastAPI.
- `app/Providers/CatServiceProvider.php`, `bootstrap/providers.php`: motor predeterminado indisponible hasta fase 2.
- `app/Http/Controllers/Estudiante/CatDemoController.php`, `app/Http/Requests/Cat/ResponderCatRequest.php`, `routes/cat.php` y su inclusión en `routes/web.php`.
- `resources/js/Pages/Estudiante/CatDemo.jsx`: una pregunta, reloj informativo, confirmación de respuesta, recuperación y resultado separado.
- `resources/js/Components/Estudiante/StudentTrackingNav.jsx`: enlace adicional CAT; no cambia la lógica de evaluación convencional.
- `tests/Feature/Cat/CatDemoTest.php`, `tests/Support/MotorCatSimulado.php`: pruebas aisladas y doble del motor.

## Verificaciones realizadas

- 18 pruebas CAT aisladas, junto con regresiones de `ResultadosTrazablesTest`, `StableStudentAccessTest` y `EvaluacionesModuleTest`: **58 pruebas, 614 aserciones, satisfactorias**. CAT fuerza SQLite en memoria antes de migraciones/fixtures. No se ejecutan esas migraciones o fixtures en la principal.
- Casos: pool exacto, publicación repetible, snapshot tras edición/borrado de alternativas, ausencia de claves/parámetros en payload activo, habilitación posterior, propiedad, usuarios invitados/sin rol/sin vínculo, revisión obsoleta, duplicado exacto/conflictivo, caída y recuperación, frontera de plazo, respuesta tardía del motor, cálculo atrasado frente a otra revisión, versión/ítem de motor inválidos, parada en ocho y diez, aislamiento de promedios y unique de abierto.
- `npm run build`: satisfactorio. No se ha realizado una sesión visual autenticada de navegador ni E2E con Python; corresponden a la siguiente validación integrada.
- SQL PostgreSQL revisado con `--pretend`; aplicación real de la única migración CAT satisfactoria.
- Prueba PostgreSQL con clon TEMPORAL de `intentos_cat` y trigger real: bloqueó snapshot, propietario, vencimiento y cambio de versión fijada; permitió revisión. Todo se descartó con rollback, sin insertar intentos reales ni consumir su secuencia.
- Huellas completas de las filas de **18 tablas académicas** comparadas antes/después de migración y publicación: ninguna cambió. Incluye preguntas, alternativas, taxonomía, plantillas/pivote, evaluaciones/respuestas/rendimientos convencionales, postulantes y contexto de inscripción/matrícula/cuotas/habilitación.
- Instrumento real comprobado con IDs 1–16, configuración acordada, duración 30 y cero intentos reales al terminar la preparación.
- Pint y `git diff --check`; archivos de inventario/auditoría previos conservados fuera de los commits nuevos. No se ejecutó rollback destructivo ni se hizo push.

## Dependencias y fase 2

Esperar `docs/contrato_integracion.md` de Claude. El identificador persistente acordado es **`id_intento_cat`**. El adaptador deberá mapear el puerto interno PHP al contrato final, sin asumir que los nombres internos son nombres HTTP. En particular, el contexto interno contiene un snapshot privado para Laravel: el adaptador debe construir una solicitud mínima, **sin transmitir claves ni alternativas correctas**, con ítems/ parámetros e historial binario según contrato.

Cerrar identificación/versionado de banco y motor, formato de historial/orden, corrección binaria, reconstrucción determinista, decisiones de parada, intervalos, errores y autenticación/timeouts. Fijar D=1, minimum=8, maximum=10 y threshold=0.45. Probar E2E reinicio Python, reintentos, secuencia, fin, vencimiento, payload seguro y aislamiento de resultados. La política de versión puede exigir iniciar un nuevo intento si el motor cambia durante una evaluación; no relajar la inmutabilidad para continuar con otro algoritmo.

Las pruebas actuales demuestran el flujo Laravel con un doble. No acreditan cálculos TRI, información Fisher, selección adaptativa real, exactitud numérica de EAP ni concurrencia real entre workers PostgreSQL. Esas comprobaciones quedan pendientes de fase 2 y deben informarse como tales.
