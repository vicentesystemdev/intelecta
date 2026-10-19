# Verificación focalizada: Matrículas y Cuotas

Fecha: 19/10/2026. Rama: `codex/corregir-elegibilidad-evaluaciones`.

## Errores comprobados y correcciones

1. **Bloqueo de renderizado:** el componente llamaba a `localToday()` sin definirlo ni importarlo. La expresión se evalúa incluso con el modal cerrado, por lo que impide cargar toda la página. Dos escenarios de ejecución del JSX real fallaron con `ReferenceError` antes del cambio. Se reemplazó por `fechaHoy`, ya enviada por Laravel mediante `AcademicDatePolicy` (Bolivia); ambos pasan ahora.
2. **Editor de cuotas con valores nulos:** al abrir una cuota existente, copiar el recurso completo enviaba opcionales `null` a inputs controlados. Se observó la advertencia de React en el navegador. El editor ahora construye exclusivamente los campos editables y normaliza los opcionales, conservando monto, estado y fechas. La regresión falló antes y pasa después. Tras recargar y abrir la cuota vencida de Mariana no hubo nuevos errores/advertencias de consola.

Único archivo de producción modificado: `resources/js/Pages/Institucional/MatriculasCuotas/Index.jsx`. No fue necesario cambiar controladores, consultas, permisos ni reglas de autorización.

## Verificación realizada

| Comprobación | Resultado |
| --- | --- |
| Integración Laravel, SQLite en memoria | 5/5 pruebas, 114 aserciones |
| Misma integración, PostgreSQL independiente en puerto 55439 | 5/5 pruebas, 114 aserciones |
| Node: validaciones existentes y tres regresiones del componente | 23/23 pruebas |
| Build de producción | Correcto: 3451 módulos |
| Pint y `git diff --check` | Correctos |
| Seis consultas HTTP del kernel contra PostgreSQL principal, transacción `READ ONLY` | HTTP 200 e Inertia correcto: listado, Diego, Mariana, vencidas, página 2 y sin coincidencias |

La integración prueba opciones, fecha civil de Bolivia, filtros combinados, conteos/saldo, paginación, altas y ediciones, bitácora, rechazo de cuotas duplicadas/pago sin fecha y acceso prohibido sin autorización. También confirma que pendiente permite evaluar, vencida restringe y pagada restablece el permiso automático, sin variar el monto.

En navegador, con la sesión administrativa iniciada por el usuario, se comprobaron listado, búsqueda por código, filtro de vencidas (8 matrículas), página 2 (10 registros), consulta vacía y apertura/cancelación de los cuatro formularios de alta/edición. Mariana conserva tres cuotas (pagada, vencida y pendiente) y habilitación restringida. El editor limita la fecha de pago a `2026-10-19`.

**No se guardaron operaciones financieras desde el navegador principal.** Las escrituras se ejercitaron mediante integración aislada; esto no certifica un guardado financiero end-to-end desde navegador sobre la base principal.

## A1: confirmado, pendiente deliberadamente

En una copia restaurada de PostgreSQL, con rollback, el servicio de cuotas sobrescribió tanto una restricción manual (false → true con cuota pendiente) como una autorización manual (true → false con cuota vencida), incluyendo su motivo.

No se aplicó una heurística: la fila actual no guarda procedencia manual/automática, y la bitácora es posterior a la actualización y admite fallos de escritura. Inferir procedencia por texto, fechas o ausencia de auditoría podría alterar decisiones legítimas.

La solución requiere persistir por separado la decisión manual y la proyección automática, definir precedencia/vigencia, conciliar los registros existentes y coordinar sus escrituras en transacción. Debe incorporar regresiones de restricción y autorización manual frente a cambios de matrícula/cuota. No basta cambiar `updateOrCreate` sin resolver esa procedencia. Se deja documentado, conforme a la prioridad solicitada de terminar las pantallas sin ampliar esta fase. Mientras siga abierto A1, un guardado administrativo puede sustituir una excepción manual: no se considera resuelto ni seguro ese caso.

## Conservación de datos y alcance

Se compararon antes/después las huellas completas de 36 tablas, incluyendo postulantes, matrículas, cuotas, inscripciones, habilitaciones, roles y permisos: **sin cambios**. Además se cotejaron individualmente las 78 cuentas con el respaldo que coincide con el baseline: las 72 estudiantiles permanecen idénticas; solo cambió `remember_token` del administrador 1 al iniciar sesión el usuario. No se incluyen valores de tokens en este informe.

Las 72 cuentas continúan activas y superan el middleware de identidad con sus 72 vínculos únicos. La decisión del servicio de acceso sigue idéntica por expediente: **62 permitidos y 10 restringidos**; Mariana continúa restringida. No se crearon cuentas, reemitieron credenciales, cambiaron vínculos, ejecutaron seeders ni aplicaron migraciones a la base principal. No se tocó CAT, TRI 3PL, EAP ni LAB.

Los artefactos privados de reproducción y comparación quedan en `storage/app/private/verificacion-matriculas-2026-10-19/`, ignorados por Git. El PostgreSQL temporal se detuvo al terminar; la instancia principal de Laragon no se reinició. El servidor web local se mantiene disponible para el usuario.

Cambios entregados sin `git add`, commit, merge ni push en esta fase.
