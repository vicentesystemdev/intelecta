# Conciliación global de identidad y acceso — 19 de octubre de 2026

> Cierre definitivo posterior: `docs/informe-final-regularizacion-demo-2026-10-19.md`. Las 72 cuentas y vínculos ya están verificados; este documento conserva la auditoría inicial.

> Foto histórica previa a autorización. El responsable autorizó después los tres vínculos, que ya se realizaron. Estado vigente, accesos reales y propuesta pendiente de 69 altas: `docs/regularizacion-demo-69-2026-10-19.md`. Las cifras de este informe describen el snapshot anterior, no el estado final actual.

## Estado de entrega y límite de autorización

**Auditoría y dry-run completados; regularización en `intelecta` PENDIENTE de autorización explícita.**
No se vinculó ni creó ninguna cuenta en la base principal. No se atribuyó ninguna operación principal al SA 1. No se ejecutaron migraciones pendientes, seeders, cambios financieros ni cambios CAT/TRI/EAP. No hay commit, merge ni push de esta fase. Este informe sustituye el alcance limitado a Diego y Mariana del informe anterior.

## 1. Inventario real completo

| Concepto | Resultado |
|---|---:|
| Cuentas totales | 9 |
| Cuentas con rol Estudiante | 3 |
| Estudiantes activos y con correo verificado | 3 |
| Expedientes | 72 |
| Expedientes activos / inactivos | 69 / 3 |
| Vínculos FK actuales | 0 |
| Cuentas huérfanas técnicamente (sin FK) | 3 |
| Cuentas sin expediente candidato legítimo documentado | 0 |
| Expedientes sin cuenta asignada | 72 |
| Expedientes sin cuenta candidata existente | 69 |
| A: correspondencia documentada | 3 |
| B: probable pendiente de documentación | 0 |
| C: cuenta sin expediente candidato | 0 |
| D: expediente sin cuenta candidata | 69 |
| E: conflicto o duplicidad detectado | 0 |
| Inscripciones / matrículas / habilitaciones | 72 / 72 / 72 |
| Programas | 4 |
| Evaluación administrativa permitida / denegada | 62 / 10 |

Las 10 denegaciones incorporan el estado del postulante, no solo la bandera de habilitación. Los registros de habilitación son 49 habilitados, 15 observados y 8 restringidos. Sus fechas son históricas: no se extendieron ni se inventaron pagos o vigencias para reparar identidad.

Cuotas: 180, distribuidas en 129 pagadas, 26 pendientes, 8 vencidas, 10 becadas y 7 exentas. Las pagadas suman 74 820,00. No hay tabla independiente `pagos_academicos`: fecha, método y estado de pago residen en `cuotas_academicas`. Se preservaron todas sus filas.

RBAC: 4 roles, 81 permisos, 9 asignaciones de rol, 143 permisos asignados a roles y 0 permisos directos. Los tres estudiantes tienen exclusivamente Estudiante. No se añadieron permisos financieros.

## 2. Evidencia de origen y correspondencia

`DatabaseSeeder` llama a `BaseLimpiaAvalanchaSeeder`. Este recibe `RolesAndUsersSeeder::studentUsers` y asigna explícitamente las cuentas por **índice de fixture**, no por igualdad de IDs. Los índices 0, 1 y 2 están definidos nominalmente en ambos seeders. `seedApplicants()` identifica el expediente por CI sintético `9100000 + índice` y el expediente administrativo usa matrícula `AVA-2026-NNNN`.

Se contrastaron los 72 registros contra nombre, apellidos, CI, correo, celular, turno, gestión, estado y código de matrícula esperados: **72/72 coincidencias**. Los 72 expedientes tienen fecha de creación `2026-09-03 16:15:23`; las tres cuentas se crearon inmediatamente antes. La documentación `docs/usuarios_prueba_avalancha.md` declara estos nombres/correos fixtures exclusivamente ficticios de desarrollo. No se encontró un importador de postulantes ni una bitácora de creación/importación que documente otra procedencia.

Conclusión acotada: la población académica inspeccionada coincide íntegramente con el origen DEMO y las tres relaciones están documentadas en código. No hay un acta histórica de carga que permita probar de forma independiente que jamás hubo importaciones. Esto NO equivale a verificar identidad civil; `users` no contiene documento institucional/CI independiente que cotejar. Se conservan las ediciones administrativas posteriores.

La causa del vínculo ausente es compatible con el diseño actual del seeder: asocia cuentas únicamente si el expediente es **nuevo** (`wasRecentlyCreated`). Una reejecución deliberadamente no rellena las FK de expedientes antiguos. No se ejecutó el seeder para confirmar o corregir esta situación.

## 3. Matriz completa y propuesta sometida a revisión

Archivos privados generados, excluidos de Git:

- `storage/app/private/conciliacion-global-2026-10-19-v2/matriz.md`: las **72 filas**, con todos los campos solicitados.
- `storage/app/private/conciliacion-global-2026-10-19-v2/inventario.json`: información estructurada completa, cuotas por estado y respuesta del middleware para las tres cuentas.
- `storage/app/private/conciliacion-global-2026-10-19-v2/propuesta.json`: solo los tres pares documentados, **revision=pendiente**.
- `propuesta.json.dry-run.json`: comprobante del ensayo, ligado al contenido exacto y a la conexión, válido durante 24 horas.

| Cuenta → expediente propuestos | Nombre | CI DEMO | Matrícula | Habilitación | Decisión administrativa |
|---|---|---|---|---|---|
| 7 → 1 | Valeria Nina Choque | 9100000 | AVA-2026-0001 | Habilitada | Permitida |
| 8 → 2 | Diego Mamani Flores | 9100001 | AVA-2026-0002 | Observada, sin cuota vencida | Permitida |
| 9 → 3 | Mariana Quispe Rojas | 9100002 | AVA-2026-0003 | Restringida, una cuota vencida | Denegada |

Los tres tienen inscripción activa en PF-UMSA-I-2026 y matrícula correspondiente. No se infirió identidad por correo aislado ni por `user_id == id_post`. No se propone vincular los otros 69 expedientes a ninguna cuenta existente.

SHA256 del manifiesto pendiente sometido al dry-run:
`6f36f0b8eaf1705ffd61b8a9c884035a1ce78e3d580d84531b409c34f66e1437`.
Marcar filas como aprobadas cambia su contenido y obliga a repetir el dry-run antes de aplicar.

## 4. Respaldo verificable y PostgreSQL

Respaldo completo custom de PostgreSQL:
`storage/app/private/conciliacion-global-2026-10-19-v1/intelecta-preconciliacion-v2.dump`.

SHA256: `29f79919abe66eb5ea308d9c80844bf993e613ffb0ba50d1f21a2ba7c817fe3f`.
Certificado: el mismo nombre con sufijo `.verified.json`. Contiene destino, fecha, SHA256 y huellas/conteos de las 41 tablas; no contiene la contraseña de conexión. El dump sí contiene todos los datos y hashes de credenciales: custodiarlo como información sensible, nunca publicarlo ni añadirlo a Git.

Se utilizó `pg_export_snapshot()` en una transacción REPEATABLE READ READ ONLY y `pg_dump --snapshot`, por lo que inventario de respaldo y dump comparten snapshot. Se restauró con `pg_restore --exit-on-error --no-owner --no-privileges` en otra instancia local, puerto **55439**, base nueva **intelecta_isolated_identity_20261019_v2**. No se restauró sobre `intelecta` ni se aplicaron migraciones.

**41/41 tablas restauradas coinciden** en conteos y huellas de todas sus filas. La ordenación de huellas usa collation C para ser estable entre instancias. Un primer intento de certificación detectó diferencias de orden por collation; no fue certificado. Se retuvo el artefacto inicial y se produjo un nuevo respaldo con la comparación corregida, sin borrar ni sobrescribir el anterior.

Consulta de integridad sobre principal, en solo lectura:

- 0 referencias huérfanas en postulante→usuario, inscripción→postulante, matrícula→inscripción, habilitación→postulante y cuota→matrícula.
- 0 discrepancias de titular/programa/grupo entre matrícula e inscripción; 0 discrepancias de titular/inscripción entre habilitación y matrícula.
- 0 CI repetidos y 0 `user_id` repetidos no nulos.
- PostgreSQL conserva `UNIQUE(user_id)` y FK `user_id → users.id ON DELETE RESTRICT`, además de `UNIQUE(ci_post)`.
- Tras los ensayos, las **41 tablas de la principal siguen iguales al respaldo**.
- Última migración aplicada: `2026_09_11_000100_finalize_tutor_personal_identity`. La migración posterior permanece pendiente; no fue ejecutada en principal.

## 5. Mecanismo preparado, no ejecutado en principal

1. `intelecta:auditar-identidades --salida=...` recorre todos los expedientes y todas las cuentas Estudiante. En PostgreSQL usa transacción de solo lectura; no llama a login, POST, seeders o sincronización financiera. Exporta matriz, evidencia y propuesta sin aprobarla.
2. `intelecta:respaldar-identidades` genera un dump nuevo y solo lo certifica después de restaurarlo en una instancia local aislada, de puerto distinto, base nueva con prefijo `intelecta_isolated_`. Nunca limpia ni restaura el origen.
3. `intelecta:conciliar-identidades manifiesto.json` es **dry-run por defecto** y registra comprobante privado del contenido exacto. No busca ni empareja personas.
4. `--aplicar` exige ese dry-run reciente, certificado de respaldo de la misma conexión y archivo sin cambios, filas A expresamente aprobadas, actor SA activo/verificado explícito, motivo trazable y confirmación interactiva con hash/destino/actor. No hay actor implícito.
5. Cada fila valida IDs, roles, huellas de datos, estado no archivado, duplicidad y vínculos existentes incluidos archivados. Solo invoca `VincularUsuarioPostulanteAction`, la misma acción auditada del comando individual `intelecta:vincular-postulante`; no actualiza FK por SQL.
6. Todo el lote comparte `CuentaService::transaction`, su lock de identidad y los locks por fila. Si una fila falla, se revierte todo el lote y sus bitácoras. Se exige comprobar la bitácora persistida: si el registrador general falla silenciosamente, también se revierte el lote.
7. Una pareja ya vinculada exactamente es un no-op; nunca sobrescribe un vínculo. Si cambian datos desde la revisión, se rechaza y exige auditar de nuevo.

Los JSON, comprobantes y certificados son artefactos administrativos privados, no firmas digitales. Su custodia y la autorización humana forman parte del procedimiento. No se puede sustituir la revisión por editar una clasificación a A sin evidencia.

## 6. Validación automatizada y acceso al portal

Pruebas focalizadas actuales: `ConciliacionIdentidadCommandTest`, `ConciliacionIdentidadTest`, `StableIdentityTest` y `ResultadosTrazablesTest`: **41/41 aprobadas, 453 aserciones, tanto en SQLite en memoria como en PostgreSQL aislado**. Cubren dry-run sin escrituras, aprobación, actor, certificado/comprobante requeridos, confirmación negativa, cambios de manifiesto, pares ambiguos, duplicados, datos cambiados, archivos, rollback tardío, fallo de auditoría e idempotencia. La prueba global crea la población DEMO solamente dentro de RefreshDatabase, audita los 72 expedientes y comprueba todos los estudiantes existentes, incluyendo el login y los endpoints.

Los tests de consola usan DatabaseMigrations en la base de pruebas, sin la transacción exterior de RefreshDatabase: PostgreSQL exige establecer aislamiento y READ ONLY antes de consultar dentro de una transacción. Se corrigió el aislamiento del fixture de pruebas sin debilitar el modo de solo lectura de los comandos. No se ejecutó la suite completa en esta fase.

Además, `PoblacionEstudiantilRestauradaTest` se ejecutó **sobre la restauración exacta de la base real**: **1/1 aprobada, 76 aserciones**, sin migraciones ni seeders, dentro de una transacción revertida al finalizar. Es opt-in con `INTELECTA_RESTORED_IDENTITY_DB`, exige prefijo aislado y usa únicamente 127.0.0.1:55439. La selección del actor en ese ensayo solo pertenece a la copia transaccional, no autoriza ninguna atribución en principal.

| Cuenta sobre la copia restaurada | Login con credencial DEMO existente | Identidad | Ficha | Ranking | Evaluaciones | Iniciar evaluación |
|---|---|---:|---:|---:|---:|---|
| Valeria, 7 → 1 | OK | 200 | 200 | 200 | 200 | 302, permitida |
| Diego, 8 → 2 | OK | 200 | 200 | 200 | 200 | 302, permitida |
| Mariana, 9 → 3 | OK | 200 | 200 | 200 | 200 | 422, restricción de habilitación |

Se probaron rutas HTTP reales del portal mediante el kernel Laravel de pruebas, con autenticación por `/login`, no saltando el middleware. No es evidencia de una sesión de navegador sobre la principal. Antes del enlace, las tres cuentas responden 403 de identidad; después del ensayo aislado, las tres superan ese control. Mariana conserva el rechazo académico/financiero. Se verificó que el lote no cambia credenciales/roles y que los flujos no cambian finanzas ni migraciones. Los efectos normales de login/logout sobre tokens o rehash en la copia de pruebas se revierten con la transacción.

La principal sigue teniendo **3/3 cuentas bloqueadas por falta de vínculo**. Por ello todavía NO se declara funcional toda la base ni preparada la defensa real; falta autorizar y ejecutar la regularización y repetir estas comprobaciones contra principal.

## 7. Estrategia administrativa para los otros 69 expedientes

No son 69 cuentas rotas: **no existen esas cuentas**. Los seeders solo prevén tres accesos digitales. No hay que emparejarlos con otras cuentas ni ejecutar de nuevo `RolesAndUsersSeeder`, que restablece contraseñas y roles.

Para una defensa inmediata, el conjunto propuesto de las tres cuentas existentes es coherente: Valeria y Diego son casos positivos; Mariana es el caso negativo por deuda. Seleccionar al azar entre los tres puede producir una denegación legítima; si se exige terminar una evaluación, el sorteo debe limitarse al conjunto positivo declarado, no desactivar la restricción de Mariana.

Si se requieren 72 cuentas DEMO, se necesita otra autorización de altas:

1. Confirmar documentalmente el carácter ficticio de la población y aprobar un manifiesto individual de los 69 expedientes, con estado y contexto. Mantener los tres inactivos como negativos, no activarlos artificialmente.
2. Definir identificador de cuenta reproducible por CI/índice DEMO, correo único del fixture y política de credencial/activación DEMO. Verificar colisiones contra TODAS las cuentas y expedientes, incluidos archivados. No reciclar ni resetear cuentas existentes.
3. Usar el servicio de cuentas y el procedimiento de vínculo auditado, con dry-run, SA autorizado, motivo, respaldo e idempotencia; validar previamente la compatibilidad de la política de activación existente. No usar el seeder como herramienta de reparación.
4. No crear cuentas de personas reales bajo una política DEMO. Para datos reales, exigir acta de identidad e invitación normal.
5. Auditar las nuevas cuentas y todas las anteriores, probar cada positiva/negativa según las 62/10 decisiones actuales y conservar las huellas financieras. Publicar un listado controlado del conjunto autorizado para la defensa, sin credenciales en Git.

Esta estrategia está preparada como procedimiento; **no se implementó ni ejecutó un alta masiva** ni se inventó correo nuevo para los 69 expedientes.

## 8. Recuperación, rollback y Git

Antes del COMMIT transaccional, cualquier error de fila, vínculo, rol, FK o bitácora revierte todas las escrituras del lote. Se reprodujo este caso con un conflicto en la segunda fila y con bitácora no persistida; no quedó la primera vinculación. Un segundo intento correcto es no-op sin nuevos registros ni sobrescrituras.

Después de una operación ya confirmada, no desasociar FK por SQL improvisado ni restaurar un dump encima de una instalación que siga recibiendo pagos. Ante error: detener nuevas operaciones con autorización, preservar evidencia, restaurar el dump en otra base nueva, repetir la comparación íntegra y preparar una compensación administrativa auditada o un cambio controlado de base aprobado por el responsable. La restauración total puede perder operaciones posteriores al snapshot; requiere evaluar ese intervalo y autorización específica. No se implementó aquí un comando de desvinculación ni se ejecutó restauración destructiva.

Rama: `codex/corregir-elegibilidad-evaluaciones`, HEAD `e1aef62bf522f4799850427dcb5151fff013561d`. La corrección de autorización anterior y los nuevos comandos, servicios, pruebas e informes permanecen sin staging/commit. Los artefactos de identidad y respaldo están ignorados por Git. El commit de código autorizado en la fase anterior queda pendiente de completar la recuperación y su verificación real. No se fusionará a main ni se hará push.

Archivos de trabajo, 15 en total (4 modificados y 11 nuevos sin seguimiento):

- Corrección previa: `app/Domains/Resultados/Services/EvaluacionAplicadaService.php`, `app/Http/Controllers/EvaluacionAplicadaController.php`, `resources/js/Pages/Estudiante/Evaluaciones.jsx`, `tests/Feature/Resultados/ResultadosTrazablesTest.php`, `app/Domains/Academico/Services/AccesoEvaluacionService.php`.
- Consola nueva: `app/Console/Commands/AuditarIdentidades.php`, `app/Console/Commands/ConciliarIdentidades.php`, `app/Console/Commands/RespaldarIdentidades.php`.
- Servicios nuevos: `app/Domains/Postulantes/Services/AuditoriaIdentidadService.php`, `app/Domains/Postulantes/Services/ConciliacionIdentidadService.php`.
- Pruebas nuevas: `tests/Feature/Postulantes/ConciliacionIdentidadCommandTest.php`, `tests/Feature/Postulantes/ConciliacionIdentidadTest.php`, `tests/Feature/Postulantes/PoblacionEstudiantilRestauradaTest.php`.
- Informes nuevos: este documento y `docs/informe-correccion-habilitacion-2026-10-19.md`.

Revisión de diff realizada; `git diff --check` y Pint sobre archivos modificados/nuevos aprobados. No se modificaron migraciones, seeders, rutas, roles, servicios financieros ni algoritmos CAT/TRI/EAP. La instancia aislada utilizada para el ensayo se detiene al terminar; se conservan los dumps y el certificado para recuperación.

### Autorización necesaria

Confirmar expresamente los tres pares **7→1, 8→2 y 9→3**, nombrar al actor TI/SA autorizado (el SA activo disponible es Adriana Choque Mamani, user_id=1, **no designado automáticamente**) y aprobar un motivo que registre esta conciliación. Las 69 altas DEMO requieren autorización separada; no se incluyen implícitamente en la aprobación de los tres vínculos.
