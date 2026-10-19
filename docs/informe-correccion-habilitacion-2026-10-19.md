# INTELECTA: corrección de acceso y auditoría técnica

> Informe histórico de diagnóstico. La recuperación de identidad se completó después: ver `docs/informe-final-regularizacion-demo-2026-10-19.md` y `docs/guia-credenciales-demo-defensa.md` para el estado vigente.

Fecha de revisión: 19/10/2026, Bolivia. Entrega prevista: 20/10/2026, 20:00.

## 1. Protección, entorno y alcance

El repositorio comenzó limpio en `main`, HEAD `e1aef62bf522f4799850427dcb5151fff013561d`. Se creó `codex/corregir-elegibilidad-evaluaciones`. No se prepararon cambios, commits ni publicaciones.

Stack comprobado: PHP 8.3.33, Laravel 13.16.1, PostgreSQL, React 18, Inertia 2, Vite 8.0.16, Node 22.23.2, Spatie Permission 8 y PHPUnit 12. Arquitectura modular existente: Academico, Evaluaciones, Institucional, Postulantes, Resultados, Reportes y Seguridad; LearningAnalytics mantiene alcance conceptual.

`composer check-platform-reqs` pasó. PDO PostgreSQL y SQLite están disponibles. Se enumeraron 131 rutas. La configuración local apunta a PostgreSQL `intelecta`, `127.0.0.1:5432`.

**Ampliación con la base real:** inicialmente PostgreSQL rechazó la conexión. El usuario inició Laragon e identificó a Diego y Mariana. Después se leyó `intelecta` con transacciones `READ ONLY`, comprobando `transaction_read_only=on`. Hay 72 postulantes, 72 inscripciones, 72 matrículas y 72 habilitaciones; ningún expediente tiene `user_id`. Hay 69 postulantes activos y 3 inactivos. Las habilitaciones se distribuyen en 49 habilitadas, 15 observadas con acceso y 8 restringidas por cuota vencida. No se encontraron diferencias entre sus banderas y el cálculo financiero automático existente, ni referencias inexistentes en las dos relaciones inspeccionadas. La migración `2026_09_12_000000_add_block8_capture_invariants` sigue pendiente; no se aplicó. No se modificaron datos, credenciales ni `.env`.

Se utilizó SQLite en memoria y un servidor PostgreSQL temporal independiente, puerto 55439, base `intelecta_isolated_access`. Las fixtures, migraciones de pruebas y reproducciones se limitaron a esos entornos. No se ejecutaron `migrate:fresh`, `db:wipe` ni seeders contra la base principal. El servidor temporal quedó detenido al finalizar. La revisión automática bloqueó el comando combinado que incluía borrar su directorio; no proporcionó un motivo más específico. Se conservaron los archivos de pruebas en `C:\Users\vicen\AppData\Local\Temp\intelecta-access-pg-98a933a33ccf411289a6c06b0b7edcbd`, sin servidor ejecutándose.

## 2. Causa raíz y reproducción antes de corregir

### Causa confirmada de los dos casos comunicados

| Persona y cuenta de acceso | Expediente | Estado administrativo real | Bloqueo actual del portal |
| --- | --- | --- | --- |
| Diego Mamani Flores, `user_id=8`, `diego.mamani@postulante.avalancha.edu.bo` | `id_post=2`, `user_id=NULL` | Matrícula activa, habilitación observada pero evaluaciones/simulacros permitidos, ninguna cuota vencida | 403 por ausencia de vínculo de identidad |
| Mariana Quispe Rojas, `user_id=9`, `mariana.quispe@postulante.avalancha.edu.bo` | `id_post=3`, `user_id=NULL` | Matrícula observada, una cuota vencida, evaluaciones/simulacros restringidos | Primero 403 por ausencia de vínculo; después del vínculo debe mantenerse la restricción económica |

Ambas cuentas están activas, verificadas y tienen rol Estudiante. Se invocó el middleware real `EnsureStudentAccess` con esas cuentas dentro de la transacción de solo lectura: ambas devolvieron 403 y «Tu cuenta todavía no está vinculada a un expediente académico disponible. Consulta con administración académica.» El fallo local demostrado ocurre **antes de la autorización financiera**, en la relación `User::postulante()` por FK. La coincidencia de correo se utilizó para localizar candidatos solicitados por el usuario, no para vincularlos automáticamente.

La recuperación concreta está preparada mediante el mecanismo existente `intelecta:vincular-postulante`: cuenta 8 ↔ expediente 2 y cuenta 9 ↔ expediente 3, con actor TI Super Administrador 1 (Adriana Choque Mamani), motivo explícito y bitácora. **Esos vínculos no se escribieron: falta la confirmación humana exigida por el comando de identidad.** No hace falta crear cuentas ni cambiar roles. Vincular a Mariana no autoriza a cancelar su deuda ni a habilitarla.

#### Cotejo individual previo a la recuperación final

La fase final solicitada por el usuario exige presentar estos datos antes de cualquier escritura en la base real. Se repitió la consulta en una transacción `READ ONLY` y se obtuvieron:

| Cuenta | Nombre completo en cuenta y expediente | Documento del expediente (enmascarado) | Referencia administrativa | Vínculo propuesto |
| --- | --- | --- | --- | --- |
| 8 | Diego Mamani Flores; coincidencia exacta normalizada | Termina en `0001`, único en la base | Inscripción 2, matrícula `AVA-2026-0002`, gestión 2026 | Cuenta 8 → expediente 2 |
| 9 | Mariana Quispe Rojas; coincidencia exacta normalizada | Termina en `0002`, único en la base | Inscripción 3, matrícula `AVA-2026-0003`, gestión 2026 | Cuenta 9 → expediente 3 |

Ambos corresponden al programa `PF-UMSA-I-2026` y grupo `PF-ING-MA-A`; los expedientes no están archivados. No hay otros expedientes con esos nombres completos ni vínculos existentes para las cuentas, incluyendo expedientes archivados. Las dos cuentas tienen exclusivamente rol Estudiante. El actor 1, Adriana Choque Mamani, es Super Administrador activo y con correo verificado.

**Límite del cotejo documental:** `users` solo almacena `id`, `name` y `email` como identificadores; no contiene CI ni código institucional. Por tanto, no se afirmó una comparación independiente del documento entre cuenta y expediente. La correspondencia documental definitiva debe confirmarla el responsable humano; la coincidencia de correo no se utiliza como autorización.

Se capturaron huellas previas para comprobar después de la vinculación que ningún registro económico ni asignación de permisos cambió:

| Conjunto completo, incluyendo registros archivados | Filas | Huella MD5 del JSON de todas sus columnas ordenado por PK |
| --- | --- | --- |
| Matrículas | 72 | `fbf30f264d1b5fd0da77aaf565d0dfac` |
| Cuotas, incluidos montos, estados y fechas de pago | 180 | `a398ffc1ca109492ea8dd768a95d0e1a` |
| Habilitaciones | 72 | `39cab9d1833df782c456460b3113dae2` |
| Roles asignados a modelos | 9 | `789eccc46f471415c538dd6a21993a8d` |
| Permisos de roles | 143 | `0a2f8e1fef0327974c1f705717a0e982` |

Estado de esta comprobación: **72 de 72 expedientes siguen sin vínculo; ninguna vinculación ejecutada aún.** Si se confirman y ejecutan únicamente los dos casos, el resultado esperado es 70 sin vínculo, sujeto a recuento posterior real. El commit solicitado para la fase final queda pendiente de completar la recuperación y sus verificaciones.

### Defectos de autorización secundarios reproducidos en aislamiento

La selección anterior, en `EvaluacionAplicadaController::habilitacion()`, era esencialmente:

```php
HabilitacionAcademica::where('id_post', $postulante->id_post)
    ->latest('id_hab')->first();
```

El último ID no acredita la inscripción vigente ni identifica el programa/grupo de un simulacro. Tampoco se consideraban `fecha_inicio_hab` y `fecha_fin_hab`. La ficha administrativa, por su parte, presenta la habilitación asociada a una matrícula concreta. Por ello podía mostrar acceso habilitado mientras el portal aplicaba una restricción de otro contexto.

Se añadieron tres regresiones y se ejecutaron **antes** de modificar producción: 3 pruebas, 3 fallos. Evidencia:

| Escenario reproducido | Comportamiento anterior |
| --- | --- |
| Inscripción actual habilitada y registro restrictivo de una inscripción inactiva con ID mayor | El portal devolvió `habilitado_evaluaciones_hab=false` |
| Simulacro del programa habilitado y restricción posterior de otro programa | POST devolvió 422, error `habilitacion` |
| Habilitación vigente y restricción temporal terminada ayer con ID mayor | POST devolvió 422, error `habilitacion` |

Estos tres defectos son reproducibles, pero no explican por sí solos los casos locales: la base leída tiene una habilitación por postulante y el middleware bloquea antes por el vínculo ausente. Se distingue esta evidencia de la hipótesis inicial para no atribuir a cuotas un fallo de identidad.

### Compatibilidad validada contra los datos locales

Las 72 habilitaciones contienen fechas históricas. Ignorar un registro vencido sin un reemplazo vigente podría eliminar una restricción financiera al tratarlo como ausencia de habilitación. Se añadió una regresión específica: matrícula con cuota vencida, habilitación expirada y ningún reemplazo; la primera versión devolvió 302 en vez de 422. Se corrigió preservando el último estado del contexto cuando no existe sustitución vigente. No se concede acceso nuevo por el mero paso del tiempo.

Se comparó en solo lectura la decisión administrativa anterior y la nueva para los 72 expedientes: **62 permitidos, 10 denegados y cero diferencias**. Esto no significa que esas 62 cuentas puedan iniciar sesión en el portal: el vínculo de identidad sigue siendo necesario. Diego conserva permiso administrativo y Mariana conserva su restricción.

## 3. Corrección aplicada y archivos

| Archivo | Cambio y razón |
| --- | --- |
| `app/Domains/Academico/Services/AccesoEvaluacionService.php` | Centraliza la selección por postulante, vigencia civil de Bolivia e inscripción/programa/grupo activos. Para simulacros limita al contexto correspondiente. Conserva los ajustes globales explícitos y, si no hay reemplazo vigente, el último estado del mismo contexto para no levantar restricciones legacy. |
| `app/Domains/Resultados/Services/EvaluacionAplicadaService.php` | Valida la elegibilidad al iniciar, dentro de la transacción existente. Mantiene el flujo histórico interno ya existente; ese parámetro no es aceptado por el request estudiantil. |
| `app/Http/Controllers/EvaluacionAplicadaController.php` | Elimina la consulta duplicada, presenta la decisión calculada y valida el envío usando el simulacro del intento propio. La propiedad de la evaluación sigue comprobándose desde el usuario autenticado. |
| `resources/js/Pages/Estudiante/Evaluaciones.jsx` | Usa `accesoEvaluaciones.permitido` y el motivo del backend para el aviso y los botones de inicio/envío. |
| `tests/Feature/Resultados/ResultadosTrazablesTest.php` | Diez regresiones nuevas sobre contexto, fechas, administración, estados económicos y vínculo explícito; conserva las pruebas anteriores de resultados y propiedad. |
| Este informe | Evidencia, alcance, pendientes y comandos de verificación. |

Entre habilitaciones aplicables se conserva la prioridad previa por mayor `id_hab`. No se añadió un período académico inexistente ni una regla financiera nueva. El portal sigue exponiendo únicamente estado, motivo y banderas de habilitación; no devuelve observaciones administrativas internas.

## 4. Reglas de matrícula/cuotas preservadas

No se cambiaron montos, pagos, cuotas, matrículas reales, seeders ni migraciones. `HabilitacionAcademicaRepository::syncForMatricula()` conserva sus reglas:

| Condición | Resultado automático existente |
| --- | --- |
| Matrícula inactiva | Evaluaciones/simulacros restringidos |
| Matrícula becada o exenta | Permitidos, incluso con cuotas vencidas, por la precedencia del beneficio existente |
| Matrícula ordinaria con cuota `vencida` | Restringidos |
| Cuota `pendiente` o matrícula `observada` sin vencidas | Estado observado; evaluación permitida |
| Cuotas pagadas/becadas/exentas sin otra restricción | Permitidos |
| Banderas manuales restrictivas vigentes | Se respetan al autorizar el inicio/envío |

**Compatibilidad sin matrícula:** el código y la prueba preexistentes permiten evaluación libre a un postulante activo sin habilitación aplicable; no se inventó obligatoriedad global de matrícula. El simulacro conserva la inscripción activa y coincidente que exige `EvaluacionAplicadaService`. Si se desea hacer obligatoria la matrícula para toda evaluación, hace falta una decisión de negocio independiente.

Las fechas de habilitación se interpretan como límites inclusivos en `America/La_Paz`; una fecha nula representa un extremo abierto. La preferencia por un registro vigente no significa que una deuda desaparezca al caducar su fecha: sin reemplazo vigente se conserva la decisión registrada del contexto. Los booleanos siguen siendo casts Eloquent, no comparaciones con textos de presentación.

## 5. Verificación ejecutada

| Verificación | Resultado real |
| --- | --- |
| Reproducción previa de los tres bloqueos | 3/3 fallaron antes del arreglo |
| Mismas tres regresiones después | 3/3 aprobaron, 15 aserciones |
| Resultados y acceso, SQLite | 18/18 pruebas, 222 aserciones |
| Ampliación con deuda legacy e identidad explícita, SQLite | 30/30 pruebas, 278 aserciones: ResultadosTrazables + StableIdentity |
| Misma ampliación de identidad/deuda legacy, PostgreSQL | 30/30 pruebas, 278 aserciones; fixtures históricas con intervalos válidos para los CHECK reales |
| Resultados + integridad académica + portal, PostgreSQL independiente | 32/32 pruebas, 367 aserciones |
| Grupo PostgreSQL concurrente explícito | 4/4 pruebas, 28 aserciones; inscripción/simulacro, cupos, matrícula y asistencia |
| Suite PHPUnit completa, SQLite, tras ampliar la comprobación de datos reales | 420 pruebas: 416 aprobadas, 3220 aserciones, 4 omitidas de concurrencia PostgreSQL; las cuatro aprobaron en su ejecución explícita |
| Node, validaciones frontend existentes | 20/20 |
| Renderizado aislado del componente React real | 2/2 escenarios: botón disponible con permiso y bloqueado con motivo cuando se deniega |
| Build de producción | Correcto, 3451 módulos |
| Requisitos de plataforma Composer | Correctos |
| Estilo y whitespace | Pint sobre archivos modificados y servicio nuevo, y `git diff --check`: correctos |

Los escenarios de pago ejercitan los endpoints administrativos: pendiente, vencida, pagada, becada y exenta; comprueban autorización del siguiente request y conservación del monto. Se probó restricción/rehabilitación manual tanto para iniciar como para enviar. La simulación del estudiante con permiso en otro programa siguió denegada en el programa restringido. Los campos falsificados `estado_hab`, `habilitado_evaluaciones_hab` e `historica` enviados desde el cliente no alteraron la autorización.

Las pruebas de propiedad existentes verifican que un estudiante no puede consultar ni enviar la evaluación de otro, incluso falsificando `id_post`. La nueva reproducción de identidad comprueba que una coincidencia de correo sin FK devuelve 403, que el vínculo explícito auditado recupera el inicio y que una cuota vencida sigue devolviendo 422 después. El renderizado React usa dependencias visuales aisladas; no se crearon intentos de evaluación en los expedientes reales.

## 6. Auditoría de módulos y pendientes

La revisión combina lectura de las rutas, requests, middleware, modelos y servicios con la regresión automatizada. No equivale a certificar producción ni a un pentest exhaustivo.

| Módulo | Estado y límites comprobados |
| --- | --- |
| Autenticación y autorización | Sesiones, activación, correo verificado, RBAC y ámbito docente implementados. No hay registro público. Middleware estudiantil usa `user_id`, sin fallback por correo. |
| Postulantes | Mecanismo de identidad explícita implementado, pero los 72 expedientes locales carecen de vínculo. Causa confirmada de los 403 de Diego/Mariana; requiere conciliación humana, no búsqueda automática por correo. |
| Matrículas/cuotas | Reglas financieras preservadas y probadas. Existe el pendiente ALTO A1 de ajuste manual frente a sincronización automática. |
| Gestión académica/asignaturas | Programas, grupos, cupos, inscripciones y contexto con validaciones y pruebas concurrentes. No se modificó su arquitectura. |
| Banco y plantillas | Valida pertenencia de taxonomía, estados, cinco alternativas para opción múltiple y una correcta. Pendiente MEDIO M1 de versión inmutable del instrumento. |
| Evaluaciones | Bloqueo por contexto/vigencia corregido. Propiedad, preguntas y respuestas se validan en backend. Pendiente ALTO A2 sobre plazo del intento. |
| Resultados/reportes | Calificación persistida y exportaciones operativas. Pendiente ALTO A3 de consolidación entre programas. Los informes descriptivos no son un motor adaptativo. |
| Base de datos | Migraciones y restricciones ejercitadas en PostgreSQL temporal. Lectura principal: relaciones inspeccionadas sin referencias inexistentes; identidades sin conciliar y migración Bloque 8 pendiente. Ninguna escritura principal. |
| Rutas/endpoints | 131 rutas enumeradas; administrativos protegidos por rol/permiso y portal por cuenta/identidad. No existe todavía API de integración con LAB. |
| Errores/validaciones | Form Requests normalizan y validan; errores de autorización académica regresan como 422. Portal presenta la misma decisión usada por el backend. |
| Learning Analytics/configuración/portadas | Hay pantallas estáticas o de alcance parcial, declaradas por el proyecto; no deben presentarse como analítica predictiva validada. |

### Hallazgos críticos y altos pendientes

**C0 — CRÍTICO local: expedientes sin vínculo con las cuentas estudiantiles.** Acreditado en los 72 registros y reproducido con cuentas 8/9 mediante el middleware. Está preparada la recuperación de los dos casos indicados por el usuario; requiere confirmar las correspondencias explícitas. No se debe ejecutar un seeder ni emparejar masivamente por correo para resolverlo. Mariana debe conservar su restricción por cuota vencida.

**A1 — ALTO: sincronización de cuotas sobrescribe decisiones manuales.** `HabilitacionAcademicaRepository::syncForMatricula()` hace `updateOrCreate` sobre el mismo registro administrado manualmente. Reproducción transaccional en PostgreSQL: matrícula activa, suspensión manual con bandera `false`, agregar una cuota pendiente mediante el servicio; la bandera terminó `true` y el motivo pasó a seguimiento preventivo. También puede sustituir una autorización manual por una restricción derivada de vencimientos. No se rediseñó este modelo durante la auditoría.

**A2 — ALTO: duración de evaluación sin vencimiento autoritativo en backend.** `portalEvaluation()` calcula segundos restantes para React; `enviar()` no valida una fecha límite. Reproducción aislada: plantilla de 30 minutos, intento iniciado hace 120 minutos, envío con tiempo declarado de 1 segundo; se aceptó y quedó `finalizada`. Esto afecta integridad evaluativa; no se modificó el flujo de tiempos durante esta auditoría.

**A3 — ALTO: consolidación de rendimiento sin separar todos los contextos.** `ResultadoAcademicoRepository::updatePerformance()` reúne todas las evaluaciones finalizadas del postulante y las asigna a su inscripción más reciente. Con varias cohortes/programas puede producir promedios/rankings atribuidos al contexto equivocado. Evidencia de lectura de código, sin certificar que existan casos en la población real.

**D1 — Dependencias, prioridad ALTA antes de publicación:** `npm audit` informó 28 paquetes afectados: 3 críticos, 20 altos, 4 moderados y 1 bajo. Los críticos incluyen `shell-quote`, `proxy-addr` y la propagación a `concurrently`. La severidad del catálogo no demuestra que una ruta Laravel sea explotable: parte del árbol corresponde a CLI/desarrollo. `composer audit` también detectó avisos en Guzzle, CommonMark, Laravel, Dompdf, Laravel Excel, PHPSpreadsheet, Flysystem, PSR-7 y Symfony HttpFoundation. Los avisos se consultaron en esta ejecución; no se alteraron los lockfiles ni se ejecutó `audit fix`.

Referencias devueltas por las herramientas: [shell-quote](https://github.com/advisories/GHSA-w7jw-789q-3m8p), [proxy-addr](https://github.com/advisories/GHSA-jqcg-44mw-7w3h), [Guzzle](https://github.com/advisories/GHSA-v5mv-p594-2x33), [CommonMark](https://github.com/advisories/GHSA-3q6v-r5mr-hxv8), [Laravel Excel](https://github.com/advisories/GHSA-c7r6-vx3h-w5g2), [Symfony HttpFoundation](https://symfony.com/cve-2026-108082). Son avisos de dependencias, no explotaciones demostradas en INTELECTA.

Además de C0, no se demostró otro fallo funcional CRÍTICO en los flujos probados. Los avisos críticos de npm permanecen como riesgo de dependencias pendiente de evaluación y actualización controlada.

### Hallazgos medios y bajos

- **M1 — MEDIO:** no hay snapshot inmutable/versionado de preguntas y alternativas por intento. Cambios posteriores de texto o clave pueden afectar consistencia histórica; se validan estados/puntajes, pero no todas las modificaciones de contenido.
- **M2 — MEDIO:** la habilitación efectiva para evaluar no sustituye los indicadores descriptivos de matrículas/habilitaciones. Con varias inscripciones conviene mostrar el contexto y la vigencia del registro consultado; aquí solo se corrigió la decisión del portal.
- **B1 — BAJO:** completar pantallas conceptuales o dejarlas explícitamente fuera del recorrido operativo de entrega.

## 7. Condiciones para integrar INTELECTA LAB

No hay cliente FastAPI ni contrato API dedicado, endpoint de sesión CAT, autenticación de servicio ni política de reintentos/idempotencia para esa integración. La presencia de Sanctum en Composer no implementa esas capacidades por sí sola. No se tocaron TRI 3PL, EAP ni CAT.

Antes de conectar un motor externo se necesitan: contrato versionado con identidad/contexto e ID de intento; autorización Laravel reutilizando el servicio de acceso; plazo decidido en servidor; respuestas y resultados idempotentes; límites de timeout y manejo de indisponibilidad; versión trazable del banco. Debe resolverse además la atribución de resultados por programa y revisarse las dependencias del cliente HTTP. Son requisitos de integración futuros, no cambios realizados en esta entrega.

## 8. Siguientes tres tareas, por prioridad

1. **Confirmar y completar los vínculos de identidad pendientes (C0), manteniendo la restricción de Mariana:** Diego cuenta 8 ↔ expediente 2; Mariana cuenta 9 ↔ expediente 3. Después separar la decisión manual de la derivada de cuotas (A1), con precedencia e historial explícitos.
2. **Cerrar integridad de la evaluación antes de LAB:** plazo autoritativo en servidor (A2), instrumento versionado (M1) y resultados por contexto (A3), con reproducciones y pruebas aisladas.
3. **Preparar una integración segura:** actualización controlada de dependencias (D1), contrato Laravel–FastAPI, autenticación, idempotencia y validación de indisponibilidad. No exponer públicamente el entorno local de demostración como producción.

## 9. Comandos para verificar localmente

Desde `C:\laragon\www\intelecta`, con PHP, Composer y Node disponibles:

```powershell
git status --short
git diff --check
composer check-platform-reqs

# Solo esta consola/proceso; no edita .env.
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
$env:DB_URL = ''
$env:BCRYPT_ROUNDS = '4'
$env:LOG_CHANNEL = 'null'
php vendor/phpunit/phpunit/phpunit --do-not-cache-result tests/Feature/Resultados/ResultadosTrazablesTest.php
php vendor/phpunit/phpunit/phpunit --do-not-cache-result
node --test tests/input-validation.test.mjs
npm run build
vendor/bin/pint --dirty --test
composer audit
npm audit
```

Para PostgreSQL, usar exclusivamente una base nueva y desechable y definir en esa consola `DB_CONNECTION=pgsql`, host, puerto, base, usuario y contraseña. Las pruebas con RefreshDatabase reconstruyen **esa base de pruebas**; nunca deben recibir el nombre `intelecta`. El grupo concurrente requiere previamente el esquema de pruebas y `RUN_POSTGRES_CONCURRENCY=1`:

```powershell
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --group=postgres-concurrency
```

La corrección de autorización no necesita migraciones ni regularizar pagos. La recuperación de las dos cuentas sí requiere completar sus vínculos tras la confirmación humana. El procedimiento existente, todavía no ejecutado sobre `intelecta`, es:

```powershell
php artisan intelecta:vincular-postulante 8 2 --actor=ID_SA_EXPRESAMENTE_AUTORIZADO --motivo="Correspondencia confirmada expresamente por el responsable de la instalación"
php artisan intelecta:vincular-postulante 9 3 --actor=ID_SA_EXPRESAMENTE_AUTORIZADO --motivo="Correspondencia confirmada expresamente por el responsable de la instalación"
```

Cada comando muestra los IDs/base y exige confirmación interactiva; la Action registra bitácora y bloquea reemplazos. La auditoría previa no hizo commit ni push. En la fase final el usuario autorizó un commit descriptivo en `codex/corregir-elegibilidad-evaluaciones` después de recuperar y verificar el acceso; todavía no se ha realizado. No se fusionará a `main` ni se hará push.

## 10. Conciliación administrativa auditable del resto de expedientes

**Actualización de alcance:** la solicitud posterior exige auditoría de toda la población. El estado vigente y la propuesta de tres pares documentados (incluida Valeria) están en `docs/conciliacion-global-identidades-2026-10-19.md`. No se ha designado un actor por defecto ni se ha autorizado todavía ninguna escritura principal. El mecanismo de lote reutiliza la misma acción auditada del comando individual con aprobación, dry-run y rollback íntegro.

1. Extraer un inventario de solo lectura de expedientes sin vínculo y cuentas estudiantiles sin expediente, incluyendo los vínculos archivados reservados. Los correos y nombres sirven para localizar candidatos, nunca para confirmarlos automáticamente.
2. Un responsable autorizado coteja individualmente nombre completo, documento de inscripción y referencia institucional/matrícula con el titular de la cuenta. Registra la referencia de esa revisión en un acta o ticket; no incorpora documentos completos ni datos sensibles innecesarios a la bitácora.
3. Verificar que cuenta y expediente existen, no están vinculados, que el expediente no está archivado y que los roles son compatibles. Un conflicto, homónimo o expediente archivado requiere resolución humana; no se reemplaza la FK.
4. Presentar al responsable los IDs exactos y obtener confirmación explícita para cada correspondencia. Ejecutar solamente `intelecta:vincular-postulante`, con actor TI/SA autorizado y motivo que referencie el acta o ticket.
5. Comprobar la entrada `vincular_identidad`, ambos extremos del vínculo y el acceso del portal. Comparar huellas de cuotas/matrículas/habilitaciones antes y después; un vínculo de identidad no acredita pago ni elimina restricciones financieras.
6. Confirmar que un intento repetido es rechazado sin sobrescritura. Actualizar el inventario y recuento de pendientes. No ejecutar seeders ni vinculaciones masivas automáticas para esta conciliación.
